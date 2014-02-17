<?php

require_once("../config.php");

if(!isset($_GET['direction'])) {
	print "<html><head><title>Direction Choice</title></head><body>";
	print "<a href=\"reportUsage.php?direction=up\">Up</a><br>";
	print "<a href=\"reportUsage.php?direction=down\">Down</a><br>";
	print "</body></html>";
	exit();
}
switch($_GET['direction']) {
case "up":
case "down":
	$sql="SELECT modem_macaddr,sub_id,sum(%s_delta) as %s,sum(%s_delta)/1024 as kilo,sum(%s_delta)/1024/1024 as meg";
	$sql.=",sum(%s_delta)/1024/1024/1024 as gig";
	$sql.=" FROM cable_usage GROUP BY modem_macaddr,sub_id ORDER BY %s DESC";
	$d=$_GET['direction'];
	$sql=sprintf($sql,$d,$d,$d,$d,$d,$d);
	break;
case "combined":
	$sql="SELECT modem_macaddr,sub_id,sum(down_delta+up_delta) as bytes,sum(down_delta+up_delta)/1024 as kilo,sum(down_delta+up_delta)/1024/1024 as meg";
	$sql.=",sum(down_delta+up_delta)/1024/1024/1024 as gig";
	$sql.=" FROM cable_usage GROUP BY modem_macaddr,sub_id ORDER BY bytes DESC";
	//print $sql; exit();
	break;
default:
	print "Error";
	exit();
}


$d=$_GET['direction'];
$sql=sprintf($sql,$d,$d,$d,$d,$d,$d);

$db=connect();
$results=$db->query($sql);
$tbl='';
$count=1;
while(($row=$results->fetchRow())==true) {
	$tbl.="<tr>\n";
	$tbl.="\t<td>{$count}</td>\n";
	$count++;
	foreach($row as $k=>$v) {
		switch($k) {
		case "down":
			$tbl.="\t<td align=\"right\">{$v}</td>\n";
			break;
		case "kilo":
		case "meg":
		case "gig":
			$vv=sprintf("%.1f",$v);
			$tbl.="\t<td align=\"right\">{$vv}</td>\n";
			break;
		case "modem_macaddr":
			$link="<a href=\"modemDetail.php?mac={$v}\">{$v}</a>";
			$tbl.="\t<td>{$link}</td>\n";
			break;
		default:
			$tbl.="\t<td>{$v}</td>\n";
			break;
		}
	}
	$tbl.="</tr>\n";
}
?>
<html>
<head><title></title></head>
<body>
<table cellpadding="5" cellspacing="0" border="1">
<tr><td>&nbsp;</td><td>Mac</td><td>Wincable</td><td>Bytes</td><td>kBytes</td><td>mBytes</td><td>gBytes</td></tr>
<?php echo $tbl; ?>
</table>
</body>
</html>
