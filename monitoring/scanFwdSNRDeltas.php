<?php


define("MAX_DELTA","9.9");
define("MAX_MODEMS_TO_SCAN",'9999');
$rrd_dir="/var/www/monitoring/rrd/";

$end=time();
$start=$end-604800;  // 1 week in seconds is 604,800

if(is_dir($rrd_dir)) {
	$dh=opendir($rrd_dir);
	while(($file = readdir($dh)) !== false) {
		if(preg_match("/\.rrd$/",$file)) {
			if(count($files) < MAX_MODEMS_TO_SCAN) {
				$files[]=$file;
			}
		}
	}
	closedir($dh);
}

print "Scanning for Modems with more than ".MAX_DELTA."db between min and max values\n";

$count=0;
foreach($files as $file) {
	$datafile=$rrd_dir.$file;
	$rv=findMinMax($datafile,$start,$end);
	if($rv !== "OK") {
		if($count == 100) {
			print "!\n";
			$count=0;
		} else {
			print "!";
			$count++;
		}
		$bad_modems[]=$rv;
	} else {
		if($count == 100) {
			print ".\n";
			$count=0;
		} else {
			print ".";
			$count++;
		}
	}
}
$start_day  =date('j',$start);
$start_month=date('n',$start);
$start_year =date('Y',$start);
$end_day    =date('j',$end);
$end_month  =date('n',$end);
$end_year   =date('Y',$end);
foreach($bad_modems as $bad) {
	$urls[]="<a href=\"/monitoring/modemHistory.php?mac={$bad}&startmonth={$start_month}&startday={$start_day}&startyear={$start_year}&endmonth={$end_month}&end_day={$end_day}&end_year={$end_year}&graphtype=fwdrx&type2=+&type3=+\">{$bad}</a><br>\n";
}
$fh=fopen('/var/www/monitoring/BAD_FWD_SWINGS','w');
foreach($urls as $u) {
	fwrite($fh,$u);
}
fclose($fh);



function findMinMax($datafile,$start,$end) {
	$cmd="/usr/bin/rrdtool fetch {$datafile} MIN --start {$start} --end {$end}";
	$output=array();
	exec($cmd,&$retval);  

	$min=9999999;
	$max=-9999999;
	for($i=2; $i<count($retval); $i++) {
		if(!preg_match("/nan/",$retval[$i])) {
			$line=preg_split("/\s/",$retval[$i]);
			$line[1]=round($line[1]*1,1);
			if($line[1] < $min) {
				//print "New Minimum Found: {$line[1]}\n";
				$min=$line[1];
			}
		}
	}
	//print "\n\n";
	$cmd="/usr/bin/rrdtool fetch {$datafile} MAX --start {$start} --end {$end}";
	exec($cmd,&$retval);  
	for($i=2; $i<count($retval); $i++) {
		if(!preg_match("/nan/",$retval[$i])) {
			$line=preg_split("/\s/",$retval[$i]);
			if(isset($line[1]) && $line[1]!='') {
				$line[1]=round($line[1]*1,1);
				if($line[1] > $max) {
					//print "New Maximum Found: {$line[1]}\n";
					$max=$line[1];
				}
			}
		}
	}


	$delta=$max-$min;
	if($delta >= MAX_DELTA) {
		$arr=preg_split("/\//",$datafile);
		$ct=count($arr)-1;
		$arr[$ct]=preg_replace("/\.rrd/","",$arr[$ct]);
		return $arr[$ct];
	} else {
		return "OK";
	}
}
