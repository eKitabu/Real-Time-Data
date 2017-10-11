<?php

require_once('../../lib/couch_functions.php');

$HOST = 'http://shop.ekitabu.com:5984';
$UNPW = $argv[1] . ':'  $argv[2];

if (!isCouchOnline()) {
	exit("CouchDB host at $HOST is not online\n");
}

$db = $_GET['d'];

if (!in_array($db, array('device1', 'device2')) {
	exit("permissioned denied for db: $db");
}

$docs = getAllDocs($db);
if ($docs) {
	deleteDocs($db, $docs);
} else {
	exit("getAllDocs() returned " . print_r($docs, true));
}
