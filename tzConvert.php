<?php

require_once("config.php");
if(!isset($_GET['time'])) {
	$zuluTime=$_GET['time'];
	$p="<form method=\"get\" action=\"tzConvert.php\">";
	$p.="Timestamp (2014-01-31T00:01:02Z format)<br><input type=\"text\" size=\"12\" name=\"time\">";
	$p.="<br><input type=\"submit\"></form>";
	buildPage($p);
} else {
	buildPage(convertTZLocal($_GET['time']));
}
function convertTZLocal($stamp) {
	if(preg_match("/Z$/",$stamp)) {
		$stamp=preg_replace("/Z/","",$stamp);
		$dt=preg_split("/T/",$stamp);
		$date=preg_split("/-/",$dt[0]);
		$time=preg_split("/:/",$dt[1]);
		$time=gmmktime($time[0],$time[1],$time[2],$date[1],$date[2],$date[0]);
		return date("m/d/Y H:i:s",$time);
	} else {
		return $stamp;
	}
}
