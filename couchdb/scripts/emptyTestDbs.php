<?php

date_default_timezone_set("America/Edmonton");

require_once('../../../data/config.php');
require_once('../../lib/couch_functions.php');

$GLOBALS['UNPW'] = 'admin:rm2011go';

if (!isCouchOnline()) {
	exit("CouchDB host at $HOST is not online\n");
}


if (($handle = fopen($GLOBALS['ACCTS'], "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        if ($data[0] === 'device') { continue; }
        $db = $data[0];

		$docs = getAllDocs($db);
		if ($docs) {
			deleteDocs($db, $docs);
			echo "purged all activity from db=$db\n";
		}
	}
}
