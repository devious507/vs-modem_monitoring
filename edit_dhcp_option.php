<?php

require_once("config.php");

checkSuper();
if( (!isset($_GET['server_id'])) || (!isset($_GET['opt_id'])) ) {
	header("Location: index.php");
	exit();
}
$s_id=$_GET['server_id'];
$o_id=$_GET['opt_id'];
$o_type=$_GET['opt_type'];
$sql="SELECT * FROM config_opts WHERE server_id={$s_id} AND opt_id={$o_id} AND opt_type='{$o_type}'";
$conn = connect();

$body ="<form method=\"post\" action=\"edit_dhcp_option_action.php\">\n";
$body.="<table width=\"300\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
$rset = $conn->query($sql);
if(PEAR::isError($rset)) {
	$body="SQL Error: {$sql}<br>";
	$body.=$rset->getMessage();
	buildPage($body);
}
$num = $rset->numRows();
if($num != 1) {
	$body.="<tr><td>Incorrect number of results returned ({$num})</td></tr>\n";
} else {
	$row=$rset->fetchRow();
	foreach($row as $k=>$v) {
		$uri=$_SERVER["REQUEST_URI"];
		$kk="<a class=\"blackNoDecoration\" onmouseover=\"popup('{$k}')\" href=\"{$uri}\">{$k}</a>";

		switch($k) {
		case "opt_dtype":
			$body.="<tr><td>{$kk}</td><td><select name=\"{$k}\">";
			foreach($config['dtypes'] as $type) {
				if($type == $v) {
					$body.="<option value=\"{$type}\" selected=\"selected\">{$type}</option>";
				} else {
					$body.="<option value=\"{$type}\">{$type}</option>";
				}
			}
			$body.="</select></td></tr>\n";
			break;
		case "opt_type":
		case "server_id":
		case "opt_id":
			$body.="\t<tr><td>{$kk}</td><td><input type=\"hidden\" name=\"{$k}\" value=\"{$v}\">{$v}</tr>\n";
			break;
		default:
			$body.="\t<tr><td>{$kk}</td><td><input type=\"text\" name=\"{$k}\" value=\"{$v}\" size=\"15\"></tr>\n";
			break;
		}
	}
}
$body.="<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Update\"></td></tr>\n";
$body.="</table>\n";
$body.="</form>\n";


buildPage($body,$sql);
?>
