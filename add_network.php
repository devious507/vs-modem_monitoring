<?php

require_once("config.php");
checkSuper();

$body  = "<form method=\"post\" action=\"add_network_action.php\">\n";
$body .= "<table width=\"300\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
$body .= "<tr><td colspan=\"2\" align=\"center\">Add Network</td></tr>\n";

$uri=$_SERVER["REQUEST_URI"];
$lbl="<a class=\"blackNoDecoration\" onmouseover=\"popup('nettype')\" href=\"{$uri}\">nettype</a>";
$body .= "<tr><td>{$lbl}</td><td><select name=\"nettype\">";
foreach($config['nettypes'] as $type) {
	$body.="<option value=\"{$type}\">{$type}</option>";
}
$body .="</select></td></tr>\n";

$body .=entryLine('cmts_ip');
$body .=entryLine('cmts_vlan','1');
$body .=entryLine('network');
$body .=entryLine('gateway');
$body .=OptionYesNo('grant_flag');
$body .=OptionYesNo('dynamic_flag');
$body .=OptionYesNo('full_flag');
$body .=entryLine('range_min');
$body .=entryLine('range_max');
$body .=entryLine('lease_time');
$body .=entryLine('config_opt1');
$body .=entryLine('config_opt2');
$body .=entryLine('config_opt3');


$body .= "<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Add Line\"></td></tr>\n";

$body .= "</table>\n";
$body .= "</form>\n";
buildPage($body);


?>
