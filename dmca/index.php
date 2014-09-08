<?php

header("Location: tagEvent.php");
exit();
require_once("../config.php");

if(!isset($_GET['ipaddr']) && !isset($_GET['subnum'])) {
	$body="<form method=\"get\" action=\"/dmca/index.php\">";
	$body.="IP Addess: <input type=\"text\" name=\"ipaddr\" size=\"21\"><br>";
	$body.="<input type=\"submit\" value=\"Lookup\"></form>";
	buildPage($body);
	exit();
}
$conn = connect();

if(isset($_GET['subnum'])) {
	$sql="SELECT ipaddr,macaddr,start_time,end_time,pc_name,subnum,modem_macaddr FROM dmca_ip_tracking WHERE subnum='{$_GET['subnum']}' ORDER BY tstamp DESC";
} elseif(isset($_GET['ipaddr'])) {
	$sql="SELECT ipaddr,macaddr,start_time,end_time,pc_name,subnum,modem_macaddr FROM dmca_ip_tracking WHERE ipaddr='{$_GET['ipaddr']}' ORDER BY tstamp DESC";
} else {
	$sql="SELECT ipaddr,macaddr,start_time,end_time,pc_name,subnum,modem_macaddr FROM dmca_ip_tracking ORDER BY tstamp DESC";
}
$rset=$conn->query($sql);
if(PEAR::isError($rset)) {
	buildPage($rset->getMessage(),$sql);
	exit();
}

$num = $rset->numRows();
$page="<!--\n{$sql}\n-->\n";
$page.="<table width=\"100%\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
$page.="<tr><td colspan=\"6\"><form method=\"get\" action=\"/dmca/index.php\">IP Address: <input type=\"text\" name=\"ipaddr\"> <input type=\"submit\" value=\"Search\"></td><td><a href=\"tzConvert.php\">TZ Convertor</td></tr>\n";
if($num > 0) {
	$row = $rset->fetchRow();
	foreach($row as $k=>$v) {
		switch($k) {
		case "ipaddr":
			$ks[]=$k;
			$vs[]="<a href=\"/dmca/index.php?ipaddr={$v}\">{$v}</a>";
			break;
		case "modem_macaddr":
			$ks[]=$k;
			//$vs[]="<a href=\"edit_modem.php?modem_macaddr={$v}\">{$v}</a>";
			$vs[]="<a href=\"monitoring/modemHistory.php?mac={$v}\">{$v}</a>";
			$vs[]="<a href=\"/dmca/tagEntry.php?mac={$v}\"></a>";
			break;
		case "subnum":
			$ks[]=$k;
			$vs[]="<a href=\"/dmca/index.php?subnum={$v}\">{$v}</a><br><a href=\"/dmca/tagEvent.php?subnum={$v}\">Tag</a>";
			break;
		default:
			$ks[]=$k;
			$vs[]=$v;
			break;
		}
	}
	$page.="<tr><td>".implode("</td><td>",$ks)."</td></tr>\n";
	$page.="<tr><td>".implode("</td><td>",$vs)."</td></tr>\n";
}
if($num >1) {
	while(($row=$rset->fetchRow())==true) {
		unset($vs);
		foreach($row as $k=>$v) {
			switch($k) {
			case "ipaddr":
				$vs[]="<a href=\"/dmca/index.php?ipaddr={$v}\">{$v}</a>";
				break;
			case "modem_macaddr":
			//	$vs[]="<a href=\"edit_modem.php?modem_macaddr={$v}\">{$v}</a>";
				$vs[]="<a href=\"monitoring/modemHistory.php?mac={$v}\">{$v}</a>";
				break;
			case "subnum":
			//	$vs[]="<a href=\"/dmca/index.php?subnum={$v}\">{$v}</a>";
				$vs[]="<a href=\"/dmca/index.php?subnum={$v}\">{$v}</a><br><a href=\"/dmca/tagEvent.php?subnum={$v}\">Tag</a>";
				break;
			default:
				$vs[]=$v;
				break;
			}
		}
		$page.="<tr><td>".implode("</td><td>",$vs)."</td></tr>\n";
	}
}


$page.="</table>\n";
buildPage($page,$sql);


?>
