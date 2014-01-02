<?php


//   0   1  2  3  4  5
// 2012-10-29T19:26:11Z
//
//

IPDRTime2Localtime();

function IPDRTime2Localtime($tstamp = '2012-10-29T19:26:11Z') {
	date_default_timezone_set('UTC');
	$pat = '%d-%d-%dT%d:%d:%dZ';
	$arr = sscanf($tstamp,$pat);
	$time = mktime($arr[3],$arr[4],$arr[5],$arr[1],$arr[2],$arr[0]);
	date_default_timezone_set('America/Chicago');
	print $tstamp."\n";
	print date('Y-m-d H:i:s e',$time)."\n";
}

