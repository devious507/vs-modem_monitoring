<?php

require_once("config.php");
checkSuper();

if(!isset($_POST['network'])) {
	buildPage("Network not defined, aborting!");
	exit();
}

foreach($_POST as $k=>$v) {
	$ks[]=$k;
	$vs[]="'".$v."'";
}

$kline=implode(",",$ks);
$vline=implode(",",$vs);

$sql="INSERT INTO config_nets ({$kline}) VALUES ({$vline})";
$conn=connect();
$rset = $conn->query($sql);
if(PEAR::isError($rset)) {
	$body="<p>SQL Error:<bR>";
	$body.=$rset->getMessage();
	$body.="</p>";
	buildPage($body);
	exit();
} else {
	header("Location: networks.php");
	exit();
}
?>
