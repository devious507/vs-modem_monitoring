<?php

require_once("config.php");
checkSuper();

if(!isset($_GET['cfg_id'])) {
	header("Location: index.php");
	exit();
}
$c_id=$_GET['cfg_id'];
$sql="SELECT * FROM config_modem WHERE cfg_id={$c_id}";
$conn = connect();

$body ="<form method=\"post\" action=\"edit_modem_configs_action.php\">\n";
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
		case "cfg_id":
			$body.="\t<tr><td>{$kk}</td><td><input type=\"hidden\" name=\"{$k}\" value=\"{$v}\" size=\"15\">{$v}</td></tr>\n";
			break;
		case "cfg_txt":
			$body.="\t<tr><td>{$kk}</td><td><textarea name=\"{$k}\" rows=\"4\" cols=\"100\">{$v}</textarea></td></tr>\n";
			break;
		default:
			$body.="\t<tr><td>{$kk}</td><td><input type=\"text\" name=\"{$k}\" value=\"{$v}\" size=\"15\"></td></tr>\n";
			break;
		}
	}
}
$body.="<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Update\"></td></tr>\n";
$body.="</table>\n";
$body.="</form>\n";


buildPage($body,$sql);
?>
