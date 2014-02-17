<?php

require_once("../../../config.php");
if(isset($argv[1])) {
	$modemSubArray=getMacSubArray();
	parseFile($argv[1],$modemSubArray);
}


function getMacSubArray() {
	$return=array();
	$sql="select modem_macaddr,subnum FROM docsis_modem";
	$db = connect();
	$rset = $db->query($sql);
	while(($row=$rset->fetchRow())==true) {
		$return[$row['modem_macaddr']]=$row['subnum'];
	}
	return $return;
}
function parseFile($file,$modemSubs) {
	$db=connect();
	$filename = '/var/www/monitoring/IPDR_RECORDS/192.168.251.10/' . $file;
	$move_file = '/var/www/monitoring/IPDR_RECORDS/192.168.251.10/parsed/'.$file;
	$data = new SimpleXMLElement(file_get_contents($filename));
	$modems=array();
	foreach($data as $dat) {
		//print "<pre>"; var_dump($dat); exit();
		$time = IPDRTime2Localtime(sprintf("%s",$dat->IPDRcreationTime));
		$mac = fixMac($dat->CMmacAddress);
		$direction = $dat->serviceDirection;
		$octets = $dat->serviceOctetsPassed;
		if($mac == "") {
			//
		} else {
			$subnum = $modemSubs[$mac];
			$modems[$mac]['subnum']=$subnum;
			if($direction == 1) {
				$modems[$mac]['timeup']=$time;
				$modems[$mac]['down']=$octets;
				$modems[$mac]['mac']=$mac;
			} else {
				$modems[$mac]['timedown']=$time;
				$modems[$mac]['up']=$octets;
			}
		}
			//printf("%s<br>\n",$time);
	}
	unset($data);
	foreach($modems as $mod) {
		if(!isset($mod['up']) && !isset($mod['down'])) {
			print "\t\t\t\tSkipping {$mod['mac']}\n";
		} else {
			$sql="INSERT INTO cable_usage VALUES ('%s','%s','%s','%s','%s',NULL,NULL)";
			$sql=sprintf($sql,$mod['mac'],$mod['timeup'],$mod['subnum'],$mod['down'],$mod['up']);
			print $sql."\n";
			$db->query($sql);
		}
	}
	rename($filename,$move_file);
}


function fixMac($mac) {
	$mac = preg_replace("/-/",'',$mac);
	$mac = preg_replace("/:/",'',$mac);
	return $mac;
}

function IPDRTime2Localtime($tstamp = '2012-10-29T19:26:11Z') {
	$pat = '%d-%d-%dT%d:%d:%dZ';
	$arr = sscanf($tstamp,$pat);
	$time = mktime($arr[3],$arr[4],$arr[5],$arr[1],$arr[2],$arr[0]);
	return date('Y-m-d H:i:s',$time);
}

