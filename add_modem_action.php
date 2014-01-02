<?php

require_once("config.php");

if(!isset($_POST['modem_macaddr'])) {
	buildPage("Modem not defined, aborting!");
	exit();
} else {
	$_POST['modem_macaddr'] = strtoupper(preg_replace('/[^A-Fa-f0-9]/', '', $_POST['modem_macaddr']));
}
if( (!isset($_POST['serialnum'])) || ($_POST['serialnum'] == '') ) {
	$_POST['serialnum']=$_POST['modem_macaddr'];
}

if(strlen($_POST['modem_macaddr'])!=12) {
	buildPage('Modem Mac Address must be <b>exactly</b> 12 characters long<br><a href="add_modem.php">Add Another Modem</a>');
	exit();
}

foreach($_POST as $k=>$v) {
	switch($k) {
	case "networkaccess":
	case "ips":
	case "down":
	case "up":
	case "downfreq":
	case "upchan":
	case "bpi":
	case "snmp":
	case "docsis2Enable":
	case "docsis1":
	case "cvc":
	case "configServer":
	case "firmwareUpgrade":
		if($v != '') {
			$aaa[]=$v;
		}
		break;
	default:
		$ks[]=$k;
		$vs[]="'".$v."'";
		break;
	}
}

asort($aaa);
$ks[]='dynamic_config_file';
$vs[]="'".implode(",",$aaa)."'";

$kline=implode(",",$ks);
$vline=implode(",",$vs);

$sql="INSERT INTO docsis_modem ({$kline}) VALUES ({$vline})";
$conn=connect();
$rset = $conn->query($sql);
if(PEAR::isError($rset)) {
	buildPage($rset->getMessage(),$sql);
	exit();
} else {
	header("Location: modem.php");
	exit();
}
?>
