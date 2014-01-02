<?php

require_once("config.php");
checkSuper();

if(!isset($_POST['network'])) {
	buildPage("Missing information, aborting");
	exit();
}
$network = $_POST['network'];
unset($_POST['network']);
foreach($_POST as $k=>$v) {
	switch($k) {
	default:
		$vals[]="{$k}='{$v}'";
		break;
	}
}
$sql = "UPDATE config_nets SET ".implode(", ",$vals)." WHERE network='{$network}'";
$conn = connect();
$rset = $conn->query($sql);
if(PEAR::isError($rset)) {
	$body ="<p>Error with SQL:<br>";
	$body.=$rset->getMessagE();
	buildPage($body,$sql);
	exit();
}
header("Location: networks.php");

?>
