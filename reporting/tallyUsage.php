<?php

require_once("../config.php");

$db=connect();
//$sql="select * from cable_usage WHERE modem_macaddr='001A668A6EA4' order by modem_macaddr,entry_time ASC";
$sql="select * from cable_usage order by modem_macaddr,entry_time ASC";
$results = $db->query($sql);
$lastModem="QQ:QQ;QQ;QQ;QQ;QQ";
$lastDown=0;
$lastUp  =0;
while(($row=$results->fetchRow())==true) {
	if($row['modem_macaddr'] != $lastModem) {
		$lastModem=$row['modem_macaddr'];
		$lastDown=floatval($row['down_ct']);
		$lastUp=floatval($row['up_ct']);
		$sql="UPDATE cable_usage set down_delta=0,up_delta=0 WHERE modem_macaddr='%s' AND entry_time='%s'";
		if($row['down_delta'] == NULL OR $row['up_delta'] == NULL) {
			$sql=sprintf($sql,$row['modem_macaddr'],$row['entry_time']);
			$db->query($sql);
			print $sql."\n";
		}
	} else {
		$down=floatval($row['down_ct'])-$lastDown;
		$up  =floatval($row['up_ct'])-$lastUp;
		if($down < 0) {
			$down=floatval($row['down_ct']);
		}
		if($up < 0) {
			$up=floatval($row['up_ct']);
		}
		/*
		if($row['entry_time'] == '2014-02-16 09:23:34') {
			var_dump($row['down_ct']);
			var_dump(floatval($row['down_ct']));
			var_dump($lastDown);
			var_dump($down);
			exit();
		}
		 */
		$lastDown = floatval($row['down_ct']);
		$lastUp   = floatval($row['up_ct']);
		$sql="UPDATE cable_usage set down_delta=%.0f,up_delta=%.0f WHERE modem_macaddr='%s' AND entry_time='%s'";
		$sql=sprintf($sql,$down,$up,$row['modem_macaddr'],$row['entry_time']);
		if($row['down_delta'] == NULL OR $row['up_delta'] == NULL) {
			$db->query($sql);
			print $sql."\n";
		}
	}
	flush();
}
?>
