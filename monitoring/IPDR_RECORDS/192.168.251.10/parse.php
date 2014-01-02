<?php

$files[]='1351534028.txt';
$files[]='1351534392.txt';
$files[]='1351538991.txt';
$files[]='1351539526.txt';

parseFile($files[3]);
/*
foreach($files as $f) {
	parseFile($f);
}
 */
function parseFile($file) {
	$filename = '/var/www/monitoring/IPDR_RECORDS/192.168.251.10/' . $file;
	$xmlstr=file_get_contents($filename,filesize($filename));
	$data = new SimpleXMLElement($xmlstr);
	unset($xmlstr);
	$modems=array();
	foreach($data as $dat) {
		//print "<pre>"; var_dump($dat); exit();
		$time = $dat->IPDRcreationTime;
		$mac = fixMac($dat->CMmacAddress);
		$direction = $dat->serviceDirection;
		$octets = $dat->serviceOctetsPassed;
		if($direction == 1) {
			$modems[$mac]['time'][0]=$time;
			$modems[$mac]['up']=$octets;
		} else {
			$modems[$mac]['time'][1]=$time;
			$modems[$mac]['down']=$octets;
		}
		//printf("%s<br>\n",$time);
	}
	foreach($modems as $mod) {
	}
	//print "<pre>";
	//  94CCB945795B
	//printf("%s -- %s<br>",$modems['94CCB945795B']['time'][0],$modems['94CCB945795B']['up']);
	var_dump($modems['0017EE4678FA']);
	//print "</pre>";
}


function fixMac($mac) {
	$mac = preg_replace("/-/",'',$mac);
	$mac = preg_replace("/:/",'',$mac);
	return $mac;
}

