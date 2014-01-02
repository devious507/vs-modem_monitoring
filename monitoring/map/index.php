<?php

include_once("../../config.php");
include_once("include/GoogleMap.php");
include_once("include/JSMin.php");

// How many minutes befor deciding a modem is offline?
$interval = 10;

$map = new GoogleMapAPI();
$map->_minify_js=isset($_REQUEST['min'])?FALSE:TRUE;
$map->setWidth('100%');
$map->setHeight('100%');
if(isset($_GET['cluster']) && $_GET['cluster'] == 'false') {
} else {
	// Enable Marker Clustering
	$map->enableClustering();
	$map->setClusterOptions(15);
	$map->setClusterLocation("include/markerclusterer_compiled.js");
}



$sql="SELECT b.*,h.time FROM (SELECT a.*,d.modem_macaddr FROM (select c.subnum,c.name,c.address,c.apartment,c.city,c.state,c.zip,c.building,g.lat,g.lon FROM customer_address AS c LEFT OUTER JOIN customer_geocode AS g ON c.subnum=g.subnum WHERE g.subnum IS NOT NULL) AS a LEFT OUTER JOIN docsis_modem AS d ON a.subnum=d.subnum WHERE d.subnum IS NOT NULL) as b LEFT OUTER JOIN modem_history AS h ON b.modem_macaddr=h.mac";
if(isset($_GET['mode'])) {
	if($_GET['mode'] == 'offline') {
		$a = "SELECT c.* FROM ({$sql}) AS c WHERE c.time < DATE_SUB(now(), INTERVAL {$interval} MINUTE)";
		$sql=$a;
	} elseif($_GET['mode'] == 'offline24') {
		$a = "SELECT c.* FROM ({$sql}) AS c WHERE c.time < DATE_SUB(now(), INTERVAL {$interval} MINUTE) AND c.time > DATE_SUB(now(), INTERVAL 24 HOUR)";
		$sql=$a;
	} elseif($_GET['mode'] == 'limit') {
		if(isset($_GET['field']) && isset($_GET['value'])) {
			$field=$_GET['field'];
			$value=$_GET['value'];
			$a = "SELECT c.* FROM ({$sql}) AS c WHERE c.{$field}='{$value}'";
			$sql=$a;
			//print $sql; exit();
		}
	}
}
$db = connect();
$res = $db->query($sql);
while(($row=$res->fetchRow())==true) {
	$divisor=1000000;
	$latoffset = rand(0,99)/$divisor;
	$lonoffset = rand(0,99)/$divisor;
	if(rand(0,1) == 0) {
		$latoffset*=-1;
	}
	if(rand(0,1) == 0) {
		$lonoffset*=-1;
	}
	$lat=$row['lat']+$latoffset;
	$lon=$row['lon']+$lonoffset;
	$lbl=$row['name'].' ('.$row['subnum'].')<br>'.$row['address'].'<br>'.$row['apartment'].'<br>'.$row['city'].', '.$row['state'].' '.$row['zip'];
	$lbl.='<br>'.$row['time'];
	$url="<a href=\"/monitoring/modemHistory.php?mac={$row['modem_macaddr']}\">{$row['modem_macaddr']}</a>";
	$lbl.='<br>'.$url;
	$lbl.="<br>({$lat},{$lon})";
	$map->addMarkerByCoords($lon,$lat,'',$lbl);
}


?>
<html>
<head>
<?=$map->getHeaderJS();?>
<?=$map->getMapJS();?>
</head>
<body>
<!--
<?=$sql;?>
-->
<?=$map->printOnLoad();?>
<?=$map->printMap();?>
<?=$map->printSidebar();?>
</body>
</html>
