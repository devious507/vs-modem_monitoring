<?php

require_once("config.php");
checkSuper();

$sql="SELECT nettype,cmts_ip,network,gateway,range_min,range_max,lease_time,config_opt1 FROM config_nets ORDER BY cmts_ip,nettype,network";
$sql="SELECT nettype,cmts_ip,network,lease_time,config_opt1,dynamic_flag as dyn,full_flag as full FROM config_nets ORDER BY cmts_ip,nettype,network";
$conn = connect();

$body="<table width=\"100%\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
$rset = $conn->query($sql);
if(PEAR::isError($rset)) {
	$body="SQL Error: {$sql}<br>";
	$body.=$rset->getMessage();
	buildPage($body);
}
if($rset->numRows() > 0) {
	$row=$rset->fetchRow();
	foreach($row as $k=>$v) {
		$uri=$_SERVER["REQUEST_URI"];
		$keys[]="<a class=\"blackNoDecoration\" onmouseover=\"popup('{$k}')\" href=\"{$uri}\">{$k}</a>";
		if($k == "config_opt1") {
			$vals[]="<a class=\"blackUnderline\" href=\"dhcp_options.php?opt_id={$v}\">{$v}</a>";
		} else {
			$vals[]=$v;
		}
	}
	$network = $row['network'];
	$keys[]="&nbsp;";
	$edit  ="<a class=\"tinyLink\" href=\"edit_network.php?network={$network}\">Edit</a>";
	$delete="<a class=\"tinyLink\" href=\"del_network.php?network={$network}\">Delete</a>";
	$vals[]="(".$edit." | ".$delete.")";
	$body.="\t<tr><td>".implode("</td><td>",$keys)."</td></tr>\n";
	$body.="\t<tr><td>".implode("</td><td>",$vals)."</td></tr>\n";
}

if($rset->numRows() > 1) {
	while(($row=$rset->fetchRow())==true) {
		unset($vals);
		foreach($row as $k=>$v) {
			if($k == "config_opt1") {
				$vals[]="<a class=\"blackUnderline\" href=\"dhcp_options.php?opt_id={$v}\">{$v}</a>";
			} else {
				$vals[]=$v;
			}
		}
		$network = $row['network'];
		$edit  ="<a class=\"tinyLink\" href=\"edit_network.php?network={$network}\">Edit</a>";
		$delete="<a class=\"tinyLink\" href=\"del_network.php?network={$network}\">Delete</a>";
		$vals[]="(".$edit." | ".$delete.")";
		$body.="\t<tr><td>".implode("</td><td>",$vals)."</td></tr>\n";
	}
}
$body.="<tr><td colspan=\"9\"><a href=\"add_network.php\" class=\"tinyLink\">Add Network</a></tr></td>\n";
$body.="</table>\n";


buildPage($body);
?>
