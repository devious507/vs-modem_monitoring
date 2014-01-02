<?php

require_once("config.php");
//checkSuper();
if(!isset($_GET['modem_macaddr'])) {
	buildPage("Incomplete Entry, aborting");
	exit();
} else {
	$mac = $_GET['modem_macaddr'];
}

$url="del_modem.php?modem_macaddr={$mac}&confirm=true";

if( (!isset($_GET['confirm'])) || ($_GET['confirm'] != true) ) {
	$body = "<p>You must confirm your deletion request.  <br>Modem Mac Address: {$mac}<br></p>\n";
	$body.= "<p><a href=\"{$url}\">Click Here to Confirm</a></p>\n";
	buildPage($body);
	exit();
} else {
	$sql="DELETE FROM docsis_modem WHERE modem_macaddr='{$mac}'";
	$conn = connect();
	$rset = $conn->query($sql);
	$hadError=false;
	if(PEAR::isError($rset)) {
		$body = "<p>SQL Error:<br>";
		$body.= $rset->getMessage();
		$body.="</p>\n";
		buildPage($body,$sql);
		$hadError=true;
	}
	$sql="DELETE FROM modem_history WHERE mac='{$mac}'";
	$rset = $conn->query($sql);
	if(PEAR::isError($rset)) {
		$body = "<p>SQL Error:<br>";
		$body.= $rset->getMessage();
		$body.="</p>\n";
		buildPage($body,$sql);
		$hadError=true;
	}
	if($hadError)
		exit();
	header("Location: modem.php");
	exit();
}

?>
