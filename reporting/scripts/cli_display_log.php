<?php

date_default_timezone_set("America/Edmonton");

require_once('../../../data/config.php');
require_once('../../lib/couch_functions.php');

if (!isCouchOnline()) {
	exit("CouchDB host at $HOST is not online\n");
}


$db = 'test_e4c3';

$docs = getAllDocs($db);
if (!$docs) { continue; }

usort($docs, 'sortby_obj_timestap');

foreach ($docs as $doc) {
    $d = new DateTime($doc->timestamp);
    $timestamp = $d->format('Y-d-m H:i:s');

    if ($doc->action == 'appOpen') {
        echo "$timestamp action=" . $doc->action . ' os=' . $doc->context->os . ' arch=' . $doc->context->arch . "\n";
    } elseif ($doc->action == 'bookOpen') {
        echo "$timestamp action=" . $doc->action . ' title=' . $doc->context->title . "\n";
    } elseif ($doc->action == 'bookClose') {
        echo "$timestamp action=" . $doc->action . "\n";
    } elseif ($doc->action == 'audioPlay') {
        echo "$timestamp action=" . $doc->action . "\n";
    } elseif ($doc->action == 'appClose') {
        echo "$timestamp action=" . $doc->action . "\n";
    } elseif ($doc->action == 'ttsPlay') {
        echo "$timestamp action-" . $doc->action . "\n";
    } elseif ($doc->action == 'ttsStop') {
        echo "$timestamp action-" . $doc->action . "\n";
    } else {
        print_r($doc);
    }
}
