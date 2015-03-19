<?php

require_once("config.php");

$db=connect();
$sql="select macaddr,ipaddr from dhcp_leases WHERE macaddr like '0090f8%'";
$res=$db->query($sql);
if(PEAR::isError($res)) {
	print $res->getMessage();
	exit();
}
while(($row=$res->fetchRow())==true) {
	$data[$row['macaddr']]=$row['ipaddr'];
}
$res->free();
$data=json_encode($data);
header("Content-type: text/plain");
print $data;
