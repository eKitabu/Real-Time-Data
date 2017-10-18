<?php

date_default_timezone_set("America/Edmonton");

require_once('../../../data/config.php');
require_once('../../lib/couch_functions.php');

$reportFile = '/home/mocyeg/ekitabu/prod/pub.shop/unicef/reports/json/alltime/devices_activated_by_school.json';

if (!isCouchOnline()) {
	exit("CouchDB host at $HOST is not online\n");
}


if (($handle = fopen($GLOBALS['ACCTS'], "r")) !== FALSE) {
    $schools = array();

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($data[0] === 'device') { continue; }

    	$db = $data[0];
        $school = (strlen($data[2]) ? $data[2] : 'Unknown';

        if (!array_key_exists($school, $schools)) {
            $schools[$school] = 0;
        }

        $docs = getAllDocs($db);
        if ($docs) {
            $schools[$school]++;
        } else {
            continue;
        }
    }
    fclose($handle);

    $arrayOut = array();
    foreach (array_keys($schools) as $school) {
        $obj = new stdClass;
        $obj->School = $school;
        $obj->Devices = $schools[$school];
        $arrayOut[] = $obj;
    }

    file_put_contents($reportFile, json_encode($arrayOut));
}
