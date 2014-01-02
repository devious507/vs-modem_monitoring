<?php

require_once("config.php");
checkSuper();

$body  = "<form method=\"post\" action=\"add_modem_configs_action.php\">\n";
$body .= "<table width=\"600\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
$body .= "<tr><td colspan=\"2\" align=\"center\">Add Modem Config Snippet</td></tr>\n";

if(isset($_GET['cfg_id'])) {
	$body .= entryLine("cfg_id",$_GET['cfg_id']);
} else {
	$body .= entryLine("cfg_id");
}
$body .= entryLine("comment");
if(isset($_GET['cfg_txt'])) {
	$body .= entryLine("cfg_txt",$_GET['cfg_txt']);
} else {
	$body .= entryLine("cfg_txt");
}
$body .= entryLine("cfg_ver","any");
$body .= entryLine("cfg_update","Now()");


$body .= "<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Add Line\"></td></tr>\n";

$body .= "</table>\n";
$body .= "</form>\n";
buildPage($body);


?>
