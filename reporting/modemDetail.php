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

$sql="SELECT entry_time,down_delta,down_delta/1024 as down_k,down_delta/1024/1024 as down_m,down_delta/1024/1024/1024 as down_g,up_delta,up_delta/1024 as up_k,up_delta/1024/1024 as up_m,up_delta/1024/1024/1024 as up_g  FROM cable_usage WHERE modem_macaddr='{$mac}' ORDER BY entry_time ASC";
//print $sql; exit();
$db=connect();
$res=$db->query($sql);
$tbl='';
while(($row=$res->fetchRow())==true) {
	$tbl.="<tr>\n";
	foreach($row as $k=>$v) {
		switch($k) {
		case "entry_time":
			$link="<a href=\"lineDetail.php?mac={$_GET['mac']}&entry_time={$v}\">{$v}</a>";
			$tbl.="\t<td align=\"right\">{$link}</td>\n";
			break;
		case "down_delta":
		case "up_delta":
			$tbl.="\t<td align=\"right\">{$v}</td>\n";
			break;
		default:
			$vv=sprintf("%.1f",$v);
			$tbl.="\t<td align=\"right\">{$vv}</td>\n";
			break;
		}
	} 
	$tbl.="</tr>\n";
}
?>
<html><head><title>Modem detail</title></head>
<body>
<table cellpadding="5" cellspacing="0" border="1">
<tr><td colspan="9" align="center"><?php echo $macLink; ?></td></tr>
<tr><td rowspan="2">Period End Time</td><td colspan="4" align="center">Downloaded</td><td colspan="4" align="center">Upload</td></tr>
<tr><td align="right">B</td><td align="right">kB</td><td align="right">mB</td><td align="right">gB</td><td align="right">B</td><td align="right">kB</td><td align="right">mB</td><td align="right">gB</td></tr>
<?php echo $tbl; ?>
</table>
</body>
</html>
