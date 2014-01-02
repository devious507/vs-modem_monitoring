<?php

$epoch = mktime(5,15,0,11,30,2012);
$start = $epoch - 300;
$end = $epoch + 300;
$dataDir = "/var/www/monitoring/rrd/";

// rrdtool xport -s 1354159800 DEF:xx=94CCB9AE8B5E.rrd:fwdrx:AVERAGE XPORT:xx:fwdrx 

$cmdPat = "rrdtool xport -s %s -e %s DEF:xx={$dataDir}%s:fwdrx:AVERAGE XPORT:xx:fwdrx";


$dh = opendir($dataDir);

while(($filename=readdir($dh)) == true) {
	if(preg_match("/.rrd$/",$filename)) {
		if($filename != '.rrd') {
			$macAddress=preg_replace("/.rrd$/","",$filename);
			$cmd=sprintf($cmdPat,$start,$end,$filename);
			print fetchRRD($cmd,$epoch,$macAddress);
		}
	}
}


function fetchRRD($cmd,$timeTarget,$mac) {
	exec($cmd,$dataArray);
	$data=implode("\n",$dataArray);
	//print $data."\n\n";
	$xml =new SimpleXMLElement($data);
	$count=0;
	foreach($xml->data->row as $r) {
		$t=date('m-d-Y H:i:s',intval($r->t[0]));
		$v=$r->v[0];
		print "{$mac} -- {$t} -- {$v}\n";
	}
}
?>
