<?php

require_once("MDB2.php");

$script="/var/www/BACKUPS/dns_secure/iptables.sh";

$dsn="pgsql://nomadix@visionsystems.tv/nomadix";
$sql="SELECT last_ip FROM nomadix_master WHERE property_state not like 'ZZ%' ORDER BY last_ip";

$db=MDB2::connect($dsn);
if(PEAR::isError($db)) {
	print $db->getMessage();
	exit();
}

$res=$db->query($sql);
if(PEAR::isError($res)) {
	print $res->getMessage();
	exit();
}

while(($row=$res->fetchRow())==true) {
	$ips[]=$row[0];
}
$db->disconnect();


$fp=fopen($script,'w');
fwrite($fp,"#!/bin/sh\n\n");
fwrite($fp,"/sbin/iptables -F\n");
fwrite($fp,"/sbin/iptables -N LOGGING\n\n");
fwrite($fp,"/sbin/iptables -A INPUT -i eth0 -p tcp -s 38.108.136.0/21 --dport 53 -j ACCEPT\n");
fwrite($fp,"/sbin/iptables -A INPUT -i eth0 -p udp -s 38.108.136.0/21 --dport 53 -j ACCEPT\n\n");
foreach($ips as $i) {
	fwrite($fp,"/sbin/iptables -A INPUT -i eth1 -p tcp -s {$i} --dport 53 -j ACCEPT\n");
	fwrite($fp,"/sbin/iptables -A INPUT -i eth1 -p udp -s {$i} --dport 53 -j ACCEPT\n");
}


fwrite($fp,"\n\n# No other DNS Resolver Traffic Permitted\n");
fwrite($fp,"/sbin/iptables -A INPUT -i eth1 -p tcp --dport 53 -j LOGGING\n");
fwrite($fp,"/sbin/iptables -A INPUT -i eth1 -p udp --dport 53 -j LOGGING\n");
fwrite($fp,"/sbin/iptables -A LOGGING -m limit --limit 2/min -j LOG --log-prefix \"IPTABLES PACKET DROPPED: \" --log-level 7\n");
fwrite($fp,"/sbin/iptables -A LOGGING -j DROP\n\n");

fclose($fp);
?>
