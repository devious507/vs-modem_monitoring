<?php

require_once("../defines.php");
require_once("../functions.php");


if(!isset($_GET['acctnum'])) {
	exit();
}
if(!isset($_GET['lastname'])) {
	exit();
}

$acctnum=$_GET['acctnum'];
$lastname=$_GET['lastname'];

// Fix Long Form Account Numbers
if(preg_match("/-/",$acctnum)) {
	$tmp=preg_split("/-/",$acctnum);
	$acctnum=intval($tmp[1]);
	unset($tmp);
}

$json['quota']='insert quota value here';
$json['usage']='insert mtd usage here';


// Connect to Database
$db=connect();

// Check to make sure lastname / acctnum pair is good
$sql="select subnum,name from customer_address WHERE subnum = ?";
$sth=$db->prepare($sql);
if(PEAR::isError($sth)) {
	unset($json);
	$json['Error']="STH001 ".$sth->getMessage();
	print json_encode($json);
	exit();
}

//$res=$sth->execute($acctnum,'% '.strtolower($lastname));
$res=$sth->execute(array($acctnum));
if(PEAR::isError($res)) {
	unset($json);
	$json['Error']="RES001 ".$res->getMessage();
	$json['SQL']=$sql;
	print json_encode($json);
	exit();
}

while(($row=$res->fetchRow())==true) {
	$name_submitted=strtolower($lastname);
	$name_row=strtolower($row['name']);
	$pattern="/".$name_submitted."/";
	if(preg_match($pattern,$name_row)) {
		$json['acctnum']=$acctnum;
		$json['lastname']=$lastname;
		$json['name']=$row['name'];
	} else {
		unset($json);
		$json['Error']='Error: Name and Account Number do not match';
		print json_encode($json);
		exit();
	}

}
if(!isset($json['lastname'])) {
	unset($json);
	$json['Error']='Error: Name and Account Number do not match';
	print json_encode($json);
	exit();
}

// Get list of mac address's for lastname / acctnum pair
$sql="select  modem_macaddr,config_file,dynamic_config_file FROM docsis_modem WHERE subnum = ? LIMIT 1";
$sth=$db->prepare($sql);
if(PEAR::isError($sth)) {
	unset($json);
	$json['Error']="STH002 ".$sth->getMessage();
	print json_encode($json);
	exit();
}

$res=$sth->execute($acctnum);
if(PEAR::isError($res)) {
	unset($json);
	$json['Error']="RES002 ".$res->getMessage();
	$json['SQL']=$sql;
	print json_encode($json);
	exit();
}
while(($row=$res->fetchRow())==true) {
	$json['mac']=$row['modem_macaddr'];
	$json['dynamic_config_file']=$row['dynamic_config_file'];
	// Set Quota in GB
	if(preg_match('/,112,/',$json['dynamic_config_file'])) {
		$json['quota']=350;
	} elseif(preg_match('/,114,/',$json['dynamic_config_file'])) {
		$json['quota']=500;
	} elseif(preg_match('/,109,/',$json['dynamic_config_file'])) {
		$json['quota']=1500;
	} elseif(preg_match('/,113,/',$json['dynamic_config_file'])) {
		$json['quota']=4000;
	} else {
		$json['quota']=0;
	}
}


unset($row);
// Get Last Updated Record for all mac addres's
$sql="select max(entry_time) as last_update FROM cable_usage WHERE sub_id = ?";
$sth=$db->prepare($sql);
if(PEAR::isError($sth)) {
	unset($json);
	$json['Error']="STH005 ".$sth->getMessage();
	print json_encode($json);
	exit();
}

$res=$sth->execute($acctnum);
if(PEAR::isError($res)) {
	unset($json);
	$json['Error']="RES005 ".$res->getMessage();
	$json['SQL']=$sql;
	print json_encode($json);
	exit();
}
while(($row=$res->fetchRow())==true) {
	$json['last_update']=$row['last_update'];
}


// Get MTD usage for all mac address's
// Download / Upload / Total
$sql="select sum(down_delta) AS down_delta, sum(up_delta) as up_delta FROM cable_usage WHERE sub_id = ?";
$sth=$db->prepare($sql);
if(PEAR::isError($sth)) {
	unset($json);
	$json['Error']="STH003 ".$sth->getMessage();
	print json_encode($json);
	exit();
}

$res=$sth->execute($acctnum);
if(PEAR::isError($res)) {
	unset($json);
	$json['Error']="RES003 ".$res->getMessage();
	$json['SQL']=$sql;
	print json_encode($json);
	exit();
}
while(($row=$res->fetchRow())==true) {
	$json['usage']=($row['down_delta']+$row['up_delta']);
	$json['usage']/=1024; // KiloBytes
	$json['usage']/=1024; // MegaBytes
	$json['usage']/=1024; // GigaBytes
	$json['usage']=round($json['usage'],2);
}


// Get historical entries for Macaddress
$sql="select month,year,sum(down_delta) as down_delta, sum(up_delta) as up_delta FROM monthly_usage WHERE sub_id = ? GROUP BY month,year ORDER BY year DESC,month ASC";
$sth=$db->prepare($sql);
if(PEAR::isError($sth)) {
	unset($json);
	$json['Error']="STH004 ".$sth->getMessage();
	print json_encode($json);
	exit();
}

$res=$sth->execute($acctnum);
if(PEAR::isError($res)) {
	unset($json);
	$json['Error']="RES004 ".$res->getMessage();
	$json['SQL']=$sql;
	print json_encode($json);
	exit();
}
while(($row=$res->fetchRow())==true) {
	$month=$row['month'];
	$year=$row['year'];
	$xfer=$row['down_delta']+$row['up_delta'];
	$date=sprintf("%s/%s",$month,$year);
	$xfer/=1024;	// KB
	$xfer/=1024;	// MB
	$xfer/=1024;	// GB
	$xfer=round($xfer,2);
	$json['history'][$date]=$xfer;
}



// print encoded values
unset($json['mac']);
unset($json['dynamic_config_file']);
print json_encode($json);
