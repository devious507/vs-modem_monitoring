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
define('USER','tech');
define('PASS','VisionTech');
?>
