<?php

require_once("config.php");

$valid_sorts=array('mac','fwdrx','fwdsnr','revtx','revrx','revsnr','ip','time','primchannel');
$order = '';
if(isset($_GET['sort'])) {
	$sort=$_GET['sort'];
	foreach($valid_sorts as $k=>$v) {
		if($v == $sort) {
			$order = " ORDER BY {$v} ASC";
		}
	}
}
$where='';
if(isset($_GET['limit']) && isset($_GET['value'])) {
	$where.="AND {$_GET['limit']} = '{$_GET['value']}'";
}
$date = date('Y-m-d h:i:s',time()-3600);
$sql = "select mac,fwdrx,fwdsnr,revtx,revrx,revsnr,ip,time,primchannel from modem_history where time > '{$date}' {$where} {$order}";
$conn = connect();
$rset=$conn->query($sql);
if(isset($_GET['print']) && $_GET['print']==true) {
	print "<html><head><title>Export View</title></head><body><form method=\"post\" action=\"http://www.visionsystems.tv/~paulo/modemID/action.php\"><textarea name=\"pastebin\" rows=\"20\" cols=\"100\">";
	while(($row=$rset->fetchRow())==true) {
		$mac[0]=substr($row['mac'],0,4);
		$mac[1]=substr($row['mac'],4,4);
		$mac[2]=substr($row['mac'],8,4);
		$row['mac']=strtolower(implode(".",$mac));
		print implode(" ",$row);
		print "\n";
	}
	print "</textarea><br><input type=\"submit\" value=\"Submit\"></form></body></html>";
	exit();
}

$body="<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
$headers='';
$count=0;
while(($row=$rset->fetchRow())==true) {
	if($headers=='') {
		$headers=getHeaders($row,$_GET);
		$body.=$headers;
	}
	$body.="\t<tr>";
	$count++;
	$body.="<td>{$count}</td>";
	foreach($row as $k=>$v) {
		switch($k) {
		case "mac":
			$url="<a href=\"monitoring/modemHistory.php?mac={$v}\">{$v}</a>";
			$body.="<td>{$url}</td>";
			break;
		case "fwdrx":
		case "fwdsnr":
		case "revtx":
		case "revrx":
		case "revsnr":
			$body.=sprintf("<td align=\"right\">%.1f</td>",$v);
			break;
		case "primchannel":
			$body.="<td><a href=\"monitoring/modemList.php?sort={$k}&limit={$k}&value={$v}\">{$v}</a></td>";
			break;
		default:
			$body.="<td>{$v}</td>";
			break;
		}
	}
	$body.="</tr>\n";
}
$body.="</table>\n";
buildPage($body,$sql);


function getHeaders($r,$g) {
	$qs=$_SERVER['QUERY_STRING'];
	$rv="<tr><td colspan=\"10\"><a href=\"monitoring/modemList.php?{$qs}&print=true\">Printable View</a></td></tr>\n";
	$rv.="\t<tr>";
	$rv.="<td>#</td>";
	foreach($r as $k=>$v) {
		if(isset($g['limit']) && isset($g['value'])) {
			$url="<a href=\"monitoring/modemList.php?sort={$k}&limit={$g['limit']}&value={$g['value']}\">{$k}</a>";
		} else {
			$url="<a href=\"monitoring/modemList.php?sort={$k}\">{$k}</a>";
		}
		$rv.="<td>{$url}</td>";
	}
	$rv.="</tr>\n";
	return $rv;
}
?>
