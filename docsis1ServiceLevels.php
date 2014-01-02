<?php
require_once("config.php");
checkSuper();


if(isset($_GET['ClassID'])) {
		$bot = SVC_CLASS_START - 1;
		$top = SVC_CLASS_END + 1;
		$sql="SELECT cfg_id FROM config_modem WHERE cfg_id > {$bot} AND cfg_id < {$top} ORDER BY cfg_id";
		$conn = connect();
		$rset = $conn->query($sql);
		if(PEAR::isError($rset)) {
			$body="<p>SQL ERROR:</p>";
			$body.=$rset->getMessage();
			buildPage($body,$sql);
			exit();
		}
		$rows = $rset->numRows();
		$cfg_id = SVC_CLASS_START;
		if($rows > 0) {
			while(($row=$rset->fetchRow())==true) {
				$dbID = $row['cfg_id'];
				if($dbID == $cfg_id) {
					$cfg_id++;
				}
			}
		}
		$ClassID = $_GET['ClassID'];
		$MaxRateDown = $_GET['MaxRateDown'];
		$MaxRateUp   = $_GET['MaxRateUp'];
		$PriorityUp  = $_GET['PriorityUp'];
		$MaxBurstUp  = $_GET['MaxBurstUp'];
		if(preg_match("/(M|m)/",$MaxRateDown)) {
			$MaxRateDown=preg_replace("/M/","",$MaxRateDown);
			$MaxRateDown=preg_replace("/m/","",$MaxRateDown);
			$MaxRateDown*=1024000;
		}
		if(preg_match("/(M|m)/",$MaxRateUp)) {
			$MaxRateUp=preg_replace("/M/","",$MaxRateUp);
			$MaxRateUp=preg_replace("/m/","",$MaxRateUp);
			$MaxRateUp*=1024000;
		}
		$PrivacyEnable = $_GET['PrivacyEnable'];
		$downMeg = $MaxRateDown/1024000;
		$upMeg   = $MaxRateUp/1024000;
		if($upMeg < 1) {
			$upKilo = $upMeg*1024;
		}

		$cfg_txt = "ClassOfService { ClassID {$ClassID}; MaxRateDown {$MaxRateDown}; MaxRateUp {$MaxRateUp}; ";
		$cfg_txt.= "PriorityUp {$PriorityUp}; MaxBurstUp {$MaxBurstUp}; PrivacyEnable {$PrivacyEnable}; }";

		if($upMeg < 1) {
			$comment = "{$downMeg}M / {$upKilo}K -- Privacy {$PrivacyEnable} ({$cfg_id})";
		} else {
			$comment = "{$downMeg}M / {$upMeg}M -- Privacy {$PrivacyEnable} ({$cfg_id})";
		}
		$sql="INSERT INTO config_modem (cfg_id,comment,cfg_txt,cfg_ver,cfg_update) VALUES ('{$cfg_id}','{$comment}','{$cfg_txt}','v1.0',Now())";
		$rset = $conn->query($sql);
		if(PEAR::isError($rset)) {
			buildPage($rset->getMessage(),$sql);
			exit();
		}
		header("Location: modem_configs.php");
		exit();
}

/*
(605,'Class 5 - DS 1mb - US 1mb',
	'ClassOfService { ClassID 5; MaxRateDown 1024000; MaxRateUp 1024000;
 PriorityUp 1; MaxBurstUp 1600; PrivacyEnable 0; }'),
*/
$body.="<form method=\"get\" action=\"docsis1ServiceLevels.php\">\n";
$body.="<table width=\"800\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
$body.="<tr><td>ClassOfService</td><td><input type=\"hidden\" name=\"ClassID\" value=\"1\">1</td></tr>\n";
$body.="<tr><td>Max Download</td><td><input type=\"text\" size=\"10\" name=\"MaxRateDown\"></td></tr>\n";
$body.="<tr><td>Max Upload</td><td><input type=\"text\" size=\"10\" name=\"MaxRateUp\"></td></tr>\n";
$body.="<tr><td>Priority Upload</td><td><input type=\"text\" size=\"2\" name=\"PriorityUp\" value=\"1\"></td></tr>\n";
$body.="<tr><td>Max Burst Upload</td><td><input type=\"text\" size=\"2\" name=\"MaxBurstUp\" value=\"1600\"></td></tr>\n";
$body.="<tr><td>Privacy Enable</td><td><select name=\"PrivacyEnable\"><option value=\"1\">Yes</option><option value=\"0\" selected=\"selected\">No</option></td></tr>\n";
$body.="<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Add Entry\"></td></tr>\n";
$body.="</table>\n";
$body.="</form>\n";

buildPage($body);
?>
