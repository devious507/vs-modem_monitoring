<?php

$_COOKIE['username']='csr';
$_COOKIE['password']='csr';
require_once("config.php");


$sql[]='CREATE TEMPORARY TABLE online_offline AS (SELECT a.time,c.node,c.property,c.building FROM (select m.time,m.mac,d.subnum FROM modem_history AS m LEFT OUTER JOIN docsis_modem AS d on m.mac=d.modem_macaddr) as a LEFT OUTER JOIN customer_address AS c ON a.subnum=c.subnum)';
$sql[]='ALTER TABLE online_offline ADD online_offline VARCHAR(8)';
$sql[]="UPDATE online_offline SET online_offline='offline' WHERE time < date_sub(now(),INTERVAL 30 MINUTE)";
$sql[]="UPDATE online_offline SET online_offline='online' WHERE online_offline IS NULL";
$sql[]='DELETE FROM online_offline WHERE property IS NULL';

$sql_property='select count(property),property,online_offline FROM online_offline GROUP BY property,online_offline ORDER BY property,online_offline';
$sql_node='select count(node),node,property,online_offline FROM online_offline GROUP BY node,property,online_offline ORDER BY node,property,online_offline';
$sql_bldg='select count(building),building,property,online_offline FROM online_offline GROUP BY building,property,online_offline ORDER BY property,building,online_offline';

if(!isset($_GET['query_type'])) {
	header("Location: index.php");
	exit();
}
$db = connect();
foreach($sql as $s) {
	$db->query($s);
}
switch($_GET['query_type']) {
case "node":
	$rset = $db->query($sql_node);
	$arr = getArray($rset,'node');
	header("Content-type: text/plain");
	print serialize($arr);
	exit();
case "property":
	$rset = $db->query($sql_property);
	$arr = getArray($rset,'property');
	header("Content-type: text/plain");
	print serialize($arr);
	exit();
case "building":
	$rset = $db->query($sql_bldg);
	$arr = getArray($rset,'building');
	header("Content-type: text/plain");
	print serialize($arr);
	exit();
default:
	header("Location: API.php?query_type=building");
	exit();
}

function getArray($rset,$type) {
	$rv=array();
	while(($row=$rset->fetchRow())==true) {
		switch($type) {
		case "property":
			$prop = $row['property'];
			if(!isset($rv[$prop]['online']))
				$rv[$prop]['online']=0;
			if(!isset($rv[$prop]['offline']))
				$rv[$prop]['offline']=0;
			$rv[$prop][$row['online_offline']]=intval($row['count(property)']);
			$rv[$prop]['property']=$prop;
			break;
		case "node":
			if($row['node'] != 0) {
				$node = $row['node'];
				$prop = $row['property'];
				if(!isset($rv[$node]['online']))
					$rv[$node]['online']=0;
				if(!isset($rv[$node]['offline']))
					$rv[$node]['offline']=0;
				$rv[$node][$row['online_offline']]=intval($row['count(node)']);
				$rv[$node]['node']=$node;
				$rv[$node]['property']=$prop;
			}
			break;
		case "building":
			$bldg = $row['building'];
			$prop = $row['property'];
			if(!isset($rv[$prop][$bldg]['online']))
				$rv[$prop][$bldg]['online']=0;
			if(!isset($rv[$prop][$bldg]['offline']))
				$rv[$prop][$bldg]['offline']=0;
			$rv[$prop][$bldg][$row['online_offline']]=intval($row['count(building)']);
			$rv[$prop][$bldg]['property']=$prop;
			$rv[$prop][$bldg]['building']=$bldg;
			break;
		}
	}
	return $rv;
}
