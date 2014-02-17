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

$sql="SELECT * FROM cable_usage WHERE modem_macaddr='{$mac}'";
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
$customerInfo.=$row['name']."<br>";
$customerInfo.=$row['address']."<br>";
$customerInfo.=$row['apartment']."<br>";
$customerInfo.=$row['city'].", ".$row['state']." ".$row['zip']."<hr>";
$customerInfo.="<a href=\"/monitoring/modemHistory.php?mac={$mac}\" target=\"_TOP\">Modem History</a><br>";
$customerInfo.="<a href=\"index.php\">Modem Usage List</a><br>";
?>
<html><head><title>Modem detail</title></head>
<body>
<table cellpadding="5" cellspacing="0" border="0">
<tr><td>
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
