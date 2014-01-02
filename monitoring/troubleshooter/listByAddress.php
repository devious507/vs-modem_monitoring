<?php

require_once("../config.php");

$data=preg_split("/\n/",$_POST['pastebin']);
unset($_POST['pastebin']);
$header=false;
$create=false;
$body="<html><head><title></title></head><body><table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
$body.="<tr><td colspan=\"16\">{$_POST['sql']}</td>";
foreach($data as $line) {
	if(!preg_match("/ /",$line)) {
		$line=$line." hi";
	}
	$tmp=preg_split("/ /",$line);
	$tmp[0]=trim($tmp[0]);
	$tmp[0]=strtoupper(preg_replace("/\./","",$tmp[0]));
	$sql="SELECT a.*,m.fwdrx,m.fwdsnr,m.revtx,m.revrx,m.revsnr,m.time,m.primchannel FROM (select d.modem_macaddr,d.subnum,d.config_file,c.node,c.property,c.building,c.address,c.apartment from docsis_modem AS d LEFT OUTER JOIN customer_address AS c ON d.subnum=c.subnum WHERE d.modem_macaddr='{$tmp[0]}') AS a LEFT OUTER JOIN modem_history AS m ON a.modem_macaddr=m.mac";
	if($create==false) {
		$create=true;
		$Asql="CREATE TEMPORARY TABLE aaa AS ({$sql})";
		$sql=$Asql;
	} else {
		$Asql="INSERT INTO aaa ({$sql})";
		$sql=$Asql;
	}
	unset($Asql);
	$db=connect();
	$rset=$db->query($sql);
	if(PEAR::isError($rset)) {
		//print $rset->getMessage()."<br>\n";
		print $sql."<br>\n";
	}
}
$sql="SELECT * FROM aaa ORDER BY property,node,building,address,apartment";
$rset=$db->query($sql);
$count=0;
while(($row=$rset->fetchRow())==true) {
	$count++;
	if($header==false) {
		$header=true;
		$body.="\t<tr><td>#</td>";
		foreach($row as $k=>$v) {
			$body.="<td>{$k}</td>";
		}
		$body.="</tr>\n";
	}
	$body.="\t<tr><td>{$count}</td>";
	foreach($row as $k=>$v) {
		$bgcolor="white";
		switch($k) {
		case "fwdrx":
			$bgcolor=fwdrxColor($v);
			$body.=sprintf("<td align=\"right\" bgcolor=\"$bgcolor\">%.1f</td>",$v);
			break;
		case "fwdsnr":
			$bgcolor=fwdsnrColor($v);
			$body.=sprintf("<td align=\"right\" bgcolor=\"$bgcolor\">%.1f</td>",$v);
			break;
		case "revtx":
			$bgcolor=revtxColor($v);
			$body.=sprintf("<td align=\"right\" bgcolor=\"$bgcolor\">%.1f</td>",$v);
			break;
		case "revrx":
			$bgcolor='white';
			$body.=sprintf("<td align=\"right\" bgcolor=\"$bgcolor\">%.1f</td>",$v);
			break;
		case "revsnr":
			$bgcolor=revsnrColor($v);
			$body.=sprintf("<td align=\"right\" bgcolor=\"$bgcolor\">%.1f</td>",$v);
			break;
		case "subnum":
		case "node":
		case "property":
		case "building":
			$link="<a href=\"/monitoring/bester.php?search={$k}&value={$v}\">{$v}</a>";
			$body.="<td bgcolor=\"{$bgcolor}\">$link</td>";
			break;
		case "modem_macaddr":
			$link="<a href=\"/monitoring/modemHistory.php?mac={$v}\">{$v}</a>";
			$body.="<td bgcolor=\"{$bgcolor}\">$link</td>";
			break;
		case "time":
			$bgcolor = timeColor($v);
			$body.="<td bgcolor=\"{$bgcolor}\">{$v}</td>";
			break;
		default:
			$body.="<td>{$v}</td>";
			break;
		}
	}
	$body.="</tr>\n";
}

$body.="</table></body></html>";
print $body;

function timeColor($time) {
	$arr=sscanf($time,"%d-%d-%d %d:%d:%d");
	$thenTime=mktime($arr[3],$arr[4],$arr[5],$arr[1],$arr[2],$arr[0]);
	$nowTime=time();
	$diff = $nowTime-$thenTime;
	if($diff < 600) {
		return "lightgreen";
	} elseif($diff < 3600) {
		return "yellow";
	} elseif($diff < 43200) {
		return "#cc3333";
	} else {
		return "red";
	}
}
?>
