<?php

date_default_timezone_set("America/Edmonton");

require_once('../../lib/couch_functions.php');

$HOST = 'http://shop.ekitabu.com:5984';
$UNPW = $_GET['un'] . ':' . $_get['pw'];

if (!isCouchOnline()) {
	exit("CouchDB host at $HOST is not online\n");
}

if (!$_GET['d']) {
	displayForm();
} else {
	displayEvents($_GET['d']);
}




/* == funcs == */

function displayEvents($db) {
	$docs = getAllDocs($db);
	foreach ($docs as $r_doc) {
		$ids[] = $r_doc->id;
	}

	echo "<pre>";

	$i = 0;
	foreach ($ids as $id) {
		$doc = getDoc($db, $id);
		$date = new DateTime($doc->timestamp);
		$dates[$i]['timestamp'] = $date->format('Y-d-m H:i:s');
		$dates[$i]['id'] = $id;
		$i++;
	}

	usort($dates, 'date_compare');

	foreach (array_keys($dates) as $id) {
		$doc = getDoc($db, $id);

		$d = new DateTime($doc->timestamp);
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

	echo "</pre>";
}

function date_compare($a, $b) {
    $t1 = strtotime($a['timestamp']);
    $t2 = strtotime($b['timestamp']);
    return $t1 - $t2;
}

function displayForm() {
	echo '<form method="GET" action="getDocs.php">Device ID: <input name="d" type="text"><input type="submit" name="submit" value="submit"></form>';
}