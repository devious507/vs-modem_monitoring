<?php

require_once('config.php');

checkApiAccess();

if(isset($_POST['sql'])) {
	$sql=$_POST['sql'];
	$db=connect();
	$res = $db->query($sql);
	if(PEAR::isError($res)) {
		$result="BAD QUERY";
		print serialize($result);
	}
	while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC))==true) {
		$rows[]=$row;
	}
	print serialize($rows);
	exit();
} elseif(isset($_POST['function']) && isset($_POST['val'])) {
	$val = $_POST['val'];
	if(isset($_POST['property'])) {
		$property=$_POST['property'];
	} else {
		$property='';
	}
	switch($_POST['function']) {
	case "fwdRxColor":
		$color=fwdRxColor($val);
		print serialize($color);
		break;
		exit();
	case "fwdSnrColor":
		$color=fwdSnrColor($val);
		print serialize($color);
		break;
		exit();
	case "revTxColor":
		$color = revTxColor($val);
		print serialize($color);
		break;
		exit();
	case "revRxColor":
		$color=revRxColor($val,$property);
		print serialize($color);
		break;
		exit();
	case "revSnrColor":
		$color=revSnrColor($val);
		print serialize($color);
		break;
		exit();
	default:
		break;
	}
	/*
	 */
}


function checkApiAccess() {
	if(!isset($_POST['remote_access_key'])) {
		$result='NULL';
		print serialize($result);
		exit();
	}
	$myKey = md5('moofincluck-cow-fish-chicken');
	$theirKey = $_POST['remote_access_key'];
	if($myKey != $theirKey) {
		// bad key, not doing anything
		$result='NULL';
		print serialize($result);
		exit();
	}
}
?>
