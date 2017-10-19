<?php

date_default_timezone_set("America/Edmonton");

require_once('../../../data/config.php');
require_once('../../lib/couch_functions.php');

$reportFile = '/home/mocyeg/ekitabu/prod/pub.shop/unicef/reports/json/alltime/usage_sum.json';

if (!isCouchOnline()) {
	exit("CouchDB host at $HOST is not online\n");
}


if (($handle = fopen($GLOBALS['ACCTS'], "r")) !== FALSE) {
    $usage = array();
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($data[0] === 'device') { continue; }
        $db = $data[0];
        $school = (strlen($data[2])) ? $data[2] : 'Unknown';

        $docs = getAllDocs($db);
        if (!$docs) { continue; }

        usort($docs, 'sortby_obj_timestamp');

        foreach ($docs as $doc) {
            if ($doc->action == 'appOpen') {
                $start_time = new DateTime($doc->timestamp);
            } elseif ($doc->action == 'appClose') {
                if (!$start_time) { continue; }

                $start_date = $start_time->format("Y-m-d");
                $end_time = new DateTime($doc->timestamp);
                $duration_s = $end_time->getTimestamp() - $start_time->getTimestamp();
                if ($duration_s < 0) {
                    unset($start_time);
                    unset($end_time);
                    continue;
                }

                if (array_key_exists($start_date, $usage)) {
                    $usage[$start_date] += $duration_s;
                } else {
                    $usage[$start_date] = $duration_s;
                }

                unset($start_time);
            }
        }
    }
    fclose($handle);

    ksort($usage);

    $arrayOut = array();
    foreach (array_keys($usage) as $date) {
        $minutes = round($usage[$date] / 60);
        $obj = new stdClass;
        $obj->Date = $date;
        $obj->Minutes = $minutes;
        $arrayOut[] = $obj;
    }

    file_put_contents($reportFile, json_encode($arrayOut));
}
