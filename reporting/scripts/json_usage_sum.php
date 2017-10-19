<?php

date_default_timezone_set("America/Edmonton");

require_once('../../../data/config.php');
require_once('../../lib/couch_functions.php');

$reportFile = '/home/mocyeg/ekitabu/prod/pub.shop/unicef/reports/json/alltime/usage_tod.json';

if (!isCouchOnline()) {
	exit("CouchDB host at $HOST is not online\n");
}


if (($handle = fopen($GLOBALS['ACCTS'], "r")) !== FALSE) {
    $totalUsage = 0;
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
                $duration_s = $end_time - $start_time;

                if (array_key_exists($start_date, $usage)) {
                    $usage[$start_date] += $duration_s;
                } else {
                    $usage[$start_date] = $duration_s;
                }
            }
        }
    }
    fclose($handle);






    $hour_percents = array();
    foreach (array_keys($hours) as $hour) {
        $percent = ($hours[$hour] / $totalUsage) * 100;
        $hour_percents[$hour] = round($percent);
    }

    $arrayOut = array();
    foreach (array_keys($hours) as $hour) {
        $hour_formatted = str_pad($hour, 2, '0', STR_PAD_LEFT) . ":00";
        $obj = new stdClass;
        $obj->Hour = $hour_formatted;
        $obj->Usage = $hour_percents[$hour];
        $arrayOut[] = $obj;
    }

    file_put_contents($reportFile, json_encode($arrayOut));
}
