<?php

if(!isset($_GET['string'])) {
	header("Location: report_downstreamsByConfig.php");
	exit();
} else {
	$myConfig = $_GET['string'];
}

require_once("../config.php");
$db = connect();
if($_GET['string'] == '1ata.bin') {
	$sql="select modem_macaddr FROM docsis_modem WHERE config_file='1ata.bin'";
} elseif($_GET['string'] == '3ata.bin') {
	$sql="select modem_macaddr FROM docsis_modem WHERE config_file='3ata.bin'";
} else {
	$sql = "select cfg_id from config_modem WHERE comment='{$myConfig}'";
	$res = $db->query($sql);
	if(PEAR::isError($res)) {
		print $res->getMessage();
		exit();
	}
	$row = $res->fetchRow();
	$cfg_id = $row['cfg_id'];
	$sql="select modem_macaddr FROM docsis_modem WHERE dynamic_config_file like '%,{$cfg_id},%' AND config_file='auto'";
}
$res = $db->query($sql);
while(($row=$res->fetchRow())==true) {
	$macs[]=$row['modem_macaddr'];
}
$paste = implode("\n",$macs);
?>
<html><head><title>Export View</title></head>
<body><p>Pasted Values</p>
<form method="post" action="listByAddress.php"><input type="hidden" name="sql" value="Pasted Values"><textarea name="pastebin" rows="20" cols="100"><?php echo $paste; ?></textarea><br><input type="submit" value="Submit"></form></body></html>
