<?php

require_once("../config.php");

if(!isset($_GET['mac'])) {
	header("Location: index.php");
	exit();
}

$mac=$_GET['mac'];
$macLink="<a href=\"modemDump.php?mac={$mac}\">{$mac}</a>";
$mac=preg_replace('/[^a-zA-Z0-9]/','',$mac);
if(strlen($_GET['mac']) != 12) {
	header("LOcation: index.php");
	exit();
}

$sql="SELECT * FROM cable_usage WHERE modem_macaddr='{$mac}' ORDER BY entry_time ASC";
if(isset($_GET['table']) && $_GET['table']=='backup') {
	$sql="SELECT * FROM cable_usage_backup WHERE modem_macaddr='{$mac}' ORDER BY down_delta DESC";
}
//print $sql; exit();
$db=connect();
$res=$db->query($sql);
$tbl='';
$wincable=0;
while(($row=$res->fetchRow())==true) {
	if($wincable==0) {
		$wincable=$row['sub_id'];
	}
	$tbl.="<tr>\n";
	foreach($row as $k=>$v) {
		switch($k) {
		case "modem_macaddr":
			break;
		case "up_delta":
		case "down_delta":
			if($v > 1024*1024*1024) {
				$divisor=1024*1024*1024;
				$unit = 'GB';
			} elseif($v > 1024*1024) {
				$divisor=1024*1024;
				$unit = 'MB';
			} elseif($v > 1024) {
				$divisor=1024;
				$unit = 'KB';
			} else {
				$divisor=1;
				$unit='B';
			}
			$vv=sprintf("%.0f %s",$v/$divisor,$unit);
			$tbl.="\t<td align=\"right\">{$vv}</td>\n";
			break;
		default:
			$vv=sprintf("%.1f",$v);
			$tbl.="\t<td align=\"right\">{$v}</td>\n";
			//$tbl.="\t<td align=\"right\">{$vv}</td>\n";
			break;
		}
	} 
	$tbl.="</tr>\n";
}

// Customer INfo
//
$sql="SELECT * FROM customer_address WHERE subnum='{$wincable}'";
$res=$db->query($sql);
$row=$res->fetchRow();
$customerInfo="<hr>".$mac."<hr>";
$tmp=preg_split("/ /",$row['name']);
$graph_name=$tmp[count($tmp)-1];
$graph_subnum=$row['subnum'];

$customerInfo.=$row['name']."<br>";
$customerInfo.=$row['address']."<br>";
$customerInfo.=$row['apartment']."<br>";
$customerInfo.=$row['city'].", ".$row['state']." ".$row['zip']."<hr>";
$customerInfo.="<a href=\"/monitoring/modemHistory.php?mac={$mac}\" target=\"_TOP\">Modem History</a><br>";
$customerInfo.="<a href=\"index.php\">Modem Usage List</a><br>";
$customerInfo.="<a href=\"modemDump.php?mac={$mac}&table=backup\">Last Months Details</a><br>";
$customerInfo.="<a href=\"perSecondAnalysis?mac={$mac}\">Per Second Analysis</a></br>";

$sql="SELECT sum(down_delta) as d, sum(up_delta) as u FROM cable_usage WHERE modem_macaddr='{$mac}'";
if(isset($_GET['table']) && $_GET['table']=='backup') {
	$sql="SELECT sum(down_delta) as d, sum(up_delta) as u FROM cable_usage_backup WHERE modem_macaddr='{$mac}'";
}
$res=$db->query($sql);
$row=$res->fetchRow();
$d=floatval($row['d'])/1024/1024/1024;
$u=floatval($row['u'])/1024/1024/1024;
$customerInfo.="<hr><hr><table cellpadding=\"5\" cellspacing=\"0\" border=\"1\" width=\"100%\">";
$customerInfo.=sprintf("<tr><td colspan=\"2\"><b>MTD Totals</b></td></tr>");

// Bring in the graphs that were recently done (4/20/16)
$url="http://38.108.136.6/reporting/modemDump-JSON.php?acctnum={$graph_subnum}&lastname={$graph_name}";
$fh=fopen($url,'r');
$graph_data=json_decode(stream_get_contents($fh));
fclose($fh);
$img="<img src=\"http://www.visionsystems.tv/quota/quotaGraph.php?quota={$graph_data->quota}&use={$graph_data->usage}\">";

$customerInfo.=sprintf("<tr><td colspan=\"2\">%s</td></tr>",$img);
$customerInfo.=sprintf("<tr><td>Download Total</td><td align=\"right\">%.1f GB</td></tr>",$d);
$customerInfo.=sprintf("<tr><td>Upload Total</td><td align=\"right\">%.1f GB</td></tr>",$u);
$customerInfo.=sprintf("<tr><td>Upload Total</td><td align=\"right\">%.1f GB</td></tr>",$u+$d);
$customerInfo.="</table><hr><hr>";
$sql="SELECT month,year,modem_macaddr,down_delta/1024/1024/1024 as down,up_delta/1024/1024/1024 as up,(down_delta+up_delta)/1024/1024/1024 as total FROM monthly_usage WHERE sub_id='{$wincable}' ORDER BY year DESC,cast(month AS unsigned) DESC LIMIT 12";
$res=$db->query($sql);

$customerInfo.="<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\" width=\"100%\">";
$customerInfo.="<tr><td>Period</td><td>MAC</td><td align=\"right\">Down</td><td align=\"right\">Up</td><td>Total</td></tr>";
while(($row=$res->fetchRow())==true) {
	//print "<pre>"; var_dump($row); print "</pre>"; exit();
	$url="<a href=\"/monitoring/modemHistory.php?mac={$row['modem_macaddr']}\">{$row['modem_macaddr']}</a>";
	$customerInfo.=sprintf("<tr><td>%02d/%d</td><td>%s</td><td align=\"right\">%.1f GB</td><td align=\"right\">%.1f GB</td><td>%.1f GB</td></tr>",
		$row['month'],
		$row['year'],
		$url,
		$row['down'],
		$row['up'],
		$row['total']);
}
$customerInfo.="</table><hr><hr>";

?>
<html><head><title>Modem detail</title></head>
<body>
<table cellpadding="5" cellspacing="0" border="0">
<tr rowspan="2"><td>
<table cellpadding="5" cellspacing="0" border="1">
<tr><td>Entry Time</td><td>Acct #</td><td>Down Counter</td><td>Up Counter</td><td>Down Delta</td><td>Up Delta</td></tr>
<?php echo $tbl; ?>
</table>
</td>
<td valign="top">
<?php echo $customerInfo; ?>
</td></tr>
</table>
</body>
</html>
