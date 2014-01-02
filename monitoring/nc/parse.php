<?php
$_COOKIE['username']='csr';
$_COOKIE['password']='csr';


require_once("../../config.php");
require_once("../../functions.php");
$file = '/var/www/monitoring/nc/primaryChannel.log';

$fp=fopen($file,'r');
$block=fread($fp,filesize($file));
fclose($fp);
$lines=preg_split("/\n/",$block);
unset($block);

// 0017.ee46.c558 10.1.1.26      C3/0/U7     online(pt)    45    2   In3/0:0    16
//

$pChan[' ']='Unk';
$pChan['']='Unk';
$pChan['In3/0:0']='111Mhz';
$pChan['In3/0:1']='117Mhz';
$pChan['In3/0:2']='123Mhz';
$pChan['In3/0:3']='129Mhz';
$pChan['In3/1:0']='873Mhz';
$pChan['In3/1:1']='879Mhz';
$pChan['In3/1:2']='885Mhz';
$pChan['In3/1:3']='891Mhz';
$pChan['In4/0:0']='141Mhz';
$pChan['In4/0:1']='147Mhz';
$pChan['In4/0:2']='153Mhz';
$pChan['In4/0:3']='159Mhz';
$pChan['In4/1:0']='897Mhz';
$pChan['In4/1:1']='903Mhz';
$pChan['In4/1:2']='909Mhz';
$pChan['In4/1:3']='915Mhz';
$db=connect();
foreach($lines as $line) {
	$line = squeezeTrim($line);
	$mac= fixMac(getPart($line,0));
	$status = getPart($line,3);
	$pc     = getPart($line,6);
	$interface = getPart($line,2);
	$primChan = $pChan[$pc];
	if(preg_match('/online/',$status)) {
		$sql="UPDATE modem_history SET primchannel='{$primChan}', interface='{$interface}' WHERE mac='{$mac}'";
		$res=$db->query($sql);
		if(PEAR::isError($res)) {
			print $sql."\n";
			print $res->getMessage();
			print "\n";
		}
	}
}
$sql="UPDATE modem_history SET primchannel='HiP' WHERE ip LIKE '172.16.%'";
$db->query($sql);



function fixMac($m) {
	$m=preg_replace("/\./",'',$m);
	$m=preg_replace("/:/",'',$m);
	$m=preg_replace("/-/",'',$m);
	$m=strtoupper($m);
	return $m;
}
function getPart($l,$c) {
	$arr = @preg_split("/ /",$l);
	if(isset($arr[$c])) {
		return $arr[$c];
	} else {
		return NULL;
	}
}
function squeezeTrim($l) {
	$l = preg_replace("/\s+/",' ',$l);
	trim($l);
	return $l;
}
