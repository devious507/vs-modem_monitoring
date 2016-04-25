<?php

$title='Averages Lister';

$sql="SELECT * FROM research1 ";

require_once("../config.php");

$db = connect();


if(isset($_GET['order']) && isset($_GET['orderdir'])) {
	$sql.="ORDER BY {$_GET['order']} {$_GET['orderdir']}";
} else {
	header("Location: /monitoring/troubleshooter/research1.php?order=resets&orderdir=desc");
	exit();
}
$rset=$db->query($sql);

$body="<html><head><title>{$title}</title></head><body><table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">";
if(PEAR::isError($rset)) {
	$body.="<tr><td>".$rset->getMessage()."</td></tr>\n";
} else {
	$body.=sqlTable($rset);
}
$body.="</table></body></html>";
print $body;


function sqlTable($rset) {
	$header=false;
	$rv='';
	$count=0;
	while(($row=$rset->fetchRow())==true) {
		$count++;
		$rv.="<tr>";
		if($header==false) {
			$header=true;
			$rv.=sqlHeader($row);
			$rv.="</tr>\n<tr>";
		}
		$rv.="<td>{$count}</td>";
		foreach($row as $k=>$v) {
			$bgcolor='white';
			switch($k) {
			case "node":
			case "property":
				$property=$v;
			case "building":
				$url="<a href=\"/monitoring/bester.php?search={$k}&value={$v}\">{$v}</a>";
				$rv.="<td>{$url}</td>";
				break;
			case "mac":
				$url="<a href=\"/monitoring/modemHistory.php?mac={$v}\">{$v}</a>";
				$rv.="<td>{$url}</td>";
				break;
			case "fwdrx":
				$bgcolor=fwdRxColor($v);
				$rv.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",$bgcolor,$v);;
				break;
			case "fwdsnr":
				$bgcolor=fwdSnrColor($v);
				$rv.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",$bgcolor,$v);;
				break;
			case "revtx":
				$bgcolor=revTxColor($v);
				$rv.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",$bgcolor,$v);;
				break;
			case "revrx":
				$bgcolor=revRxColor($v,$property);
				$rv.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",$bgcolor,$v);;
				break;
			case "revsnr":
				$bgcolor=revSnrColor($v);
				$rv.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",$bgcolor,$v);;
				break;
			default:
				$rv.="<td bgcolor=\"{$bgcolor}\">{$v}</td>";
				break;
			}
		}
		$rv.="</tr>\n";
	}
	return $rv;
}
function sqlHeader($row) {
	global $_GET;
	$order=$_GET['order'];
	$orderdir=$_GET['orderdir'];
	$rv='';
	$rv="<td>#</td>";
	foreach($row as $k=>$v) {
		switch($k) {
		case "fwdrx":
		case "fwdsnr":
		case "revtx":
		case "revrx":
		case "revsnr":
		case "resets":
			if($k == $order) {
				if($orderdir == 'asc') {
					$myorderdir='desc';
				} else {
					$myorderdir='asc';
				}
			} else {
				$myorderdir='asc';
			}
			$url="<a href=\"/monitoring/troubleshooter/research1.php?order={$k}&orderdir={$myorderdir}\">{$k}</a>";
			$rv.="<td>{$url}</td>";
			break;
		default:
			$rv.="<td>{$k}</td>";
			break;
		}
	}
	return $rv;
}
