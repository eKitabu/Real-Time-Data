<?php


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

function getAllDocs($db) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $GLOBALS['HOST'] . "/$db/_all_docs?include_docs=true");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERPWD, $GLOBALS['UNPW']);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-type: application/json',
		'Accept: */*'
	));

	$response = json_decode(curl_exec($ch));

 	curl_close($ch);

	if (property_exists($response, 'total_rows') && $response->total_rows > 0) {
		$rows = array();
		foreach ($response->rows as $item) {
			$rows[] = $item->doc;
		}
		return $rows;
	} else {
		return false;
	}
}

function getDoc($db, $id) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $GLOBALS['HOST'] . "/$db/$id");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERPWD, $GLOBALS['UNPW']);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-type: application/json',
		'Accept: */*'
	));

	$response = json_decode(curl_exec($ch));

 	curl_close($ch);

 	return $response;
}

function deleteDocs($db, $docs) {
	$ch = curl_init();

	foreach ($docs as $row) {
		curl_setopt($ch, CURLOPT_URL, $GLOBALS['HOST'] . "/$db/" . $row->_id . "?rev=" . $row->_rev);
		curl_setopt($ch, CURLOPT_USERPWD, $GLOBALS['UNPW']);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-type: application/json',
			'Accept: */*'
		));

		$response = json_decode(curl_exec($ch));
	}

	curl_close($ch);
	return true;
}

function sortby_obj_timestamp($a, $b) {
    $t1 = strtotime($a->timestamp);
    $t2 = strtotime($b->timestamp);
    return $t1 - $t2;
}
