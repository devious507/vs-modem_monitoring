<?php

require_once("config.php");

checkSuper();
if(isset($_GET['opt_id'])) {
	$s_id = $_GET['opt_id'];
	$sql="SELECT * FROM config_opts WHERE opt_id={$s_id} ORDER BY server_id,opt_id,opt_type";
} else {
	$sql="SELECT * FROM config_opts ORDER BY server_id,opt_id,opt_type";
}
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
		if($k == "opt_id") {
			$vals[]="<a class=\"blackUnderline\" href=\"dhcp_options.php?opt_id={$v}\">{$v}</a>";
		} else {
			$vals[]=$v;
		}
	}
	$s_id=$row['server_id'];
	$o_id=$row['opt_id'];
        $o_type=$row['opt_type'];
	$keys[]="&nbsp;";
	$edit  ="<a class=\"tinyLink\" href=\"edit_dhcp_option.php?server_id={$s_id}&opt_id={$o_id}&opt_type={$o_type}\">Edit</a>";
	$delete="<a class=\"tinyLink\" href=\"del_dhcp_option.php?server_id={$s_id}&opt_id={$o_id}&opt_type={$o_type}\">Delete</a>";
	$vals[]="(".$edit." | ".$delete.")";
	$body.="\t<tr><td>".implode("</td><td>",$keys)."</td></tr>\n";
	$body.="\t<tr><td>".implode("</td><td>",$vals)."</td></tr>\n";
}

if($rset->numRows() > 1) {
	while(($row=$rset->fetchRow())==true) {
		unset($vals);
		foreach($row as $k=>$v) {
			if($k == "opt_id") {
				$vals[]="<a class=\"blackUnderline\" href=\"dhcp_options.php?opt_id={$v}\">{$v}</a>";
			} else {
				$vals[]=$v;
			}
		}
		$s_id=$row['server_id'];
		$o_id=$row['opt_id'];
        	$o_type=$row['opt_type'];
		$edit  ="<a class=\"tinyLink\" href=\"edit_dhcp_option.php?server_id={$s_id}&opt_id={$o_id}&opt_type={$o_type}\">Edit</a>";
		$delete="<a class=\"tinyLink\" href=\"del_dhcp_option.php?server_id={$s_id}&opt_id={$o_id}&opt_type={$o_type}\">Delete</a>";
		$vals[]="(".$edit." | ".$delete.")";
		$body.="\t<tr><td>".implode("</td><td>",$vals)."</td></tr>\n";
	}
}
$body.="<tr><td colspan=\"8\"><a href=\"add_dhcp_option.php\" class=\"tinyLink\">Add Option</a></tr></td>\n";
$body.="</table>\n";


buildPage($body);
?>
