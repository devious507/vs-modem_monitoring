<?php

include_once("../../config.php");
include_once("include/GoogleMap.php");
include_once("include/JSMin.php");


$limit=400;

$map = new GoogleMapAPI();
$map->_minify_js=isset($_REQUEST['min'])?FALSE:TRUE;
$map->setWidth(1300);
$map->setHeight(650);
$db =connect();

$sql="select g.subnum FROM customer_geocode AS g LEFT OUTER JOIN customer_address AS c ON g.subnum=c.subnum WHERE c.subnum IS NULL";
$res = $db->query($sql);
while(($row=$res->fetchRow())==true) {
	$sql2="DELETE FROM customer_geocode WHERE subnum={$row['subnum']}";
	$res2=$db->query($sql2);
}

$sql = "SELECT c.subnum AS c_subnum,c.apartment,c.address,c.city,c.state,g.subnum FROM customer_address AS c LEFT OUTER JOIN customer_geocode AS g ON c.subnum=g.subnum WHERE g.subnum IS NULL LIMIT {$limit}";

$res = $db->query($sql);
while(($row=$res->fetchRow())==true) {
	$addr = $row['address'].' '.$row['apartment'].', '.$row['city'].' '.$row['state'];
	$latlon=$map->getGeoCode($addr);
	$lat = $latlon['lat'];
	$lon = $latlon['lon'];
	$pair = " ({$lat},{$lon})";
	if($lat != '' && $lon != '') {
		$sql2 = "INSERT INTO customer_geocode VALUES ({$row['c_subnum']},{$lat},{$lon})";
		$res2=$db->query($sql2);
		print $addr.$pair."<br>\n";
	} else {
		print $row['c_subnum']." cannot be geocoded<br>\n";
	}
	flush();
	ob_flush();
}


print "<a href=\"/\">Back</a>";
?>
