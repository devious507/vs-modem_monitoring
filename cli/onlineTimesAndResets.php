<?

$_COOKIE['username']='admin';
$_COOKIE['password']='docsis';

require_once("../config.php");
$oids=getOids();

$db=connect();
$sql="SELECT ip FROM research1";
$rset=$db->query($sql);
if(PEAR::isError($rset)) {
	print $sql."<BR>\n";
	print $rset->getMessage()."<br>\n";
	exit();
}
while(($row=$rset->fetchRow())==true) {
	$ips[]=$row['ip'];
}



foreach($ips as $ip) {
	$last_power=@snmpget($ip,'public',$oids['last_powercycle']);
	if($last_power != '') {
		$tmp=preg_split("/:/",$last_power);
		array_shift($tmp);
		$last_power=implode(":",$tmp);
		$tmp=preg_split("/\)/",$last_power);
		$last_power=preg_replace("/,/",' ',trim($tmp[1]));
		$last_power=preg_replace("/days/",'d',$last_power);
	}

	$resets=@snmpget($ip,'public',$oids['modem_resets']);
	if($resets != '') {
		$tmp=preg_split("/:/",$resets);
		$resets=trim($tmp[1]);
	}
	if(($last_power != '') AND ($resets !='')) {
		$sql=sprintf("UPDATE research1 SET uptime='%s', resets=%d WHERE ip='%s'",$last_power,$resets,$ip);
		print $sql."\n";
		$rset2=$db->query($sql);
	}
}


function getOids() {
	$oids['modem_resets']                           ='.1.3.6.1.2.1.10.127.1.2.2.1.4.2';
	$oids['last_powercycle']                        ='.1.3.6.1.2.1.1.3.0';
	return $oids;
}
?>
