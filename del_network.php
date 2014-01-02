<?php

require_once("config.php");
checkSuper();
if(!isset($_GET['network'])) {
	buildPage("Incomplete Entry, aborting");
	exit();
} else {
	$network = $_GET['network'];
}

$url="del_network.php?network={$network}&confirm=true";

if( (!isset($_GET['confirm'])) || ($_GET['confirm'] != true) ) {
	$body = "<p>You must confirm your deletion request.  <br>Network ID: {$network}<br></p>\n";
	$body.= "<p><a href=\"{$url}\">Click Here to Confirm</a></p>\n";
	buildPage($body);
	exit();
} else {
	$sql="DELETE FROM config_nets WHERE network='{$network}'";
	$conn = connect();
	$rset = $conn->query($sql);
	if(PEAR::isError($rset)) {
		$body = "<p>SQL Error:<br>";
		$body.= $rset->getMessage();
		$body.="</p>\n";
		buildPage($body,$sql);
		exit();
	}
	header("Location: networks.php");
	exit();
}

?>
