<?php

require_once("config.php");

if(!isset($_GET['mac'])) {
	header("Location: ../index.php");
	exit();
} else {
	$mac=$_GET['mac'];
	$rrdfile = BASE_DIR."/monitoring/rrd/{$mac}.rrd";
	if(strlen($_GET['mac']) == 12) {
		$colSpan=9;
	} else {
		$colSpan=8;
	}
}

if(strlen($mac) == 12) {
	$body="<form method=\"get\" action=\"monitoring/modemHistory.php\">\n";
} else {
	$body=NULL;
}
$body .="<table width=\"100%\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
if(strlen($mac) < 12) {
	$body.="<tr><td colspan=\"{$colSpan}\" align=\"center\">Search Item: %{$mac}%</td></tr>\n";
	$body.="<tr><td>MAC</td><td>FwdRX</td><td>Fwd SNR</td><td>RevTX</td><td>RevRX</td><td>RevSNR</td><td>Mdm IP</td><td>DS</td><td>Last Update</td></tr>\n";
} else {
	$body.="<tr><td colspan=\"{$colSpan}\" align=\"center\"><a class=\"blackUnderline\" href=\"modem.php?search=modem_macaddr&value={$mac}\">{$mac}</a></td></tr>\n";
	$body.="<tr><td>FwdRX</td><td>Fwd SNR</td><td>RevTX</td><td>RevRX</td><td>RevSNR</td><td>Mdm IP</td><td>DS</td><td>Last Update</td><td>First Contact</td></tr>\n";
}

if(strlen($mac) == 12) {
	$sql = "SELECT fwdrx,fwdsnr,revtx,revrx,revsnr,ip,primchannel,time,firstcontact FROM modem_history WHERE mac ='{$mac}'";
} else {
	$sql = "SELECT mac,fwdrx,fwdsnr,revtx,revrx,revsnr,ip,primchannel,time FROM modem_history WHERE mac like '%{$mac}%'";
}
$conn = connect();
$rset=$conn->query($sql);
$count=0;
$subMac=NULL;
while(($row=$rset->fetchRow())==true) {
	$count++;
	unset($vs);
	foreach($row as $k=>$v) {
		switch($k) {
		case "subnum":
			$subNum=$v;
			break;
		case "mac":
			$vs[]="<td><a href=\"/monitoring/modemHistory.php?mac={$v}\">{$v}</a></td>";
			$subMac=$v;
			break;
		case "fwdrx":
			$bg = fwdRxColor($v);
			$vs[]="<td bgcolor=\"{$bg}\">{$v}</td>";
			break;
		case "fwdsnr":
			$bg = fwdSnrColor($v);
			$vs[]="<td bgcolor=\"{$bg}\">{$v}</td>";
			break;
		case "revtx":
			$bg = revTxColor($v);
			$lastRevTX=$v;
			$vs[]="<td bgcolor=\"{$bg}\">{$v}</td>";
			break;
		case "revrx":
			$property_name=_getPropertyName($mac);
			$bg = revRxColor($v,$property_name);
			$vs[]="<td bgcolor=\"{$bg}\">{$v}</td>";
			break;
		case "revsnr":
			$bg = revSnrColor($v);
			$vs[]="<td bgcolor=\"{$bg}\">{$v}</td>";
			break;
		case "time":
			$arA=preg_split("/ /",$v);
			$arB=preg_split("/-/",$arA[0]);
			$arC=preg_split("/:/",$arA[1]);
			unset($arA);
			$dbTime = mktime($arC[0],$arC[1],$arC[2],$arB[1],$arB[2],$arB[0]);
			$time   = time();
			$vv=$time-$dbTime;
			if($vv > 900) {
				$bg = 'red';
			} elseif($vv > 600) {
				$bg = 'yellow';
			} else {
				$bg = 'lightgray';
			}
			$vs[]="<td bgcolor=\"{$bg}\">{$v}</td>";
			break;
		case "ip":
			$vs[]="<td><a href=\"http://{$v}\">{$v}</a></td>";
			break;
		default:
			$vs[]="<td>{$v}</td>";
			break;
		}
	}
		$body.="<tr>".implode(" ",$vs)."</tr>\n";
}
if(strlen($mac) == 12) {
	$addressSql="SELECT d.subnum,c.name,c.apartment,c.address,c.city,c.state,c.zip,c.building,c.node,c.property FROM docsis_modem AS d JOIN customer_address AS c ON d.subnum=c.subnum WHERE d.modem_macaddr='{$mac}'";
	$rset=$conn->query($addressSql);
	$row=$rset->fetchRow();
	$url_property="<a href=\"monitoring/bester.php?search=property&value={$row['property']}\">{$row['property']}</a>";
	$body.="<tr><td colspan=\"5\"><hr></td><td colspan=\"4\"><b>{$url_property}</b></td></tr>\n";
		$body.="<tr>";
		foreach($row as $k=>$v) {
			switch($k){
			case "building":
				$url="<a href=\"monitoring/bester.php?search={$k}&value={$v}\">{$v}</a>";
				$body.="<td colspan=\"2\">Bld# {$url}</td>";
				break;
			case "node":
				$url="<a href=\"monitoring/bester.php?search={$k}&value={$v}\">{$v}</a>";
				$body.="<td colspan=\"2\">Node# {$url}</td>";
				break;
			case "subnum":
				$url="<a href=\"monitoring/bester.php?search={$k}&value={$v}\">{$v}</a>";
				$body.="<td colspan=\"2\">{$url}</td>";
				break;
			case "city":
			case "zip":
			case "apartment":
			case "name":
				$body.="<td colspan=\"2\">{$v}</td>";
				break;
			case "address":
				$body.="<td colspan=\"3\">{$v}</td></tr>\n<tr>";
				break;
			case "property":
				break;
			default:
				$body.="<td>{$v}</td>";
				break;
			}
		}
		$body.="</tr>\n";
	$bester = "<a href=\"http://bester.visionsystems.tv:8080/modem/upmodem.php?mac={$mac}\">Bester</a>";
	$bester2 = "<a href=\"/monitoring/cmtsTool.php?mac={$mac}\">Bester2</a>";
	$pinger = "<a href=\"http://dashboard.visionsystems.tv/pinger/v2/index.php?mac={$mac}&cmts=dm2&auto=true&repeat=70\">Ping</a>";
	$configure = "<a href=\"http://38.108.136.6/modem.php?search=modem_macaddr&value={$mac}\">Config</a>";
	$newBester = "<a href=\"/monitoring/bester.php?search=mac&value={$mac}\">List View</a>";
	$upModem = "<a href=\"monitoring/upModem.php?mac={$mac}\">Up Mdm Tool</a>";
	$dMac = getDottedMac($mac);
	$flapList = "<a href=\"/monitoring/flapcheck/index.php?mac={$dMac}\">Flaps</a>";
	$usageLink = "<a href=\"/reporting/modemDump.php?mac={$mac}\">Up/Down Hist.</a>";
	$dispatchLink= "<a href=\"http://dashboard.visionsystems.tv/dispatcher/assistant.php?mac={$mac}\">Dispatch&nbsp;Assistant</a>";
	$body.="<tr><td colspan=\"{$colSpan}\"><input type=\"hidden\" name=\"mac\" value=\"{$mac}\">[ {$bester} | {$bester2} | {$pinger} | {$configure} | {$newBester} | {$upModem} | {$flapList} | {$usageLink} | {$dispatchLink} ]</td></tr>\n";
	$month=$day=$year=NULL;
	if(isset($_GET['start'])) {
		$a = date_parse($_GET['start']);
		$month = sprintf("%02d",$a['month']);
		$day   = sprintf("%02d",$a['day']);
		$year  = sprintf("%02d",$a['year']);
	} else {
		$then = time()-(24*60*60*10);
		$month = date('m',$then);
		$day   = date('d',$then);
		$year  = date('Y',$then);
	}
	$start = "<input type=\"date\" value=\"{$year}-{$month}-{$day}\" name=\"start\">";
	// End thing
	$month=$day=$year=NULL;
	if(isset($_GET['end'])) {
		$a = date_parse($_GET['end']);
		$month = sprintf("%02d",$a['month']);
		$day   = sprintf("%02d",$a['day']);
		$year  = sprintf("%02d",$a['year']);
	} else {
		$month = date('m');
		$day   = date('d');
		$year  = date('Y');
	}
	$end = "<input type=\"date\" value=\"{$year}-{$month}-{$day}\" name=\"end\">";
	if(isset($_GET['graphtype'])) {
		$type=typeSelector($_GET['graphtype']);
	} else {
		$type=typeSelector();
	}
	$body.="<tr><td>Start</td><td colspan=\"5\">{$start} 00:00:00</td><td>End</td><td colspan=\"2\">{$end} 23:59:59</td></tr>\n";
	if(isset($_GET['graphtype'])) {
		$typeSel = typeSelector($_GET['graphtype']);
		$tSel2 = typeSelector($_GET['type2'], 'type2');
		$tSel3 = typeSelector($_GET['type3'], 'type3');
	} else {
		$typeSel = typeSelector();
		$tSel2 = typeSelector('fwdsnr', 'type2');
		$tSel3 = typeSelector('revsnr', 'type3');
	}
	$body.="<tr><td>Graph Type</td><td colspan=\"3\">1 {$typeSel}<br>2 {$tSel2}<br>3 {$tSel3}</td><td colspan=\"5\">&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Graph It\"></td></tr>\n";
	if(isset($_GET['graphtype']))
		$body.= myGraph($_GET,$_GET['graphtype']);
}
$body.="</table>\n";
if(strlen($mac) == 12) {
	$body.="</form>\n";
}

buildPage($body,$sql);

function myGraph($g,$t) {
	$type = $t; 
	$mac=$g['mac'];
	$a = date_parse($_GET['start']);
	$g['startmonth']=$a['month'];
	$g['startday']  =$a['day'];
	$g['startyear'] =$a['year'];
	$a = date_parse($_GET['end']);
	$g['endmonth']  =$a['month'];
	$g['endday']    =$a['day'];
	$g['endyear']   =$a['year'];
		
	$start=mktime(0,0,0,$g['startmonth'],$g['startday'],$g['startyear']);
	$end  =mktime(23,59,59,$g['endmonth'],$g['endday'],$g['endyear']);
	$pieces[]="type={$type}";
	$pieces[]="start={$start}";
	$pieces[]="end={$end}";
	$pieces[]="mac={$mac}";
	if($g['type2'] != " ") 
		$pieces[]="type2={$g['type2']}";
	if($g['type3'] != " ") 
		$pieces[]="type3={$g['type3']}";


	$qString=implode("&",$pieces);
	global $colSpan;
	$rv="<tr><td colspan=\"{$colSpan}\" align=\"center\"><img src=\"monitoring/drawGraph.php?{$qString}\"></td></tr>\n";
	return $rv;
}

function typeSelector($s=NULL,$secondary=false) {
	$type=array('fwdrx','fwdsnr','revtx','revrx','revsnr');
	if($secondary != false) {
		array_unshift($type," ");
		$rv ="<select name=\"{$secondary}\">";
	} else {
		$rv ="<select name=\"graphtype\">";
	}
	foreach($type as $t) {
		if($t == $s) {
			$sel = "selected=\"selected\"";
		} else {
			$sel=NULL;
		}
		$rv.="<option $sel value=\"{$t}\">{$t}</option>";
	}
	$rv.="</select>";
	return $rv;
}

function getDottedMac($mac) {
	$mac=preg_replace("/\./","",$mac);
	$mm[]=substr($mac,0,4);
	$mm[]=substr($mac,4,4);
	$mm[]=substr($mac,8,4);
	return implode(".",$mm);
}

function _getPropertyName($mac) {
	$db = connect();
	$sql="SELECT c.property FROM docsis_modem AS d LEFT OUTER JOIN customer_address AS c on d.subnum=c.subnum WHERE modem_macaddr='{$mac}'";
	$res=$db->query($sql);
	if(PEAR::isError($res)) {
		print $res->getMessage();
		exit();
	} else {
		$row=$res->fetchRow();
		return $row['property'];
	}
	return;
}
?>
