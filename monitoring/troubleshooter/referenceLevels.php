<?php

require_once("../../config.php");
define('URL','/monitoring/drawGraph.php?');

$qs=array(
	'days'=>5,
	'type'=>'fwdrx',
	'type2'=>'fwdsnr',
	'type3'=>'revsnr',
	'end'=>time(),
	'start'=>time()-(60*60*24*5),
	'mac'=>'MAC');


if(isset($_GET['days'])) {
	$qs['days']=$_GET['days'];
	$qs['start']=$qs['end']-(60*60*24*$_GET['days']);
}

if(isset($_GET['type'])) 
	$type=$_GET['type'];

if(isset($_GET['type2']))
	$type2=$_GET['type2'];

if(isset($_GET['type3']))
	$type3=$_GET['type3'];

foreach($qs as $k=>$v) {
	$pieces[]=$k."=".$v;
}


$db=connect();
$sql = 'select c.subnum,c.name,c.address,d.modem_macaddr from customer_address AS c LEFT OUTER JOIN docsis_modem AS d ON c.subnum=d.subnum WHERE c.franch=999 AND d.modem_macaddr IS NOT NULL ORDER BY address,modem_macaddr';
$res=$db->query($sql);
while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC))==true) {
	$cell[]=makeCell($qs,$row['modem_macaddr'],$row['name'],$row['address']);
}
print "<html><head><title>Reference Levels</title></head><body>";
print "<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">";
$count=1;
foreach($cell as $line) {
	if($count%2 == 1) {
		print "<tr><td>{$line}</td>";
		$count++;
	} else {
		print "<td>{$line}</td></tr>";
		$count++;
	}
}
if($count%2 == 0) {
	print "<td>&nbsp;</td></tr>";
}
print "</table></body></html>";


function makeCell($qs,$mac,$name,$address) {
	$qs['mac']=$mac;
	foreach($qs as $k=>$v) {
		$pieces[]=$k."=".$v;
	}
	$string = implode("&",$pieces);
	$url=URL.$string;
	$link="<a href=\"{$url}\" border=\"0\"><img src=\"{$url}\" width=\"585\" height=\"300\"></a>";
	$cell="{$link}<br>{$name} -- {$address}";
	return $cell;
}
?>
