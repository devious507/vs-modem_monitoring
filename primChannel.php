<?php

require_once("config.php");

if(!isset($_GET['mac'])) {
	//print '0'; exit();
	$mac='0014.e82b.a0f4';
}
$mac=$_GET['mac'];
$mac=strtoupper(preg_replace('/\./','',$mac));
$sql=sprintf("SELECT primchannel FROM modem_history WHERE mac='%s'",$mac);
$db=connect();
$res=$db->query($sql);
$row=$res->fetchRow();
$chan=preg_replace("/Mhz/",'',$row['primchannel']);
$chan=preg_replace("/hhz/",'',$chan);
print $chan;
print " Mhz";

?>
