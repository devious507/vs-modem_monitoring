<?php

require_once("config.php");

if(!isset($_GET['modem_macaddr'])) {
	header("Location: index.php");
	exit();
}
if(isset($_GET['mode']) && ($_GET['mode'] = 1)) {
	$mode=1;
} else {
	$mode='1.1+';
}
$mac = $_GET['modem_macaddr'];
$sql="SELECT * FROM docsis_modem WHERE modem_macaddr='{$mac}'";
$conn = connect();

$body ="<form method=\"post\" action=\"edit_modem_action.php\">\n";
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
	if($num==0) {
		if(isset($_GET['modem_macaddr'])) {
			$body.="<tr><td><a href=\"add_modem.php?modem_macaddr={$_GET['modem_macaddr']}\">Add Modem</a></td></tr>\n";
		} else {
			$body.="<tr><td><a href=\"add_modem.php\">Add Modem</a></td></tr>\n";
		}
	}

} else {
	$row=$rset->fetchRow();
	foreach($row as $k=>$v) {
		$uri=$_SERVER["REQUEST_URI"];
		$kk="<a class=\"blackNoDecoration\" onmouseover=\"popup('{$k}')\" href=\"{$uri}\">{$k}</a>";
		switch($k) {
		case "config_file":
			$body.=staticConfig($k,$v,$kk);
			break;
		case "dynamic_config_file":
			if($mode == 1) {
				$dynamic = dynamicConfig1($k,$v);
			} else {
				// True -- Allow 1.0 Provisioning
				// False -- Deny 1.0 Provisioning
				$dynamic = dynamicConfig($k,$v,true);
			}
			$body.="<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
			$body.="<tr><td colspan=\"2\" align=\"center\">{$kk}</td></tr>\n";
			$body.="<tr><td colspan=\"2\">{$dynamic}</td></tr>\n";
			$body.="<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
			break;
		case "modem_macaddr":
			$body.="\t<tr><td>{$kk}</td><td><input type=\"hidden\" name=\"{$k}\" value=\"{$v}\">{$v}</td></tr>\n";
			break;
		default:
			$body.="\t<tr><td>{$kk}</td><td><input type=\"text\" name=\"{$k}\" value=\"{$v}\" size=\"15\"></tr>\n";
			break;
		}
	}
	$body.="<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Update\"></td></tr>\n";
}
$body.="</table>\n";
$body.="</form>\n";


buildPage($body,$sql);
?>
