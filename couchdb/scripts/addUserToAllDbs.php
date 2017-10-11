<?php

$HOST = 'http://shop.ekitabu.com:5984';
$UNPW = $argv[1] . ':'  $argv[2];

if (!isCouchOnline()) {
	exit("CouchDB host at $HOST is not online\n");
}


$row = 1;
if (($handle = fopen("../unicef_accounts.csv", "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    	$db_name = $data[0];
    	if (assignUserToDb("reporter", $db_name)) {
    		echo "added reporter to $db_name\n";
    	} else {
    		echo "ERROR: failed adding reporter to $db_name";
    	}
    }
    fclose($handle);
}



// ============================================================== //

function isCouchOnline() {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $GLOBALS['HOST'] . '/');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-type: application/json',
		'Accept: */*'
	));
 
	$response = json_decode(curl_exec($ch));

	if (property_exists($response, 'couchdb') && $response->couchdb == "Welcome") {
		return true;
	} else {
		return false;
	}

	curl_close($ch);
}

function getId() {
	return bin2hex(openssl_random_pseudo_bytes(2));
}

function getPassword() {
	return bin2hex(openssl_random_pseudo_bytes(3));
}

function getUniqueId() {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $GLOBALS['HOST'] . '/_all_dbs');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-type: application/json',
		'Accept: */*'
	));
 
	$all_dbs = json_decode(curl_exec($ch));

	curl_close($ch);

	do {
		$id = getId();
	} while (in_array($id, $all_dbs));

	return $id;
}

function createDb($id) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $GLOBALS['HOST'] . "/$id");
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERPWD, $GLOBALS['UNPW']);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-type: application/json',
		'Accept: */*'
	));

	$response = json_decode(curl_exec($ch));

	curl_close($ch);

	if (property_exists($response, 'ok') && $response->ok === true) {
		return true;
	} else {
		print_r($response);
		return false;
	}
}

function createUser($id, $password) {
	$device = new stdClass;
	$device->name = $id;
	$device->password = $password;
	$device->type = 'user';
	$device->roles = [];

	$payload = json_encode($device);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $GLOBALS['HOST'] . '/_users/org.couchdb.user:' . $id);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//	curl_setopt($ch, CURLOPT_USERPWD, $GLOBALS['UNPW']);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-type: application/json',
		'Accept: */*'
	));

	$response = json_decode(curl_exec($ch));

	curl_close($ch);

	if (property_exists($response, 'ok') && $response->ok === true) {
		return true;
	} else {
		return false;
	}
}

function assignUserToDb($user, $db) {
	$security = new stdClass();
	$security->members = new stdClass();
	$security->members->names[] = $db;
	$security->members->names[] = $user;

	$payload = json_encode($security);

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $GLOBALS['HOST'] . "/$db/_security");
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERPWD, $GLOBALS['UNPW']);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-type: application/json',
		'Accept: */*'
	));

	$response = json_decode(curl_exec($ch));

	curl_close($ch);

	if (property_exists($response, 'ok') && $response->ok === true) {
		return true;
	} else {
		return false;
	}
}
