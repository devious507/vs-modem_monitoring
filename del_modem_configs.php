<?php

require_once("config.php");
checkSuper();
if(!isset($_GET['cfg_id'])) {
	buildPage("Incomplete Entry, aborting");
	exit();
} else {
	$c_id=$_GET['cfg_id'];
}

$url="del_modem_configs.php?cfg_id={$c_id}&confirm=true";

if( (!isset($_GET['confirm'])) || ($_GET['confirm'] != true) ) {
	$body = "<p>You must confirm your deletion request.  <br>CFG-ID: {$c_id}<br></p>\n";
	$body.= "<p><a href=\"{$url}\">Click Here to Confirm</a></p>\n";
	buildPage($body);
	exit();
} else {
	$sql="DELETE FROM config_modem WHERE cfg_id={$c_id}";
	$conn = connect();
	$rset = $conn->query($sql);
	if(PEAR::isError($rset)) {
		$body = "<p>SQL Error:<br>";
		$body.= $rset->getMessage();
		$body.="</p>\n";
		buildPage($body,$sql);
		exit();
	}
	header("Location: modem_configs.php");
	exit();
}

?>
