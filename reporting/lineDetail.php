<?php

require_once("../config.php");

if(!isset($_GET['mac'])) {
	header("Location: index.php");
	exit();
}

$mac=$_GET['mac'];
$mac=preg_replace('/[^a-zA-Z0-9]/','',$mac);
if(strlen($_GET['mac']) != 12) {
	header("Location: index.php");
	exit();
}


$sql="SELECT * FROM cable_usage WHERE modem_macaddr='{$mac}' AND entry_time='{$_GET['entry_time']}'";
//print $sql; exit();
$db=connect();
$res=$db->query($sql);
$tbl='';
while(($row=$res->fetchRow())==true) {
	foreach($row as $k=>$v) {
		$tbl.="<tr>\n";
		$tbl.="\t<td>{$k}</td>\n";
		$tbl.="\t<td>{$v}</td>\n";
		$tbl.="</tr>\n";
	}
}
?>
<html><head><title>Entry detail</title></head>
<body>
<table cellpadding="5" cellspacing="0" border="1">
<?php echo $tbl; ?>
</table>
</body>
</html>
