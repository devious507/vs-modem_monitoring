<?php

require_once("../../config.php");

if(isset($_GET['search']) AND isset($_GET['value'])) {
	$where=" WHERE {$_GET['search']} = '{$_GET['value']}' ";
} else {
	$where = '';
}
$sql="select thedate,thetime,sending,type,conv(b_macaddr,10,16) as mac,inet_ntoa(b_ipaddr) as ip,conv(b_modem_macaddr,10,16) as modem_mac,subnum from dhcp_log {$where} ORDER BY thedate DESC, thetime DESC LIMIT 50";

$db = connect();
$header=false;
$rset=$db->query($sql);
$table="<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
while(($row=$rset->fetchRow())==true) {
	if($header==false) {
		$table.=getHeaderRow($row);
		$header=true;
	}
	$table.="<tr>";
	foreach($row as $k=>$v) {
		switch($k) {
		case "subnum":
			if($v!=0) {
				$url="<a href=\"modem.php?search=subnum&value={$v}\">{$v}</a>";
				$table.="<td>{$url}</td>";
			} else {
				$table.="<td>{$v}</td>";
			}
			break;
		case "type":
			$url="<a href=\"monitoring/troubleshooter/lastDhcpEntries.php?search=type&value={$v}\">{$v}</a>";
			$table.="<td>{$url}</td>";
			break;
		case "mac":
			$v=macPadding($v);
			$table.="<td>{$v}</td>";
			break;
		case "modem_mac":
			$display=macPadding($v);
			$dec_mac=dechex($v);
			$url="<a href=\"monitoring/troubleshooter/lastDhcpEntries.php?search=b_modem_macaddr&value={$dec_mac}\">{$display}</a>";
			$url="<a href=\"modem.php?search=modem_macaddr&value={$display}\">{$display}</a>";
			$table.="<td>{$url}</td>";
			break;
		default:
			$table.="<td>{$v}</td>";
			break;
		}
	}
	$table.="</tr>\n";
}
$table.="</table>\n";


buildPage($table,$sql);


function macPadding($v) {
	if($v!=0) {
		$v=str_pad($v,12,'000000000000',STR_PAD_LEFT);
	} else {
		$v='';
	}
	return $v;
}
?>
