<?php

require_once("defines.php");
require_once("../../functions.php");
require_once("../../defines.php");

if(isset($_GET['mac'])) {
	$mac=strtolower($_GET['mac']);
	$m[]=substr($mac,0,4);
	$m[]=substr($mac,4,4);
	$m[]=substr($mac,8,4);
	$mac=implode(".",$m);
	$log=$mac;
	$logfile="history/{$mac}";
	$conn=connect();
	$sql=" select c.name,c.property,c.building FROM docsis_modem AS d LEFT OUTER JOIN customer_address AS c on d.subnum=c.subnum WHERE d.modem_macaddr='{$_GET['mac']}'";
	$res=$conn->query($sql);
	$row=$res->fetchRow();
	$log="<b>".$row['property']." / ".$row['building']." / ".$row['name']."</b><br>";
	if(file_exists($logfile)) {
		$log.="<pre>";
		$log.="MAC            Upstream   Ins    Hit   Miss  CRC  P-Adj  Flap  Timestamp\n";
		$log.="------------------------------------------------------------------------------\n";
		$log.=file_get_contents($logfile);
		$log.="</pre>";
	} else {
		$log.="No Logs Found for {$mac}";
	}
}
$emptyTable="delete from myflaps";

$raw=file_get_contents(OUTFILE);
$myLines=array();
$lines=preg_split("/\n/",$raw);
foreach($lines as $line) {
	if(!preg_match("/No data available/",$line)) {
		$myLines[]=parseline($line);
	}
}
unset($raw);
unset($lines); // Free Up some memory, I don't need raw or lines anymore
$conn=connect();
$conn->query($emptyTable);
//print $emptyTable.";<br>\n";
foreach($myLines as $l) {
	$Tsql="INSERT INTO myflaps VALUES ('%s','%s',%d,%d,%d,%d,'%s',%d);";
	$l['mac']=strtoupper(preg_replace("/\./",'',$l['mac']));
	$sql=sprintf($Tsql,$l['mac'],$l['upstream'],$l['ins'],$l['hit'],$l['miss'],$l['csr'],$l['padj'],$l['flap'],$l['tstamp']);
	if($l['mac'] != '') {
		$conn->query($sql);
	}
}
$lbls=array('MAC',
	'Upstream',
	'<a href="index.php?sort=ins">INS</a>',
	'<a href="index.php?sort=hit">Hit</a>',
	'<a href="index.php?sort=miss">MISS</a>',
	'<a href="index.php?sort=crc">CRC</a>',
	'<a href="index.php?sort=padj">P-Adj</a>',
	'<a href="index.php">Flap</a>',
	'Subnum',
	'Config',
	'<a href="index.php?sort=name">Name</a>',
	'<a href="index.php?sort=building">Bldg</a>',
	'<a href="index.php?sort=property">Prop</a>.',
	'<a href="index.php?sort=node">Node</a>',
	'<a href="index.php?sort=fwdrx">FwdRX</a>',
	'<a href="index.php?sort=fwdsnr">FwdSNR</a>',
	'<a href="index.php?sort=revtx">RevTX</a>',
	'<a href="index.php?sort=revrx">RevRX</a>',
	'<a href="index.php?sort=revsnr">RevSNR</a>');
$sql="SELECT aaa.*,m.fwdrx,m.fwdsnr,m.revtx,m.revrx,m.revsnr FROM (SELECT aa.* FROM (SELECT a.*,c.name,c.building,c.property,c.node FROM (select f.*,d.subnum,d.config_file FROM myflaps AS f LEFT OUTER JOIN docsis_modem as d ON f.mac=d.modem_macaddr) as a LEFT OUTER JOIN customer_address AS c ON a.subnum=c.subnum) as aa) as aaa LEFT OUTER JOIN modem_history as m ON aaa.mac=m.mac ";
$sql.="WHERE flap >= ".MINFLAPS." ";
if(!isset($_GET['sort'])) {
	$sql.="ORDER BY aaa.flap DESC, aaa.subnum ASC";
} else {
	switch($_GET['sort']) {
	case "property":
	case "building":
	case "node":
		$sql.="ORDER BY property,building,name";
		break;
	case "ins":
		$sql.="ORDER BY aaa.ins DESC,aaa.flap ASC,aaa.subnum ASC";
		break;
	case "padj":
		$sql.="ORDER BY aaa.padj DESC, aaa.flap DESC, aaa.subnum ASC";
		break;
	case "miss":
		$sql.="ORDER BY aaa.miss DESC, aaa.flap DESC, aaa.subnum ASC";
		break;
	case "crc":
		$sql.="ORDER BY aaa.crc DESC, aaa.flap DESC, aaa.subnum ASC";
		break;
	case "hit":
		$sql.="ORDER BY aaa.hit DESC, aaa.flap DESC, aaa.subnum ASC";
		break;
	case "fwdrx":
		$sql.="ORDER BY fwdrx ASC, flap DESC, subnum ASC";
		break;
	case "fwdsnr":
		$sql.="ORDER BY fwdsnr ASC, flap DESC, subnum ASC";
		break;
	case "revtx":
		$sql.="ORDER BY revtx ASC, flap DESC, subnum ASC";
		break;
	case "revrx":
		$sql.="ORDER BY revrx ASC, flap DESC, subnum ASC";
		break;
	case "revsnr":
		$sql.="ORDER BY revsnr ASC, flap DESC, subnum ASC";
		break;
	case "name":
		$sql.="ORDER BY name ASC, flap DESC, padj ASC";
		break;
	case "flap":
	default:
		$sql.="ORDER BY aaa.flap DESC, aaa.subnum ASC";
		break;
	}
}
//print $sql; exit();
$sqlCount="SELECT count(*) as c FROM myflaps";
$res=$conn->query($sqlCount);
$row=$res->fetchRow();
$linePos=round($row['c']/10,0)+2;
$res=$conn->query($sql);
print "<!DOCTYPE html>\n";
print "<html>\n";
print "<head>\n";
print "<script src=\"functions.js\" type=\"text/Javascript\"></script>\n";
print "<link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\">\n";
print "<meta charset=\"UTF-8\">\n";
print "<title>Flap Research</title>\n";
print "</head>\n";
print "<body ondragover=\"drag_over(event)\" ondrop=\"drop(event)\">\n";
print "<div class=\"logfile\" name=\"logfile\" id=\"logfile\" draggable=\"true\" ondragstart=\"drag_start(event)\">Subscriber Flap History:<iframe id=\"logframe\" class=\"logframe\"></iframe><a href=\"javascript:closeDiv()\">Close</a></div>\n";
print "<table border=\"1\" cellpadding=\"4\" cellspacing=\"0\">\n";
$mtime="Flaps as of: ".date('H:i:s m/d/y',filemtime(OUTFILE));
print "<tr><td align=\"center\" colspan=\"8\">{$mtime}</td><td colspan=\"6\" align=\"center\">Subscriber Info</td><td colspan=\"5\" align=\"center\">Current Levels</a></td></tr>\n";
print "<tr><td align=\"right\">";
print implode("</td><td align=\"right\">",$lbls);
print "</td></tr>\n";
$count=0;
while(($row=$res->fetchRow()) == true) {
	print "<tr>\n";
	foreach($row as $k=>$v) {
		switch($k) {
		case "mac":
			$url="<a href=\"javascript:setDiv('{$v}','{$row['name']}')\">{$v}</a>";
			$v=$url;
			$bg='white';
			break;
		case "building":
			$url="<a target=\"flap_worker\" href=\"/monitoring/bester.php?search=building&value={$v}\">{$v}</a>";
			$bg='white';
			$v=$url;
			break;
		case "name":
			if(preg_match("/Node.*Reference/",$v) OR $v=='' OR preg_match("/Phone Dummy/",$v)) {
				$bg="#cacaca";
			} else{
				$bg='white';
				$count++;
			}
			break;
		case "node":
			$url="<a target=\"flap_worker\" href=\"/monitoring/bester.php?search=node&value={$v}\">{$v}</a>";
			$v=$url;
			$bg='white';
			break;
		case "subnum":
			$url="<a target=\"flap_worker\" href=\"/monitoring/modemHistory.php?mac={$row['mac']}\">{$v}</a>";
			$v=$url;
			$bg='white';
			break;
		case "fwdrx":
			$bg=fwdRxColor($v);
			break;
		case "fwdsnr":
			$bg=fwdSnrColor($v);
			break;
		case "revtx":
			$bg=revTxColor($v);
			break;
		case "revrx":
			$bg=revRxColor($v,$row['property'],$row['node']);
			break;
		case "revsnr":
			$bg=revSnrColor($v);
			break;
		default:
			$bg='white';
			break;
		}
		if($row['config_file'] != 'disable.bin') {
			print "\t<td align=\"right\" bgcolor=\"{$bg}\">{$v}</td>\n";
		}
	}
	print "</tr>\n";
	if($linePos == $count && !isset($_GET['sort'])) {
		print "<tr>\n\t<td colspan=\"19\" bgcolor=\"black\">&nbsp;</td>\n</tr>\n";
	}
}
print "</table>\n";
print "</body></html>";
