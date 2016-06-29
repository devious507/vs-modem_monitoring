<?php

// Nothing to change below this line
require_once("defines.php");
require_once("functions.php");
/*
	if( (!isset($_COOKIE['username'])) || (!isset($_COOKIE['password'])) ) {
		doLoginPage();
	}
}
 */

if( ($_COOKIE['username'] == SUPER_USER) && ($_COOKIE['password'] == SUPER_PASS) ) {
	$menu1 ="<li><a href=\"modem.php\">Modems</a></li>\n";
	$menu1.="<hr>\n";
	$menu1.="<li><a href=\"modem_configs.php\">Modem Options</a></li>\n";
	$menu1.="<li><a href=\"networks.php\">Networks</a></li>\n";
	$menu1.="<li><a href=\"dhcp_options.php\">DHCP Options</a></li>\n";

	$menu2 ="<hr>\n<p>Utilities</p>\n<hr>\n";
        $menu2.="<ul>\n";
	$menu2.="<li><a href=\"monitoring/uploadAddress.php\">Upload Addresses</a></li>\n";
	$menu2.="<li><a href=\"monitoring/map/geoCode.php\">Geocode Addresses</a></li>\n";
	$menu2.="<li><a href=\"monitoring/bester.php\">Modem List</a></li>\n";
	$menu2.="<li><a href=\"monitoring/troubleshooter/index.php\">Troubleshooter</a></li>\n";
        $menu2.="<li><a href=\"currentDhcpLeases.php\">Current Leases</a></li>\n";
        $menu2.="<li><a href=\"/dmca/index.php\">Old Leases</a></li>\n";
	$menu2.="<li><a href=\"monitoring/map.php\">Modem Maps</a>\n";
	$menu2.="<li><a href=\"logout.php\">Logout</a></li>\n";
        $menu2.="</ul>\n";

	$menu2.="<hr>\n<p>Configuration</p>\n<hr>\n";
        $menu2.="<ul>\n";
	$menu2.="<li><a href=\"networkAccessBuilder.php\">Network Access</a></li>";
	$menu2.="<li><a href=\"docsis2AccessBuilder.php\">Docsis2 Access</a></li>";
	$menu2.="<li><a href=\"flowBuilder.php\">Flow Builder</a></li>\n";
	$menu2.="<li><a href=\"dsFrequency.php\">DS Frequency</a></li>\n";
	$menu2.="<li><a href=\"usChannels.php\">US Channel</a></li>\n";
	$menu2.="<li><a href=\"bpiBuilder.php\">BPI Stanzas</a></li>\n";
	$menu2.="<li><a href=\"docsis1ServiceLevels.php\">1.0 Svc Level</a></li>\n";
        $menu2 .="</ul>";
} elseif( ($_COOKIE['username'] == USER) && ($_COOKIE['password'] == PASS) ) {
	$menu1  ="<li><a href=\"modem.php\">Modems</a></li>\n";
	$menu2  ="<hr>\n<p>Utilities</p>\n<hr>\n<ul>\n";
	$menu2.="<li><a href=\"monitoring/bester.php\">Modem List</a></li>\n";
	$menu2.="<li><a href=\"monitoring/troubleshooter/index.php\">Troubleshooter</a></li>\n";
        $menu2 .="<li><a href=\"currentDhcpLeases.php\">Current Leases</a></li>\n";
        $menu2 .="<li><a href=\"oldLeases.php\">Old Leases</a></li>\n";
	$menu2.="<li><a href=\"monitoring/map.php\">Modem Maps</a>\n";
	$menu2 .="<li><a href=\"logout.php\">Logout</a></li>\n";
        $menu2 .="</ul>";
} else {
	if(php_sapi_name() != 'cli') { 
		if(preg_match('/API.php/',$_SERVER['REQUEST_URI'])) {
		} elseif((preg_match("/ATA-JSON.php/",$_SERVER['REQUEST_URI']))) {
		} elseif((preg_match("/primChannel.php/",$_SERVER['REQUEST_URI']))) {
		} else {
			doLoginPage();
			exit();
		} 
	}
}
require_once("MDB2.php");

// Error Reporting: comment these lines out for production
error_reporting(E_ALL);
ini_set("display_errors", 1);

?>
