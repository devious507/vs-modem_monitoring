<?php

require_once("config.php");
checkSuper();
if( (!isset($_GET['server_id'])) || (!isset($_GET['opt_id'])) || (!isset($_GET['opt_type'])) ) {
	buildPage("Incomplete Entry, aborting");
	exit();
} else {
	$s_id=$_GET['server_id'];
	$o_id=$_GET['opt_id'];
	$o_type=$_GET['opt_type'];
}

$url="del_dhcp_option.php?server_id={$s_id}&opt_id={$o_id}&opt_type={$o_type}&confirm=true";

if( (!isset($_GET['confirm'])) || ($_GET['confirm'] != true) ) {
	$body = "<p>You must confirm your deletion request.  <br>Server-ID: {$s_id}<br>Opt-ID: {$o_id}<br>Opt_Type: {$o_type}<br></p>\n";
	$body.= "<p><a href=\"{$url}\">Click Here to Confirm</a></p>\n";
	buildPage($body);
	exit();
} else {
	$sql="DELETE FROM config_opts WHERE server_id={$s_id} AND opt_id={$o_id} AND opt_type={$o_type}";
	$conn = connect();
	$rset = $conn->query($sql);
	if(PEAR::isError($rset)) {
		$body = "<p>SQL Error:<br>";
		$body.= $rset->getMessage();
		$body.="</p>\n";
		buildPage($body,$sql);
		exit();
	}
	header("Location: dhcp_options.php");
	exit();
}

?>
