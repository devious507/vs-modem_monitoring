<?php

require_once("defines.php");
require_once("../../functions.php");
require_once("../../defines.php");


$log="";
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
print "<!DOCTYPE html>\n";
print "<html>\n";
print "<head>\n";
print "<meta charset=\"UTF-8\">\n";
print "<title>Flap Research</title>\n";
print "</head>\n";
print "<body>\n";

print $log;
print "<a href=\"/monitoring/modemHistory.php?mac={$_GET['mac']}\" target=\"flap_worker\">Modem History</a> | <a target=\"flap_worker\" href=\"/edit_modem.php?modem_macaddr={$_GET['mac']}\">Modem Configuration</a>";
print "</body>\n";
print "</html>\n";
