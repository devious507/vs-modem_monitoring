<?php

require_once("config.php");
checkSuper();

if(!isset($_POST['cfg_id'])) {
	buildPage("<p>Config incomplete, not adding.</p>");
	exit();
} 
foreach($_POST as $k=>$v) {
	$ks[]=$k;
	if($k != 'cfg_update') {
		$vs[]="'{$v}'";
	} else {
		$vs[]=$v;
	}
}
$kline=implode(",",$ks);
$vline=implode(",",$vs);

$sql="INSERT INTO config_modem ({$kline}) VALUES ({$vline})";
$conn = connect();
$rset = $conn->query($sql);
if(PEAR::isError($rset)) {
	$body="<p>SQL ERROR:</p>";
	$body.=$rset->getMessage();
	buildPage($body,$sql);
	exit();
} else {
	header("Location: modem_configs.php");
	exit();
}
?>
