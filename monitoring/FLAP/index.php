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
$lbls=array('MAC','Upstream','INS','Hit','MISS','CRC','P-Adj','Flap','Subnum','Config','Name','Bldg','Prop.','Node','FwdRX','FwdSNR','RevTX','RevRX','RevSNR');
$sql="SELECT aaa.*,m.fwdrx,m.fwdsnr,m.revtx,m.revrx,m.revsnr FROM (SELECT aa.* FROM (SELECT a.*,c.name,c.building,c.property,c.node FROM (select f.*,d.subnum,d.config_file FROM myflaps AS f LEFT OUTER JOIN docsis_modem as d ON f.mac=d.modem_macaddr) as a LEFT OUTER JOIN customer_address AS c ON a.subnum=c.subnum) as aa) as aaa LEFT OUTER JOIN modem_history as m ON aaa.mac=m.mac ORDER BY aaa.flap DESC";
$res=$conn->query($sql);
print "<!DOCTYPE html><html><head><link rel=\"stylesheet\" type=\"text/css\" href=\"style.css\"><meta charset=\"UTF-8\"><title>Flap Research</title></head><body>";
if(isset($log)) {
	print "<div class=\"logfile\" name=\"logfile\">{$log}<hr><a href=\"index.php\">Close</a></div>\n";
}
print "<table border=\"1\" cellpadding=\"4\" cellspacing=\"0\">\n";
print "<tr><td align=\"right\">";
print implode("</td><td align=\"right\">",$lbls);
print "</td></tr>\n";
while(($row=$res->fetchRow()) == true) {
	print "<tr>\n";
	foreach($row as $k=>$v) {
		switch($k) {
		case "mac":
			$url="<a href=\"index.php?mac={$v}&name={$row['name']}\">{$v}</a>";
			$v=$url;
			$bg='white';
			break;
		case "name":
			if(preg_match("/Node.*Reference/",$v) OR $v=='') {
				$bg="#cacaca";
			} else{
				$bg='white';
			}
			break;
		case "subnum":
			$url="<a href=\"/monitoring/bester.php?search=subnum&value={$v}\">{$v}</a>";
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
}
print "</table>\n";
print "</body></html>";
