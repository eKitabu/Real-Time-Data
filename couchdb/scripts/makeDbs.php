<?php

require_once('../../lib/couch_functions.php');

$HOST = 'http://shop.ekitabu.com:5984';
$UNPW = $argv[1] . ':'  $argv[2];

if (!isCouchOnline()) {
	exit("CouchDB host at $HOST is not online\n");
}

echo "device,password,school,county,country\n";

for ($i = 0; $i < 50; $i++) {
	$id = chr(rand(97,122)) . getUniqueId();
	$password = getPassword();

	if (!createDb($id)) { exit("FAIL: createDb($id)\n"); }
	if (!createUser($id, $password)) { exit("FAIL: createUser($id, $password)\n"); }
	if (!assignUserToDb($id, $id)) { exit("FAIL: assignUserToDb($id, $id)\n"); }

	echo "$id,$password,,,\n";
}



// === funcs === //

function getId() {
	return bin2hex(openssl_random_pseudo_bytes(2));
}

function getPassword() {
	return bin2hex(openssl_random_pseudo_bytes(3));
}
