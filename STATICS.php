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

// Fire Panel
$statics['00034f032df5']='38.108.138.175';

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

// 29140
$statics['0023ed9afb8e'] = '38.108.138.9';

// 29128
$statics['145bd1c0ae0c'] = '38.108.140.182';

// 0023ed9b01e7,   28361, needs a sticky ip
$statics['001c11f53fee'] = '38.108.138.10';

// 29186
$statics['0024a18c13c8'] = '38.108.138.253';

// 29440
$statics['001c11422a9f'] = '38.108.138.208';
// 29128 
// 38.108.142.70   dhcp    145b.d1c0.ae0c
$statics['145bd1c0ae0c'] = '38.108.142.70';

// 29528
// 38.108.138.144  dhcp    7cbf.b17d.53ca  N 
$statics['7cbfb17d53ca'] = '38.108.138.144';

// 28101
// 38.108.138.111  dhcp    e483.9972.99ee
$statics['e483997299ee'] = '38.108.138.111';


// 15698
// 38.108.138.183  dhcp    0024.a18c.1442
$statics['0024a18c1442'] = '38.108.138.183';

// 29447
// 38.108.138.252  dhcp    0024.a18c.1463
$statics['0024a18c1463'] = '38.108.138.252';

// 27586
// 38.108.138.236  dhcp    0024.a18c.37a0  N 
$statics['0024a18c37a0'] = '38.108.138.236';

// 29930
// 38.108.138.71   dhcp    e483.9972.976c
$statics['e4839972976c'] = '38.108.138.71';

//
// 30142
// 38.108.138.227  dhcp    001c.1142.2a75  N 
$statics['001c11422a75'] = '38.108.138.227';

$statics['001c11422ab9'] = '38.108.138.117';
$statics['e48399729796'] = '38.108.138.123';


// weston office
$statics['442b037ed3cf'] = '38.108.141.83';

// 30609
$statics['001c11f53fee'] = '38.108.138.10';

// 30648
$statics['001c11422a9e'] = '38.108.141.24';
// 30613
$statics['001c11f53fed'] = '38.108.138.32';
// 30130, 38.108.141.207, 7896.84bf.da2d
$statics['789684bfda2d'] = '38.108.141.207';


$db = connect();
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
