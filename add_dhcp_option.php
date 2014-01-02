<?php

require_once("config.php");
checkSuper();

$body  = "<form method=\"post\" action=\"add_dhcp_option_action.php\">\n";
$body .= "<table width=\"300\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
$body .= "<tr><td colspan=\"2\" align=\"center\">Add DHCP Option</td></tr>\n";

$body .= entryLine("server_id");
$body .= entryLine("opt_id");
$body .= entryLine("opt_type");

$uri=$_SERVER["REQUEST_URI"];
$lbl="<a class=\"blackNoDecoration\" onmouseover=\"popup('opt_dtype')\" href=\"{$uri}\">opt_dtype</a>";
$body .= "<tr><td>{$lbl}</td><td><select name=\"opt_dtype\">";
foreach($config['dtypes'] as $type) {
	$body.="<option value=\"{$type}\">{$type}</option>\n";
}
$body .= "</select></td></tr>\n";

$body .= entryLine("opt_value",'');
$body .= entryLine("sub_opt","0");
$body .= entryLine("comment");
$body .= "<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Add Line\"></td></tr>\n";

$body .= "</table>\n";
$body .= "</form>\n";
buildPage($body);


?>
