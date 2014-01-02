<?php

require_once("../../config.php");

$db =connect();
if(isset($_GET['mac'])) {
	$where = " WHERE mac='{$_GET['mac']}'";
} elseif(isset($_GET['maxed'])) {
	$where = " WHERE maxed=true";
} elseif(isset($_GET['p_adjusting'])) {
	$where = " WHERE p_adjusting=true";
} else {
	$where = ' WHERE entrytime > date_sub(now(), interval 60 minute) ';
}

if(isset($_GET['sort'])) {
	$order = " ORDER BY {$_GET['sort']} ";
	if(isset($_GET['order'])) {
		$order.= " {$_GET['order']} ";
	}
} else {
	$order = " ORDER BY mac, entrytime DESC";
}

$sql = "SELECT entrytime,mac,upstream,ins,hit,miss,crc,p_adj,flap,miss_pct as '% Miss',maxed,p_adjusting As adj FROM flap_logging {$where} {$order}";
$res = $db->query($sql);
if(PEAR::isError($res)) {
	print $sql."<br>\n";
	print $res->getMessage();
	exit();
}


$body="<!--\n{$sql}\n-->\n\n";
$body.="<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
if(isset($_GET['mac'])) {
	$mac=preg_replace("/\./","",$_GET['mac']);
	$url="<a href=\"/monitoring/modemHistory.php?mac={$mac}\">Modem History</a>";
	$body.="\t<tr><td colspan=\"12\">{$url}</a></td>\n";
}
while(($row = $res->fetchRow())==true) {
	$body.="\t<tr>";
	if(!isset($has_header)) {
		$body.=getHeader($row);
		$has_header=true;
	}
	foreach($row as $k=>$v) {
		switch($k) {
		case "mac":
			$body.="<td><a href=\"monitoring/flapcheck/index.php?mac={$v}\">{$v}</a></td>";
			break;
		case "maxed":
			if($v == 1) {
				$body.="<td><a href=\"monitoring/flapcheck/index.php?maxed=true\">Yes</a></td>";
			} else {
				$body.="<td>No</td>";
			}
			break;
		case "adj":
			if($v == 1) {
				$body.="<td><a href=\"monitoring/flapcheck/index.php?p_adjusting=true\">Yes</a></td>";
			} else {
				$body.="<td>No</td>";
			}
			break;
		default:
			$body.="<td>".$v."</td>";
			break;
		}
	}
	$body.="</tr>\n";
}
$body.="\t<tr><td colspan=\"12\"><a href=\"/monitoring/flapcheck/index.php\">Unfiltered List</a></td></tr>\n";
$body.="</table>\n";
buildPage($body);


function getHeader($row) {
	$rv="\t<tr>";
	foreach($row as $k=>$v) {
		switch($k) {
		case "ins":
		case "miss":
		case "hit":
		case "crc":
		case "flap":
		case "p_adj":
			$rv.="<td><b><a href=\"monitoring/flapcheck/index.php?order=DESC&sort={$k}\">{$k}</a></b></td>";
			break;
		case "% miss":
			$rv.="<td><b><a href=\"monitoring/flapcheck/index.php?order=DESC&sort=miss_pct\">{$k}</a></b></td>";
			break;
		default:
			$rv.="<td><b>".$k."</b></td>";
			break;
		} 
	}
	$rv.="</tr>\n";
	return $rv;
}

?>
