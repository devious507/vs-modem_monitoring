<?php

require_once("../config.php");
if(!isset($_GET['pipe'])) {
	header("Location: report_downstreamsByConfig.php?pipe=1000");
	exit();
}

$db = connect();

$sql="select cfg_id,comment,cfg_txt from config_modem WHERE cfg_id >=100 AND cfg_id < 200 ORDER BY comment";
$rset=$db->query($sql);
if(PEAR::isError($rset)) {
	print $rset->getMessage()."<br>\n";
	print $sql."<br>\n";
}
while(($row=$rset->fetchRow())==true) {
	$id = $row['cfg_id'];
	$comment = $row['comment'];
	$temp = preg_split("/;/",$row['cfg_txt']);
	foreach($temp as $t) {
		if(preg_match("/MaxRateSustained/",$t)) {
			$rate=trim(preg_replace("/MaxRateSustained/","",$t));
			$rate/=1024;
			$rate/=1024;
			$rate=round($rate,1);
		}
	}
	//print "<pre>";var_dump($temp); exit();
	$sql="select count(*) from docsis_modem WHERE config_file='auto' and dynamic_config_file like '%,{$id},%'";
	$rset2=$db->query($sql);
	if(PEAR::isError($rset2)) {
		print $rset->getMessage()."<br>\n";
		print $sql."<br>\n";
	}
	$row2=$rset2->fetchRow();
	$answers[$id]['comment']=$comment;
	$answers[$id]['count']=$row2['count(*)'];
	$answers[$id]['rate']=$rate;
}
$sql="SELECT config_file,count(config_file) FROM docsis_modem WHERE config_file='1ata.bin' OR config_file='3ata.bin' GROUP BY config_file";
$rset=$db->query($sql);
if(PEAR::isError($rset)) {
	print $rset->getMessage()."<br>\n";
	print $sql."<br>\n";
	exit();
}
while(($row=$rset->fetchRow())==true) {
	$id = $row['config_file'];
	$comment = $row['config_file'];
	$count = $row['count(config_file)'];
	$answers[$id]['comment']=$comment;
	$answers[$id]['count']=$count;
	if($comment == '1ata.bin') {
		$answers[$id]['rate']=.2;
	} elseif($comment == '3ata.bin') {
		$answers[$id]['rate']=.5;
	}
}

print "<html><head><title>Level Counts (Downstream)</title></head><body>\n";
print "<table cellpadding=\"5\" cellspacing=\"0\" border=\"0\"><tr><td>";
print "<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
print "<tr><td>Descr</td><td align=\"right\">Count</td><td align=\"right\">Speed</td><td align=\"right\">Bandwidth</td></tr>\n";
$bwTotal=0;
foreach($answers as $ans) {
	$bw = $ans['count']*$ans['rate'];
	$bwTotal+=$bw;
	print "<tr>\n";
	$link = "<a href=\"report_FindModemsByConfig.php?string={$ans['comment']}\">{$ans['comment']}</a>";
	print "\t<td>{$link}</td>\n";
	print "\t<td align=\"right\">{$ans['count']}</td>\n";
	print "\t<td align=\"right\">{$ans['rate']}</td>\n";
	print "\t<td align=\"right\">{$bw}</td>\n";
	print "</tr>\n";
}
print "</table></td>\n";
print "<td valign=\"top\">";


$ratio = round($bwTotal/$_GET['pipe'],1);
$pipe=sprintf("%.1f",$_GET['pipe']);
print "<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">";
print "<tr><td>Bandwidth Available</td><td align=\"right\">{$pipe}</td></tr>";
print "<tr><td>Bandwidth Allocatted</td><td align=\"right\">{$bwTotal}</td></tr>";
print "<tr><td>Oversell Ratio</td><td align=\"right\">{$ratio}</td></tr>";
print "</table>";



print "</td></tr>";
print "</table>";
print "</body></html>\n";



function myDumper($var) {
	print "<pre>";
	var_dump($var);
	print "</pre>";
}

?>
