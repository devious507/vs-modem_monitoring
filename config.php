<?php

define('TARGET',0);
define('BASE_DIR','/var/www');
define('MYSQL_HOST','localhost');
define('MYSQL_USER','docsis');
define('MYSQL_PASS','99rdblns');
define('MYSQL_DB','dhcp_server');
define('DOCSIS_SERVER_CONFIG','/etc/docsis-server.conf');
define("rrdTool","/usr/bin/rrdtool");
define("rrdDir","/var/www/monitoring/rrd/");



// Defines for what cfg_id's to use for various config-file pieces
define('DS_FLOW_START','100');
define('DS_FLOW_END','199');
define('US_FLOW_START','200');
define('US_FLOW_END','299');
define('DS_FREQ_START','300');
define('DS_FREQ_END','399');
define('US_CHAN_START','400');
define('US_CHAN_END','499');
define('BPI_START','500');
define('BPI_END','599');
define('SVC_CLASS_START','600');
define('SVC_CLASS_END','699');
define('SNMP_START','700');
define('SNMP_END','799');
define('DOCSIS2_START','800');
define('DOCSIS2_END','899');
define('NETWORK_ACCESS_START','1000');
define('NETWORK_ACCESS_END','1005');
define('CONFIG_SERVER_START','1050');
define('CONFIG_SERVER_END','1059');
define('CONFIG_FILE_START','1100');
define('CONFIG_FILE_END','1199');
define('CVC_START','1200');
define('CVC_END','1299');


// Defines for Super User and Std User
define('SUPER_USER','admin');
define('SUPER_PASS','docsis');
define('USER','csr');
define('PASS','csr');

// Nothing to change below this line
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
        $menu2.="<li><a href=\"oldLeases.php\">Old Leases</a></li>\n";
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
		if(!preg_match('/API.php/',$_SERVER['REQUEST_URI'])) {
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
