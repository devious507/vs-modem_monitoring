<?php

$_COOKIE['username']='csr';
$_COOKIE['password']='csr';
require_once("config.php");

define("lockfile","/tmp/modemScanner.LOCK");
define("community","V5LLC2012");

define("interfaceNames",".1.3.6.1.2.1.31.1.1.1.1");
define("macListing",".1.3.6.1.2.1.10.127.1.3.3.1.2");
define("ipListing",".1.3.6.1.2.1.10.127.1.3.3.1.3");
define("revRx",".1.3.6.1.2.1.10.127.1.3.3.1.6");
define("revSnr",".1.3.6.1.2.1.10.127.1.3.3.1.13");
define("cmStatus",".1.3.6.1.2.1.10.127.1.3.3.1.9");
define("upstreams","1.3.6.1.2.1.10.127.1.1.4.1.5");
define("modemRx",".1.3.6.1.2.1.10.127.1.1.1.1.6");
define("fwdSnr",".1.3.6.1.2.1.10.127.1.1.4.1.5");
define("revTx",".1.3.6.1.2.1.10.127.1.2.2.1.3");
define("modemDownChannel",".1.3.6.1.2.1.10.127.1.3.3.1.4");
define("modemUpChannel",".1.3.6.1.2.1.10.127.1.3.3.1.5");


if(file_exists(lockfile)) {
	$fp=fopen(lockfile,'r');
	$time = fread($fp,1024);
	fclose($fp);
	$NowTime = time();
	if($NowTime - $time > 1198) {
		unlink(lockfile);
	} else {
		$logFileName = '/tmp/modemScanner.out';
		$fileData = file_get_contents($logFileName);
		$humanTime = date('m-d-Y H:i:s');
		$body = "lock file exists at {$humanTime}, not running monitoring";
		$body.="\n\n";
		$body.=$fileData;
		$subject = "monitoring did not run {$humanTime}";
		$to = 'daveb@visionsystems.tv,paulo@visionsystems.tv';
		mail($to,$subject,$body);
		print "Process started in last 20 minutes, refusing to run!\n\n";
		exit();
	}
}
print "Creating lockfile\n";
$fp=fopen(lockfile,'w');
fwrite($fp,time());
fclose($fp);


//$hosts=array('38.108.136.4');
$hosts=array('10.1.1.1','172.16.12.1');

$start = time();
foreach($hosts as $host) {
	$a = scanHost($host);
	var_dump($a);
}
$end = time();
$el=$end-$start;
print "Start:   {$start}\n";
print "End:     {$end}\n";
print "Elapsed: {$el}\n";
unlink(lockfile);


function scanHost($h) {
	global $tenths;
	$interfaces = array();
	$modems = array();
	$tenths = false;
	$data = snmprealwalk($h,community,interfaceNames);
	foreach($data as $k=>$v) {
		$v = preg_replace("/^STRING: /","",$v);
		$pieces = preg_split("/\./",$k);
		$k = $pieces[count($pieces)-1];
		$interfaces[$k]=$v;
	}
	$data = snmprealwalk($h,community,upstreams);
	$total=0;
	$count=0;
	foreach($data as $k=>$v) {
		$num=preg_replace("/^INTEGER: /","",$v);
		if($num > 0) {
			$total+=$num;
			$count++;;
		}
	}
	if($count >0) {
		if($total/$count > 100) {
			$bondedSnr = $total/$count/10;
			$tenths = true;
		} else {
			$bondedSnr = $total/$count/10;
		}
	} else {
		$bondedSnr = 0;
	}
	$data = snmprealwalk($h,community,macListing);
	foreach($data as $k=>$v) {
		$index = getIndex($k);
		$modems[$index]['index']= $index;
		$modems[$index]['mac']  = getMac($v);
		$modems[$index]['revsnr'] = $bondedSnr;
	}
	$data = snmprealwalk($h,community,modemDownChannel);
	foreach($data as $k=>$v) {
		$dat = preg_split("/\./",$k);
		$k = $dat[count($dat)-1];
		$v = preg_replace("/^INTEGER: /","",$v);
		$modems[$k]['dsIndex']=$v;
		$modems[$k]['dsIfName']=$interfaces[$v];
	}
	$data = snmprealwalk($h,community,modemUpChannel);
	foreach($data as $k=>$v) {
		$dat = preg_split("/\./",$k);
		$k = $dat[count($dat)-1];
		$v = preg_replace("/^INTEGER: /","",$v);
		$modems[$k]['usIndex']=$v;
		$modems[$k]['usIfName']=$interfaces[$v];
	}
	$data = snmprealwalk($h,community,ipListing);
	foreach($data as $k=>$v) {
		$index = getIndex($k);
		$modems[$index]['ip'] = getIp($v);
	}
	$data = snmprealwalk($h,community,revRx);
	foreach($data as $k=>$v) {
		$index = getIndex($k);
		$modems[$index]['revrx'] = revRx($v);
	}
	$data = snmprealwalk($h,community,revSnr);
	foreach($data as $k=>$v) {
		$index = getIndex($k);
		$modems[$index]['revsnr'] = revSnr($v);
		if($modems[$index]['revsnr'] > 100) {
			$modems[$index]['revsnr']/=10;
		}
	}
	$data = snmprealwalk($h,community,cmStatus);
	foreach($data as $k=>$v) {
		$index = getIndex($k);
		$modems[$index]['cmStatus'] = $v;
	}

	foreach($modems as $m ){
		print "Scanning: {$m['mac']} / {$m['ip']}\n";
		if(preg_match('/6/',$m['cmStatus'])) {
			$index=$m['index'];
			$localFwdRx=snmpModem($m['ip'],modemRx);
			if($localFwdRx != false) {
				$modems[$index]['fwdrx']=$localFwdRx;
				$modems[$index]['fwdsnr']=snmpModem($m['ip'],fwdSnr);
				$modems[$index]['revtx']=snmpModem($m['ip'],revTx);
				saveRRD($modems[$index]);
				saveDB($modems[$index]);
			}
		}
	}
}

function saveDB($m) {
	$mac = $m['mac'];
	if(!isset($m['revrx'])) { $m['revrx']=NULL; }
	$sql="INSERT INTO modem_history VALUES ('{$m['mac']}','{$m['fwdrx']}','{$m['fwdsnr']}','{$m['revtx']}','{$m['revrx']}','{$m['revsnr']}','{$m['ip']}',now(),NULL,NULL,now()) ON DUPLICATE KEY UPDATE fwdrx='{$m['fwdrx']}', fwdsnr='{$m['fwdsnr']}', revtx='{$m['revtx']}', revrx='{$m['revrx']}', revsnr='{$m['revsnr']}', time=now(), ip='{$m['ip']}'";
	//$sql="INSERT INTO modem_history VALUES ('{$m['mac']}','{$m['fwdrx']}','{$m['fwdsnr']}','{$m['revtx']}','{$m['revrx']}','{$m['revsnr']}',now()) ON DUPLICATE KEY UPDATE fwdrx='{$m['fwdrx']}', fwdsnr='{$m['fwdsnr']}', revtx='{$m['revtx']}', revrx='{$m['revrx']}', revsnr='{$m['revsnr']}', time=now()";
	$c = connect();
	$c->query($sql);
}

function saveRRD($m) {
	$modemDef=array("--step","300",
		"DS:fwdrx:GAUGE:600:-50:65",
		"DS:fwdsnr:GAUGE:600:0:65",
		"DS:revtx:GAUGE:600:0:65",
		"DS:revrx:GAUGE:600:-30:65",
		"DS:revsnr:GAUGE:600:0:65",
		"RRA:AVERAGE:0.5:1:8640",
		"RRA:MIN:0.5:1:8640",
		"RRA:MAX:0.5:1:8640",
		"RRA:AVERAGE:0.5:6:4320",
		"RRA:MIN:0.5:6:4320",
		"RRA:MAX:0.5:6:4320"
	);
	$rrdFile = rrdDir.$m['mac'].".rrd";
	if(!file_exists($rrdFile)) {
		print "Creating {$rrdFile}\n";
		$cmd = rrdTool." create ".$rrdFile." ".implode(" ",$modemDef);
		system($cmd);
	}
	if(!isset($m['revrx'])) {
		$m['revrx']='U';
	}
	$cmd = rrdTool." update {$rrdFile} N:{$m['fwdrx']}:{$m['fwdsnr']}:{$m['revtx']}:{$m['revrx']}:{$m['revsnr']}";
	system($cmd);
	
}
function snmpModem($ip,$oid) {
	global $tenths;
	$total=0;
	$count=0;
	$data = snmprealwalk($ip,community,$oid,1000000,1);
	if(!is_array($data)) {
		return false;
	}
	foreach($data as $k=>$v) {
		$v = preg_replace("/^INTEGER: /","",$v);
		$total+=$v;
		$count++;
	}
	if($tenths) {
		$total/=10;
	}
	if($count >0) {
		return sprintf("%.1f",$total/$count);
	} else {
		return 0;
	}
}
function cmStatus($dat) {
	// INTEGER: registrationComplete(6)
	$dat = preg_replace("/^INTEGER: /","",$dat);
	return $dat;
}
function revSnr($dat) {
	// INTEGER: 36.1 dB
	$dat = preg_replace("/^INTEGER: /","",$dat);
	$dat = preg_replace("/ dB$/","",$dat);
	return $dat;
}
function revRx($dat) {
	// INTEGER: .0 dBmV
	$dat = preg_replace("/^INTEGER: /","",$dat);
	$dat = preg_replace("/ dBmV$/","",$dat);
	$dat /= 10;
	return $dat;
}
function getIp($dat) {
	$dat=preg_replace("/^IpAddress: /","",$dat);
	return $dat;
}
function getMac($dat) {
	if(preg_match("/^STRING: /",$dat)) {
		$dat=preg_replace("/^STRING: /","",$dat);
		$octets = preg_split('/:/',$dat);
		$rv='';
		while(count($octets) < 6) {
			array_unshift($octets,0);
		}
		foreach($octets as $oct) {
			$oct=hexdec($oct);
			$rv.=sprintf("%02X",$oct);
		}
		return $rv;
	} else {
		$dat = preg_replace("/^Hex-STRING: /","",$dat);
		$dat = preg_replace("/ /","",$dat);
		return $dat;
	}
}
function getIndex($dat) {
	preg_match('/\d+$/',$dat,$match);
	return $match[0];
}

?>
