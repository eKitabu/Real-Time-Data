<?php

date_default_timezone_set("America/Edmonton");

require_once('../../../data/config.php');
require_once('../../lib/couch_functions.php');

$reportFile = '/home/mocyeg/ekitabu/prod/pub.shop/unicef/reports/json/alltime/usage_tod.json';

if (!isCouchOnline()) {
	exit("CouchDB host at $HOST is not online\n");
}

for ($i = 0; $i < 24; $i++) {
    $hours[$i] = 0;
}

if (($handle = fopen($GLOBALS['ACCTS'], "r")) !== FALSE) {
    $totalUsage = 0;
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($data[0] === 'device') { continue; }
        $db = $data[0];
        $school = (strlen($data[2])) ? $data[2] : 'Unknown';

        $docs = getAllDocs($db);
        if (!$docs) { continue; }

        usort($docs, 'sortby_obj_timestap');

        foreach ($docs as $doc) {
            if ($doc->action == 'appOpen') {
                $start_time = new DateTime($doc->timestamp);
                $hour = $start_time->format('G');
                $hours[$hour]++;
                $totalUsage++;
//                echo 'event=openApp os=' . $doc->context->os . ' arch=' . $doc->context->arch . " hour=" . $start_time->format('H') . "\n";
            }
        }
    }
    fclose($handle);

    $hour_percents = array();
    foreach (array_keys($hours) as $hour) {
        $hour_percents[$hour] = round($hours[$hour] / $totalUsage) * 100;
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
