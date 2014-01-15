<?php

require_once("../config.php");
$sql="select d.modem_macaddr,d.subnum,d.config_file,m.fwdrx,m.fwdsnr,m.revtx,m.revrx,m.revsnr,m.ip,m.time,m.primchannel,m.interface,m.firstcontact FROM docsis_modem AS d LEFT OUTER JOIN modem_history AS m ON d.modem_macaddr = m.mac WHERE d.subnum='%s'";

if(isset($_GET['sub_id'])) {
	$sql=sprintf($sql,$_GET['sub_id']);
} else {
	exit();
}

$db=connect();

$res=$db->query($sql);
while(($row=$res->fetchRow())==true) {
	$myresults[]=$row;
}
print json_encode($myresults);
