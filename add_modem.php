<?php

require_once("config.php");

$body  = "<form method=\"post\" action=\"add_modem_action.php\">\n";
$body .= "<table width=\"300\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
$body .= "<tr><td colspan=\"2\" align=\"center\">Add Modem</td></tr>\n";

$uri=$_SERVER["REQUEST_URI"];
$lbl="<a class=\"blackNoDecoration\" onmouseover=\"popup('config_file')\" href=\"{$uri}\">config_file</a>";

if(isset($_GET['modem_macaddr'])) {
	$body .=entryLine('modem_macaddr',$_GET['modem_macaddr']);
} else {
	$body .=entryLine('modem_macaddr');
}
$body .=entryLine('cmts_vlan','1');
$body .=entryLine('serialnum');
$body .=entryLine('subnum');
$body .=staticConfig('config_file','',$lbl);

$dyn  =dynamicConfig('dynamic_config','',false);
$kk="<a class=\"blackNoDecoration\" onmouseover=\"popup('dynamic_config_file')\" href=\"{$uri}\">dynamic_config_file</a>";

$body.="<tr><td colspan=\"2\">&nbsp;</td></tr>\n";
$body.="<tr><td colspan=\"2\" align=\"center\">{$kk}</td></tr>\n";
$body.="<tr><td colspan=\"2\">{$dyn}</td></tr>\n";
$body.="<tr><td colspan=\"2\">&nbsp;</td></tr>\n";

$body.=entryLine('static_ip','0');
$body.=entryLine('dynamic_ip','0');
$body.=entryLine('config_opt','0');


/*
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
 */


$body .= "<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Add Line\"></td></tr>\n";

$body .= "</table>\n";
$body .= "</form>\n";
buildPage($body);


?>
