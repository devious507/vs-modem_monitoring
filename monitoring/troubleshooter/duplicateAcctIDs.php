<?php

require_once("../config.php");

$Asql="select subnum,count(subnum) FROM docsis_modem GROUP BY subnum ORDER BY subnum";

$db = connect();

$rset=$db->query($Asql);

$title="Account #'s with more than 1 modem";
$body="<html><head><title>{$title}</title></head><body><table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">";
if(PEAR::isError($rset)) {
	$body.="<tr><td>".$rset->getMessage()."</td></tr>\n";
} else {
	while(($row=$rset->fetchRow())==true) {
		$a=$row['subnum'];
		$c=$row['count(subnum)'];
		$url="<a href=\"/monitoring/bester.php?search=subnum&value={$a}&hideaddress=true\">{$a}</a>";
		if($c > 1) {
			$body.="<tr><td>{$url}</td><td>{$c}</td></tr>";
		}
	}


}

$body.="</table></body></html>\n";
print $body;
?>
