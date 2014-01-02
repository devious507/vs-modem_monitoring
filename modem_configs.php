<?php

require_once("config.php");
checkSuper();

$sql="SELECT cfg_id,comment,cfg_errors,cfg_update FROM config_modem ORDER BY cfg_id";
//$sql="SELECT * FROM config_modem";
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
		$vals[]=$v;
	}
	$c_id=$row['cfg_id'];
	$keys[]="&nbsp;";
	$edit  ="<a class=\"tinyLink\" href=\"edit_modem_configs.php?cfg_id={$c_id}\">Edit</a>";
	$delete="<a class=\"tinyLink\" href=\"del_modem_configs.php?cfg_id={$c_id}\">Delete</a>";
	$vals[]="(".$edit." | ".$delete.")";
	$body.="\t<tr><td>".implode("</td><td>",$keys)."</td></tr>\n";
	$body.="\t<tr><td>".implode("</td><td>",$vals)."</td></tr>\n";
}

if($rset->numRows() > 1) {
	while(($row=$rset->fetchRow())==true) {
		unset($vals);
		foreach($row as $k=>$v) {
			$vals[]=$v;
		}
		$c_id=$row['cfg_id'];
		$edit  ="<a class=\"tinyLink\" href=\"edit_modem_configs.php?cfg_id={$c_id}\">Edit</a>";
		$delete="<a class=\"tinyLink\" href=\"del_modem_configs.php?cfg_id={$c_id}\">Delete</a>";
		$vals[]="(".$edit." | ".$delete.")";
		$body.="\t<tr><td>".implode("</td><td>",$vals)."</td></tr>\n";
	}
}
$body.="<tr><td colspan=\"8\"><a href=\"add_modem_configs.php\" class=\"tinyLink\">Add Config Segment</a></tr></td>\n";
$body.="<tr><td colspan=\"8\">&nbsp;</td></tr>\n";
$body.="</table>\n";


buildPage($body);
?>
