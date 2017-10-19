<?php

date_default_timezone_set("America/Edmonton");

require_once('../../../data/config.php');
require_once('../../lib/couch_functions.php');

$reportFile = '/home/mocyeg/ekitabu/prod/pub.shop/unicef/reports/json/alltime/content_utilization.json';

if (!isCouchOnline()) {
	exit("CouchDB host at $HOST is not online\n");
}


if (($handle = fopen($GLOBALS['ACCTS'], "r")) !== FALSE) {
    $books = array();

    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($data[0] === 'device') { continue; }
        $db = $data[0];
        $school = (strlen($data[2])) ? $data[2] : 'Unknown';

        $docs = getAllDocs($db);
        if (!$docs) { continue; }

        usort($docs, 'sortby_obj_timestamp');

        foreach ($docs as $doc) {
            if ($doc->action == 'bookOpen') {
                if (array_key_exists($doc->context->title, $books)) {
                    $books[$doc->context->title]['open_count']++;
                } else {
                    $books[$doc->context->title]['open_count'] = 1;
                    $books[$doc->context->title]['tts_count'] = 0;
                    $books[$doc->context->title]['media_overlay_count'] = 0;
                }
                $lastBookOpen = $doc->context->title;
            } elseif ($doc->action == 'ttsPlay') {
                if (array_key_exists('tts_count', $books[$lastBookOpen])) {
                    $books[$lastBookOpen]['tts_count']++;
                } else {
                    $books[$lastBookOpen]['tts_count'] = 1;
                }
            } elseif ($doc->action == 'audioPlay') {
                if (array_key_exists('media_overlay_count', $books[$lastBookOpen])) {
                    $books[$lastBookOpen]['media_overlay_count']++;
                } else {
                    $books[$lastBookOpen]['media_overlay_count'] = 1;
                }
            }
        }
    }
    fclose($handle);

    $arrayOut = array();
    foreach (array_keys($books) as $book) {
        $obj = new stdClass;
        $obj->book_name = $book;
        $obj->open_count = $books[$book]['open_count'];
        $obj->media_overlay_count = $books[$book]['media_overlay_count'];
        $obj->tts_count = $books[$book]['tts_count'];
        $arrayOut[] = $obj;
    }

    file_put_contents($reportFile, json_encode($arrayOut));
}
