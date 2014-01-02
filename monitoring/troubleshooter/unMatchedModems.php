<?php

require_once("../config.php");

$sql="SELECT aa.*,m.* FROM (select d.modem_macaddr,d.subnum AS dsubnum,c.* FROM docsis_modem AS d LEFT OUTER JOIN customer_address AS c ON d.subnum=c.subnum WHERE c.subnum is null) as aa LEFT OUTER JOIN modem_history AS m ON aa.modem_macaddr=m.mac";

$db = connect();
$rset=$db->query($sql);
$headers=false;
$tbl="<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
while(($row=$rset->fetchRow())==true) {
	if($headers == false) {
		$headers=true;
		$tbl.=getHeaders($row);
	}
	$tbl.="\t<tr>";
	foreach($row as $k=>$v) {
		switch($k) {
		case "modem_macaddr":
			$url="<a href=\"/modem.php?search=modem_macaddr&value={$v}\">{$v}</a>";
			$tbl.="<td>{$url}</td>";
			break;
		default:
			$tbl.="<td>{$v}</td>";
			break;
		}
	}
	$tbl.="</tr>\n";
}
$tbl.="</table>\n";

function getHeaders($r) {
	$rv="\n<tr>";
	foreach($r as $k=>$v) {
		$rv.="<th align=\"left\">{$k}</th>";
	}
	$rv.="</tr>\n";
	return $rv;
}
?>
<html>
<head>
<title></title>
</head>
<body>
<?php echo $tbl; ?>
</body>
</html>
