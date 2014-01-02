<?php

require_once("config.php");
checkSuper();

if(!isset($_GET['network'])) {
	header("Location: index.php");
	exit();
}
$network = $_GET['network'];
$sql="SELECT * FROM config_nets WHERE network='{$network}'";
$conn = connect();

$body ="<form method=\"post\" action=\"edit_network_action.php\">\n";
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
		case "nettype":
			$body.="<tr><td>{$kk}</td><td><select name=\"{$k}\">";
			foreach($config['nettypes'] as $type) {
				if($type == $v) {
					$body.="<option value=\"{$type}\" selected=\"selected\">{$type}</option>";
				} else {
					$body.="<option value=\"{$type}\">{$type}</option>";
				}
			}
			$body.="</select></td></tr>\n";
			break;
		case "dynamic_flag":
		case "full_flag":
		case "grant_flag":
			$body.=OptionYesNo($k,$v,$kk);
			break;
		case "network":
			$body.="\t<tr><td>{$kk}</td><td><input type=\"hidden\" name=\"{$k}\" value=\"{$v}\">{$v}</td></tr>\n";
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
