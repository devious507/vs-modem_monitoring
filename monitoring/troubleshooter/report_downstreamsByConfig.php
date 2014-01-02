<?php

require_once("../config.php");

$db = connect();

$sql="select cfg_id,comment from config_modem WHERE cfg_id >=100 AND cfg_id < 200 ORDER BY comment";
$rset=$db->query($sql);
if(PEAR::isError($rset)) {
	print $rset->getMessage()."<br>\n";
	print $sql."<br>\n";
}
while(($row=$rset->fetchRow())==true) {
	$id = $row['cfg_id'];
	$comment = $row['comment'];
	$sql="select count(*) from docsis_modem WHERE config_file='auto' and dynamic_config_file like '%,{$id},%'";
	$rset2=$db->query($sql);
	if(PEAR::isError($rset2)) {
		print $rset->getMessage()."<br>\n";
		print $sql."<br>\n";
	}
	$row2=$rset2->fetchRow();
	$answers[$id]['comment']=$comment;
	$answers[$id]['count']=$row2['count(*)'];
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
}

print "<html><head><title>Level Counts (Downstream)</title></head><body>\n";
print "<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
foreach($answers as $ans) {
	print "<tr>\n";
	print "\t<td>{$ans['comment']}</td>\n";
	print "\t<td align=\"right\">{$ans['count']}</td>\n";
	print "</tr>\n";
}
print "</table>\n";
print "</body></html>\n";



function myDumper($var) {
	print "<pre>";
	var_dump($var);
	print "</pre>";
}

?>
