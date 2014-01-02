<?php

$title='Averages Lister';

$sql="SELECT v2.* FROM (SELECT v1.*,c.property,c.building,c.node FROM (select m.mac,m.fwdrx,m.fwdsnr,m.revtx,m.revrx,m.revsnr,d.subnum FROM modem_history AS m LEFT OUTER JOIN docsis_modem AS d on m.mac=d.modem_macaddr WHERE m.time > date_sub(now(),INTERVAL 1 HOUR)) AS v1 LEFT OUTER JOIN customer_address AS c ON v1.subnum=c.subnum) as v2";
$Asql="CREATE TEMPORARY TABLE aaa AS ({$sql})";

require_once("../config.php");

$db = connect();

$rset=$db->query($Asql);

$body="<html><head><title>{$title}</title></head><body><table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">";
if(PEAR::isError($rset)) {
	$body.="<tr><td>".$rset->getMessage()."</td></tr>\n";
} else {
	if(isset($_GET['type']) AND $_GET['type'] == 'building') {
		$sql="SELECT DISTINCT avg(fwdrx),avg(fwdsnr),avg(revtx),avg(revrx),avg(revsnr),property,building FROM aaa GROUP BY property,building ORDER BY property,building";
	} elseif(isset($_GET['type']) AND $_GET['type'] == 'property') { 
		$sql="SELECT DISTINCT avg(fwdrx),avg(fwdsnr),avg(revtx),avg(revrx),avg(revsnr),property FROM aaa GROUP BY property ORDER BY property";
	} elseif(isset($_GET['type']) AND $_GET['type'] == 'node') {
		$sql="SELECT DISTINCT avg(fwdrx),avg(fwdsnr),avg(revtx),avg(revrx),avg(revsnr),node FROM aaa GROUP BY node ORDER BY property";
	} else {
		$sql="SELECT DISTINCT avg(fwdrx),avg(fwdsnr),avg(revtx),avg(revrx),avg(revsnr),node,property,building FROM aaa GROUP BY node,property,building ORDER BY node,property,building";
	}
	$rset = $db->query($sql);
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
			case "building":
				$url="<a href=\"/monitoring/bester.php?search={$k}&value={$v}\">{$v}</a>";
				$rv.="<td>{$url}</td>";
				break;
			case "avg(fwdrx)":
				$bgcolor=fwdRxColor($v);
				$rv.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",$bgcolor,$v);;
				break;
			case "avg(fwdsnr)":
				$bgcolor=fwdSnrColor($v);
				$rv.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",$bgcolor,$v);;
				break;
			case "avg(revtx)":
				$bgcolor=revTxColor($v);
				$rv.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",$bgcolor,$v);;
				break;
			case "avg(revrx)":
				$rv.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",$bgcolor,$v);;
				break;
			case "avg(revsnr)":
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
	$rv='';
	$rv="<td>#</td>";
	foreach($row as $k=>$v) {
		$rv.="<td>{$k}</td>";
	}
	return $rv;
}
