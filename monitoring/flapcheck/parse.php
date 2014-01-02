<?php

define('MYSQL_HOST','localhost');
define('MYSQL_USER','docsis');
define('MYSQL_PASS','99rdblns');
define('MYSQL_DB','dhcp_server');


require_once("/var/www/functions.php");
$dataFile = '/var/www/monitoring/flapcheck/FLAP';

$contents = file_get_contents($dataFile);
$data = preg_split("/\r\n/",$contents);
unset($contents);

$db = connect();
foreach($data as $line) {
	if(preg_match("/^....\.....\...../",$line)) {
		$line = preg_replace("/( )\\1+/","$1",$line);
		$tmpArr = preg_split("/ /",$line);
		$time = $tmpArr[8]." ".$tmpArr[9]." ".$tmpArr[10];
		$tmpArr[8]=$time;
		unset($tmpArr[9]);
		unset($tmpArr[10]);
		$hitMissTotal = $tmpArr[3]+$tmpArr[4];
		$tmpArr[9] = round($tmpArr[4]/$hitMissTotal*100);
		if(preg_match("/^!/",$tmpArr[6])) {
			$tmpArr[10] = true; 
		} else {
			$tmpArr[10] = false;
		}
		if(preg_match("/^\*/",$tmpArr[6])) {
			$tmpArr[11] = true; 
		} else {
			$tmpArr[11] = false;
		}
		$tmpArr[6] = preg_replace("/(!|\*)/","",$tmpArr[6]);
		foreach($tmpArr AS $k=>$v) {
			switch($k) {
			case 0:
			case 1:
			case 8:
				$tmpArr[$k]="'".$v."'";
				break;
			case 10:
			case 11:
				if($v == true) {
					$tmpArr[$k]='true';
				} else {
					$tmpArr[$k]='false';
				}
				break;
			default:
				break;
			}
		}
		$sql="INSERT INTO flap_logging VALUES (default,".implode(",",$tmpArr).")";
		$res = $db->query($sql);
		if(PEAR::isError($res)) {
			print $res->getMessage();
			exit();
		}
		print $sql."\n";
	}
}
$db->query("DELETE FROM flap_logging WHERE entrytime < date_sub(now(), interval 14 day");
$db->commit();
$db->query("OPTIMIZE TABLE flap_logging");
$db->commit();
$db->disconnect();
