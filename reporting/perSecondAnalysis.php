<?php

require_once("../config.php");

if(!isset($_GET['mac'])) {
	        header("Location: index.php");
		        exit();
}

$old_time = 0;
$now_time = 0;
$mac=$_GET['mac'];
$sql="SELECT * FROM cable_usage WHERE modem_macaddr='{$mac}' ORDER BY entry_time ASC";
$db=connect();
$res=$db->query($sql);
$tdata='';
while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC))==true) {
	if($old_time == 0) {
		$old_time=$row['entry_time'];
		$now_time=$row['entry_time'];
	} else {
		$bits_down = $row['down_delta'];
		$bits_up   = $row['up_delta'];
		$old_time=$now_time;
		$now_time=$row['entry_time'];
		$old = myMkTime($old_time);
		$now = myMkTime($now_time);
		$seconds = $now-$old;
		$down_xfer = convertKMG($bits_down);
		$up_xfer = convertKMG($bits_up);
		$down_persec = convertKMG($bits_down/$seconds,false);
		$up_persec = convertKMG($bits_up/$seconds,false);
		$tdata.="<tr><td>{$row['entry_time']}</td><td align=\"right\">{$seconds}</td>";
		$tdata.="<td align=\"right\" bgcolor=\"#cacaca\">{$down_xfer}</td><td align=\"right\">{$up_xfer}</td><td align=\"right\" bgcolor=\"#cacaca\">{$down_persec}/s</td><td align=\"right\">{$up_persec}/s</td></tr>\n";
	}
}

function convertKMG($bits,$dobytes=true) {
	if($dobytes == true) {
		$bytes=$bits;
		$bB = "B";
	} else {
		$bytes = $bits*8;
		$bB = 'b';
	}
	$suffix = $bB;
	if($bytes > 1024) {
		$bytes/=1024;
		$suffix = "K".$bB;
	} 
	if($bytes > 1024) {
		$bytes/=1024;
		$suffix = "M".$bB;
	}
	if($bytes >= 1024) {
		$bytes/=1024;
		$suffix = "G".$bB;
	}
	if($dobytes == true) {
		$bytes=round($bytes,1);
	} else {
		$bytes=round($bytes,1);
	}
	return sprintf("%.01f %s",$bytes,$suffix);
	//return $bytes." ".$suffix;
}
function myMkTime($str) {
	$tmp=preg_split("/ /",$str);
	$date=preg_split("/-/",$tmp[0]);
	$time=preg_split("/:/",$tmp[1]);
	$unix_time = mktime($time[0],$time[1],$time[2],$date[1],$date[2],$date[0]);

	/*print $str."<br>";
	printf("mktime(%d,%d,%d,%d,%d,%d)<br>",$time[0],$time[1],$time[2],$date[2],$date[1],$date[0]);
	print $unix_time."<br>";
	print date("Y-m-d H:i:s",$unix_time);
	exit();
	 */
	return $unix_time;
}

?>
<html>
<head><title>Per Second Analysis</title></head>
<body>
<table border="1" cellpadding="5" cellspacing="0">
<tr><td colspan="2"><b><?php echo $mac;?></b></td><td colspan="2" align="center">Xfer Bytes</td><td colspan="2" align="center">Xfer Speed</td></tr>
<tr><td>Entry Time</td><td># Seconds</td><td align="right" bgcolor="#cacaca">Down</td><td align="right">Up</td><td align="right" bgcolor="#cacaca">Down</td><td align="right">Up</td></tr>
<?php echo $tdata; ?>
</table>
</body>
</html>
