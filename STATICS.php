<?php

$_COOKIE['username']='csr';
$_COOKIE['password']='csr';
require_once('config.php');
// 29746
$statics['94ccb94c5093']='38.108.138.74';

// Plaza
$statics['00259c4e7658']='38.108.141.174';

// CrossCreek
// OLD ENTRY $statics['000c414d3d6d']='38.108.140.77';
$statics['0006b1067f3a']='38.108.140.77';

// OakCrossing
$statics['001d7e479aec']='38.108.140.206';

// Dave
$statics['3c0754122ea1']='38.108.141.66';

// Fire Panel 6699
//$statics['00034f032df5']='38.108.138.175';
$statics['00034f03c30d']='38.108.138.175';

// Joe Barcus TEST
// 38.108.140.118  dhcp    94cc.b94c.506f
$statics['94ccb94c506f'] = '38.108.140.118';
// Frank Tribble
// 38.108.139.115  dhcp    001c.11f5.3fd5
$statics['001c11f53fd5'] = '38.108.139.115';

// Office Test
// 38.108.138.38   dhcp    e483.9972.99e5
$statics['e483997299e5'] = '38.108.138.38';

// Mansions Corporate
$statics['0021431df91c'] = '38.108.139.102';
//
$statics['001c11422ab9'] = '38.108.138.117';
$statics['e48399729796'] = '38.108.138.123';

//
$statics['001c11f53953']='38.108.138.12';


// Fill in mac->ip pairings from database
//
$db = connect();
$sql = "SELECT mac_addr,ip_addr FROM local_statics";
$res = $db->query($sql);
if(PEAR::isError($res)) {
	print $res->getMessage();
	exit();
}

$row=$res->fetchRow();
while(($row=$res->fetchRow())==true) {
	$mac=$row['mac_addr'];
	$ip =$row['ip_addr'];
	$statics[$mac]=$ip;
}
// Move on to the update stuff



$sql="UPDATE dhcp_leases SET dynamic_flag='YES' WHERE dynamic_flag='NO'";
$db->query($sql);
foreach($statics as $k=>$v) {
	$mac = $k;
	$ip  = $v;
	$endTstamp=time()+86400;
	$start = date('Y-m-d h:i:s');
	$end   = date('Y-m-d h:i:s',$endTstamp);
	$sql = "INSERT INTO dhcp_leases VALUES ('{$ip}','{$mac}','{$start}','{$end}','{$start}','NO','NO','NEWPC','999999','abcd1234abcd','1','','1')";
	$sql.= " ON DUPLICATE KEY UPDATE dynamic_flag='NO', end_time='{$end}'";
	print $sql; print "\n";
	$db->query($sql);
}

$sql="insert into dhcp_oldleases select *,now() from dhcp_leases WHERE end_time < now()";
$db->query($sql);

$sql="DELETE FROM dhcp_leases WHERE end_time < now() AND dynamic_flag='YES'";
$db->query($sql);

$sqlAR[0]['sql']="SELECT count(ipaddr) FROM dhcp_leases WHERE ipaddr like '38.108.138.%'";
$sqlAR[0]['min']=180;
$sqlAR[0]['update']="UPDATE config_nets SET full_flag='NO' WHERE network='38.108.138.0/24' AND full_flag='YES'";

$sqlAR[1]['sql']="SELECT count(ipaddr) FROM dhcp_leases WHERE ipaddr like '38.108.139.%'";
$sqlAR[1]['min']=80;
$sqlAR[1]['update']="UPDATE config_nets SET full_flag='NO' WHERE network='38.108.139.0/25' AND full_flag='YES'";

$sqlAR[2]['sql']="SELECT count(ipaddr) FROM dhcp_leases WHERE ipaddr like '38.108.140.%'";
$sqlAR[2]['min']=180;
$sqlAR[2]['update']="UPDATE config_nets SET full_flag='NO' WHERE network='38.108.140.0/24' AND full_flag='YES'";

$sqlAR[3]['sql']="SELECT count(ipaddr) FROM dhcp_leases WHERE ipaddr like '38.108.141.%'";
$sqlAR[3]['min']=180;
$sqlAR[3]['update']="UPDATE config_nets SET full_flag='NO' WHERE network='38.108.141.0/24' AND full_flag='YES'";

$sqlAR[4]['sql']="SELECT count(ipaddr) FROM dhcp_leases WHERE ipaddr like '38.108.142.%' OR ipaddr like '38.108.143.%'";
$sqlAR[4]['min']=350;
$sqlAR[4]['update']="UPDATE config_nets SET full_flag='NO' WHERE network='38.108.142.0/23' AND full_flag='YES'";

foreach ($sqlAR as $s) {
	$sql = $s['sql'];
	$min = $s['min'];
	$upd = $s['update'];
	$rset = $db->query($sql);
	if(PEAR::isError($rset)) {
		print $sql."\n";
		print $rset->getMessage()."\n";
		exit();
	}
	$row=$rset->fetchRow();
	$count = $row['count(ipaddr)'];
	print $sql.' -- '.$count." \n";
	if($count <= $min) {
		$rrset=$db->query($upd);
		print $upd."\n";
		if(PEAR::isError($rrset)) {
			print $rrset->getMessage()."\n";
		}
	}
}

?>
