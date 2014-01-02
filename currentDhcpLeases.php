<?php

require_once("config.php");

$conn = connect();

$sql="SELECT ipaddr,macaddr,start_time,end_time,pc_name,subnum,modem_macaddr FROM dhcp_leases ORDER BY end_time DESC";
$rset=$conn->query($sql);
if(PEAR::isError($rset)) {
	buildPage($rset->getMessage(),$sql);
	exit();
}

$num = $rset->numRows();
$page="<table width=\"100%\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
if($num > 0) {
	$row = $rset->fetchRow();
	foreach($row as $k=>$v) {
		switch($k) {
		case "pc_name":
			$ks[]=$k;
			if(strlen($v) > 12) {
				$vs[]=substr($v,0,12);
			} else {
				$vs[]=$v;
			}
			break;
		case "modem_macaddr":
			$ks[]=$k;
			$vs[]="<a href=\"edit_modem.php?modem_macaddr={$v}\">{$v}</a>";
			break;
		case "subnum":
			$ks[]=$k;
			$vs[]="<a href=\"oldLeases.php?subnum={$v}\">{$v}</a>";
			break;
		default:
			$ks[]=$k;
			$vs[]=$v;
			break;
		}
	}
	$page.="<tr><td>".implode("</td><td>",$ks)."</td></tr>\n";
	$page.="<tr><td>".implode("</td><td>",$vs)."</td></tr>\n";
}
if($num >1) {
	while(($row=$rset->fetchRow())==true) {
		unset($vs);
		foreach($row as $k=>$v) {
			switch($k) {
			case "pc_name":
				$ks[]=$k;
				if(strlen($v) > 12) {
					$vs[]=substr($v,0,12);
				} else {
					$vs[]=$v;
				}
				break;
			case "modem_macaddr":
				$ks[]=$k;
				$vs[]="<a href=\"edit_modem.php?modem_macaddr={$v}\">{$v}</a>";
				break;
			case "subnum":
				$ks[]=$k;
				$vs[]="<a href=\"oldLeases.php?subnum={$v}\">{$v}</a>";
				break;
			default:
				$ks[]=$k;
				$vs[]=$v;
				break;
			}
		}
		$page.="<tr><td>".implode("</td><td>",$vs)."</td></tr>\n";
	}
}


$page.="</table>\n";
buildPage($page);


?>
