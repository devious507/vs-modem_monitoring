<?php

require_once("config.php");

$meURL="/monitoring/bester.php";
if(isset($_GET['offline']) AND $_GET['offline']=='true') {
	$where = "WHERE lastcontact < now()-600";
	$where = "WHERE lastcontact < DATE_SUB(now(), INTERVAL 10 MINUTE)";
	if(isset($_GET['hours'])) {
		$where.=" AND lastcontact > DATE_SUB(now(), INTERVAL {$_GET['hours']} HOUR)";
	}
} elseif(isset($_GET['search']) AND isset($_GET['value'])) {
	switch($_GET['search']) {
	case "nonode":
		if($_GET['value'] == 1) {
			$where="WHERE c.node IS NULL AND a.subnum < 99000";
		} else {
			$where="WHERE c.node IS NULL";
		}
		if(isset($_GET['hideaddress'])) {
			$order="ORDER BY a.config_file,a.subnum";
		} else {
			$order="ORDER BY a.subnum";
		}
		break;
	case "subnum":
		$where="WHERE a.{$_GET['search']} = '{$_GET['value']}'";
		break;
	default:
		if($_GET['value'] != '') {
			$where="WHERE {$_GET['search']} = '{$_GET['value']}'";
		} else {
			$where='';
		}
		break;
	}
} else {
	$where='';
}

if(!isset($order)) {
	$order="ORDER BY property,node,building,apartment,mac";
}
if(isset($_GET['order'])) {
	$order="ORDER BY {$_GET['order']} ASC";
}

if(isset($_GET['hideaddress']) AND $_GET['hideaddress']==true) {
	$sql="SELECT c.franch,c.subnum,c.node,c.property,c.building,a.* FROM (select d.config_file,d.subnum,h.mac,h.fwdrx,h.fwdsnr,h.revtx,h.revrx,h.revsnr,h.primchannel,h.interface,h.time AS lastcontact FROM modem_history AS h LEFT OUTER JOIN docsis_modem AS d ON h.mac=d.modem_macaddr) as a LEFT OUTER JOIN customer_address AS c ON a.subnum=c.subnum {$where} {$order}";
	$avgIndent=7;
	$topColSpan=15;
} else {
	$sql="SELECT c.franch,c.subnum,c.name,c.address,c.apartment,c.node,c.property,c.building,a.* FROM (select d.subnum,h.mac,h.fwdrx,h.fwdsnr,h.revtx,h.revrx,h.revsnr,h.primchannel,h.interface,h.time AS lastcontact FROM modem_history AS h LEFT OUTER JOIN docsis_modem AS d ON h.mac=d.modem_macaddr) as a LEFT OUTER JOIN customer_address AS c ON a.subnum=c.subnum {$where} {$order}";
	$avgIndent=9;
	$topColSpan=17;
}

$db=connect();

$body="<html><head><title></title></head><body><!--\n{$sql}\n-->\n<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
$rset=$db->query($sql);
if(PEAR::isError($rset)) {
	print $sql."<BR>\n";
	print $rset->getMessage()."<br>\n";
	exit();
}
$header=false;
$property='';
$count=0;
$revRxZeros=0;
$tot['fwdrx']=0;
$tot['fwdsnr']=0;
$tot['revtx']=0;
$tot['revrx']=0;
$tot['revsnr']=0;
if(preg_match("/hideaddress=true/",$_SERVER['REQUEST_URI'])) {
	$url=preg_replace("/\&hideaddress=true/","",$_SERVER['REQUEST_URI']);
	$url=preg_replace("/\?hideaddress=true/","",$url);
} else {
	if(preg_match("/\?/",$_SERVER['REQUEST_URI'])) {
		$url=$_SERVER["REQUEST_URI"]."&hideaddress=true";
	} else {
		$url=$_SERVER["REQUEST_URI"]."?hideaddress=true";
	}
}

$offlineURL="<a href=\"/monitoring/bester.php?offline=true\">Offline Modems</a>";
$noAddress="<a href=\"/monitoring/bester.php?search=nonode&value=0&hideaddress=true\">No Address Info</a>";
$online24OfflineNow="<a href=\"/monitoring/bester.php?offline=true&hours=24\">Reported in Last 24 Hrs. Offline Now</a>";
if(!isset($_GET['hideaddress']) OR ($_GET['hideaddress']!='true')) {
	$body.="<tr><td colspan=\"{$topColSpan}\"><a href=\"/index.php\">Main Page</a> | <a href=\"{$url}\">Hide Names and Addresses</a> | <a href=\"/monitoring/bester.php\">Show All Modems</a> | {$offlineURL} | {$noAddress} | {$online24OfflineNow} </td></tr>\n";
} else {
	$body.="<tr><td colspan=\"{$topColSpan}\"><a href=\"/index.php\">Main Page</a> | <a href=\"{$url}\">Show Names and Addresses</a> | <a href=\"/monitoring/bester.php\">Show All Modems</a> | {$offlineURL} | {$noAddress} | {$online24OfflineNow} </td></tr>\n";

}
$body.="\t<tr><td colspan=\"{$topColSpan}\"><hr></td></tr>\n";
while(($row=$rset->fetchRow())==true) {
	$count++;
	$body.="\n<tr>";
	foreach($row as $k=>$v) {
		$bgColor='#eeeeee';
		if($header==false) {
			$header=true;
			$body.=doHeader($row);
		}
		switch($k) {
		case "franch":
		case "primchannel":
		case "interface":
		case "subnum":
			$url="/monitoring/bester.php?search={$k}&value={$v}";
			$body.="<td bgcolor=\"{$bgColor}\"><a href=\"{$url}\">{$v}</a></td>";
			break;
		case "address":
			$url="/monitoring/bester.php?search={$k}&value={$v}";
			$body.="<td bgcolor=\"{$bgColor}\"><a href=\"{$url}\">{$v}</a></td>";
			break;
		case "name":
		case "apartment":
			$body.="<td bgcolor=\"{$bgColor}\">{$v}</td>";
			break;
		case "node":
		case "property":
			$url="/monitoring/bester.php?search={$k}&value={$v}";
			$body.="<td bgcolor=\"{$bgColor}\"><a href=\"{$url}\">{$v}</a></td>";
			break;
		case "building":
			$url="/monitoring/bester.php?search={$k}&value={$v}";
			$body.="<td bgcolor=\"{$bgColor}\"><a href=\"{$url}\">{$v}</a></td>";
			break;
		case "fwdrx":
			if(!isset($minFwdRx))
				$minFwdRx=$v;
			if(!isset($maxFwdRx))
				$maxFwdRx=$v;
			if($v < $minFwdRx)
				$minFwdRx=$v;
			if($v > $maxFwdRx)
				$maxFwdRx=$v;
			$bgColor=fwdRxColor($v);
			$tot['fwdrx']+=$v;
			$body.="<td align=\"right\" bgcolor=\"{$bgColor}\">{$v}</td>";
			break;
		case "fwdsnr":
			if($v > 0) {
				if(!isset($minFwdSnr))
					$minFwdSnr=$v;
				if(!isset($maxFwdSnr))
					$maxFwdSnr=$v;
				if($v < $minFwdSnr)
					$minFwdSnr=$v;
				if($v > $maxFwdSnr)
					$maxFwdSnr=$v;
			}
			$bgColor=fwdSnrColor($v);
			$tot['fwdsnr']+=$v;
			$body.="<td align=\"right\" bgcolor=\"{$bgColor}\">{$v}</td>";
			break;
		case "revtx":
			if(!isset($minRevTx))
				$minRevTx=$v;
			if(!isset($maxRevTx))
				$maxRevTx=$v;
			if($v < $minRevTx)
				$minRevTx=$v;
			if($v > $maxRevTx)
				$maxRevTx=$v;
			$bgColor=revTxColor($v);
			$tot['revtx']+=$v;
			$body.="<td align=\"right\" bgcolor=\"{$bgColor}\">{$v}</td>";
			break;
		case "revrx":
			if(!isset($minRevRx))
				$minRevRx=$v;
			if(!isset($maxRevRx))
				$maxRevRx=$v;
			if($v < $minRevRx AND $v !=0)
				$minRevRx=$v;
			if($v > $maxRevRx)
				$maxRevRx=$v;
			if($v == 0) 
				$revRxZeros++;
			$bgColor=revRxColor($v,$row['property']);
			$property=$row['property'];
			$tot['revrx']+=$v;
			$body.="<td align=\"right\" bgcolor=\"{$bgColor}\">{$v}</td>";
			break;
		case "revsnr":
			if(!isset($minRevSnr))
				$minRevSnr=$v;
			if(!isset($maxRevSnr))
				$maxRevSnr=$v;
			if($v < $minRevSnr)
				$minRevSnr=$v;
			if($v > $maxRevSnr)
				$maxRevSnr=$v;
			$bgColor=revSnrColor($v);
			$tot['revsnr']+=$v;
			$body.="<td align=\"right\" bgcolor=\"{$bgColor}\">{$v}</td>";
			break;
		case "mac":
			$url="/monitoring/modemHistory.php?mac={$v}";
			$body.="<td bgcolor=\"{$bgColor}\"><a href=\"{$url}\">{$v}</a></td>";
			break;
		case "lastcontact":
			$now=time();
			$then=dbTimestampConvert($v);
			$span=$now-$then;
			if($span > 3600) {
				$bgColor="cc6666";
			} elseif($span > 600) {
				$bgColor="yellow";
			}
		default:
			$body.="<td bgcolor=\"{$bgColor}\">{$v}</td>";
			break;
		}
	}
	$body.="</tr>\n";
}

$revRxColor='white';

$revRxCount=$count-$revRxZeros;
if(isset($_GET['search'])) 
	$revRxColor=revRxColor($tot['revrx']/$revRxCount,$property);

$body.="\n<tr><td align=\"right\" colspan=\"{$avgIndent}\">Averages:({$count} modems)&nbsp;&nbsp;&nbsp;&nbsp;</td>";
$body.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",fwdRxColor($tot['fwdrx']/$count),$tot['fwdrx']/$count);
$body.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",fwdSnrColor($tot['fwdsnr']/$count),$tot['fwdsnr']/$count);
$body.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",revTxColor($tot['revtx']/$count),$tot['revtx']/$count);
$body.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",$revRxColor,$tot['revrx']/$revRxCount);
$body.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",revSnrColor($tot['revsnr']/$count),$tot['revsnr']/$count);
$body.="<td colspan=\"3\">&nbsp;</td></tr>\n";

if(isset($_GET['search']))
	$revRxColor=revRxColor($minRevRx,$property);
$body.="\n<tr><td align=\"right\" colspan=\"{$avgIndent}\">Minimums: &nbsp;&nbsp;&nbsp;&nbsp;</td>";
$body.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",fwdRxColor($minFwdRx),$minFwdRx);
$body.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",fwdSnrColor($minFwdSnr),$minFwdSnr);
$body.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",revTxColor($minRevTx),$minRevTx);
$body.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",$revRxColor,$minRevRx);
$body.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",revSnrColor($minRevSnr),$minRevSnr);
$body.="<td colspan=\"3\">&nbsp;</td></tr>\n";

if(isset($_GET['search']))
	$revRxColor=revRxColor($maxRevRx,$property);
$body.="\n<tr><td align=\"right\" colspan=\"{$avgIndent}\">Maximums: &nbsp;&nbsp;&nbsp;&nbsp;</td>";
$body.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",fwdRxColor($maxFwdRx),$maxFwdRx);
$body.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",fwdSnrColor($maxFwdSnr),$maxFwdSnr);
$body.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",revTxColor($maxRevTx),$maxRevTx);
$body.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",$revRxColor,$maxRevRx);
$body.=sprintf("<td align=\"right\" bgcolor=\"%s\">%.1f</td>",revSnrColor($maxRevSnr),$maxRevSnr);
$body.="<td colspan=\"3\">&nbsp;</td></tr>\n";


$body.="</table>\n";
$body.="\n\n<!--\n{$sql}\n-->\n\n";
$body.="</body></html>\n";
print $body;

function doHeader($row) {
	global $_GET;
	if(isset($_GET['search'])) {
		$ss = $_GET['search'];
	} else {
		$ss = '';
	}
	if(isset($_GET['value'])) {
		$vv = $_GET['value'];
	} else {
		$vv = '';
	}
	$rv='';
	foreach($row as $k=>$v) {
		switch($k) {
		case "primchannel":
		case "interface":
		case "lastcontact":
			if(isset($_GET['offline'])) {
				$url="bester.php?offline=true&order={$k}";
			} else {
				$url="bester.php?search={$ss}&value={$vv}&order={$k}";
			}
			$rv.="<td align=\"left\"><a href=\"{$url}\">{$k}</a></td>";
			break;
		case "fwdrx":
		case "fwdsnr":
		case "revtx":
		case "revrx":
		case "revsnr":
			if(isset($_GET['offline'])) {
				$url="bester.php?offline=true&order={$k}";
			} else {
				$url="bester.php?search={$ss}&value={$vv}&order={$k}";
			}
			$rv.="<td align=\"right\"><a href=\"{$url}\">{$k}</a></td>";
			break;
		default:
			$rv.="<td>{$k}</td>";
			break;
		}
	}
	$rv.="</tr>\n\t<tr>";
	return $rv;
}
?>
