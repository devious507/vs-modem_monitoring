<?php

if(isset($_GET['wincable']) && isset($_GET['mac']) && isset($_GET['ip_addr'])) {
	require_once("config.php");
	$conn = connect();
	$wincable=$_GET['wincable'];
	$mac = $_GET['mac'];
	$ip_addr = $_GET['ip_addr'];
	$sql[]="DELETE FROM local_statics WHERE wincable='{$wincable}'";
	$sql[]="DELETE FROM local_statics WHERE mac_addr='{$mac}'";
	$sql[]="INSERT INTO local_statics VALUES (default,{$wincable},'{$mac}','{$ip_addr}')";
	foreach($sql as $s) {
		$res=$conn->query($s);
		print $s;
		if(PEAR::isError($res)) {
			print "\n<br>\n".$res->getMessage()."<br>\n";
		} else {
			print ": OK<br>\n";
		}
	}
	exit();
}
if(isset($_GET['input'])) {
	$input=trim($_GET['input']);
	preg_match("/....\.....\...../",$input,$matches);
	$mac=$matches[0];
	$mac=preg_replace("/\./","",$mac);
	$pattern="/\b\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\b/";
	preg_match($pattern,$input,$matches);
	$ip_addr = $matches[0];
	$pattern="/\b\d{5}\b/";
	preg_match($pattern,$input,$matches);
	$wincable = $matches[0];


	//print "// ".$input."\n";
	//print "\$statics['{$mac}']='{$ip_addr}';\n";
	print "<html><head><title>Static Maker</title></head>\n";
	print "<pre>\n";
	print "\n\nmysql dhcp_server\n";
	print "\n\nDELETE FROM local_statics WHERE wincable={$wincable};\n";
	print "\n\nDELETE FROM local_statics WHERE mac_addr='{$mac}';\n";
	print "\n\nINSERT INTO local_statics VALUES (default,{$wincable},'{$mac}','{$ip_addr}');\n";
	print "SELECT l.entry_date,l.wincable,l.mac_addr,l.ip_addr,c.name FROM local_statics AS l LEFT JOIN customer_address AS c ON l.wincable=c.subnum WHERE c.name IS NULL OR c.name='';\n\n\n";
	print "</pre>\n";
	print "<a href=\"static.php?wincable={$wincable}&mac={$mac}&ip_addr={$ip_addr}\">Update Database</a>\n";
	print "</body></html>";
	exit();

} else {
	print "<html>
		<head><title>static maker!</title></head>
		<body>
		<form method=\"get\" action=\"static.php\">
		<input type=\"text\" name=\"input\" size=\"100\"><br>
		<input type=\"submit\">
		</form>
		</body>
		<html>";
	exit();
}
