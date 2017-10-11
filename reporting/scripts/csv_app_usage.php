<?php

date_default_timezone_set("America/Edmonton");

require_once('couch_functions.php');

$HOST = 'http://shop.ekitabu.com:5984';
$UNPW = $argv[1] . ':'  $argv[2];

if (!isCouchOnline()) {
	exit("CouchDB host at $HOST is not online\n");
}


if (($handle = fopen("../unicef_accounts.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    	$db = $data[0];

        $docs = getAllDocs($db);
        foreach ($docs as $r_doc) {
            $ids[] = $r_doc->id;
        }

        $i = 0;
        foreach ($ids as $id) {
            $doc = getDoc($db, $id);
            $date = new DateTime($doc->timestamp);
            $dates[$i]['timestamp'] = $date->format('Y-d-m H:i:s');
            $dates[$i]['id'] = $id;
            $i++;
        }

        function date_compare($a, $b) {
            $t1 = strtotime($a['timestamp']);
            $t2 = strtotime($b['timestamp']);
            return $t1 - $t2;
        }
        usort($dates, 'date_compare');

        foreach (array_keys($dates) as $id) {
            $doc = getDoc($db, $id);

            if ($doc->action == 'openApp') {
                if (!$start_time instanceof DateTime) {
                    $start_time = new DateTime($doc->timestamp);
                }
            }

            echo $d->format('Y-d-m H:i:s') . ' action=' . $doc->action . ' ';
            if ($doc->action == 'openApp' || $doc->action == 'appOpen') {
                echo 'os=' . $doc->context->os . ' arch=' . $doc->context->arch . "<br>";
            } elseif ($doc->action == 'bookOpen') {
                echo 'title=' . $doc->context->title . "<br>";
            } elseif ($doc->action == 'bookClose') {
                echo "<br>";
            } elseif ($doc->action == 'audioPlay') {
                echo "<br>";
            } elseif ($doc->action == 'appClose') {
                echo "<br>";
            } else {
                print_r($doc->context);
                exit;
            }
        }
    }
    fclose($handle);
}

