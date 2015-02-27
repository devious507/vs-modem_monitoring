<?php

$_COOKIE['username']='csr';
$_COOKIE['password']='csr123';
require_once("config.php");

if(isset($_GET['mac']) AND strlen($_GET['mac'])==12) {
	$sql="SELECT ip FROM modem_history WHERE mac='{$_GET['mac']}'";
} else {
	exit();
}
$db=connect();
$rset=$db->query($sql);
$row=$rset->fetchRow();
print $row['ip'];
?>
