<?php

require_once("config.php");

if(!isset($_POST['modem_macaddr'])) {
	buildPage("Missing information, aborting");
	exit();
}
if( (!isset($_POST['serialnum'])) || ($_POST['serialnum'] == '') ) {
	        $_POST['serialnum']=$_POST['modem_macaddr'];
}

$mac = $_POST['modem_macaddr'];
unset($_POST['modem_macaddr']);
foreach($_POST as $k=>$v) {
	switch($k) {
	case "networkaccess":
	case "ips":
	case "docsis1":
	case "down":
	case "up":
	case "downfreq":
	case "upchan":
	case "snmp":
	case "docsis2Enable":
	case "configServer":
	case "firmwareUpgrade":
	case "cvc":
	case "bpi":
		if($v!='') {
			$aaa[]=$v;
		}
		break;
	default:
		$vals[]="{$k}='{$v}'";
		break;
	}
}
asort($aaa);
$dyn = implode(",",$aaa);
$vals[]="dynamic_config_file='{$dyn}'";
$sql = "UPDATE docsis_modem SET ".implode(", ",$vals)." WHERE modem_macaddr='{$mac}'";
//print $sql; exit();
$conn = connect();
$rset = $conn->query($sql);
if(PEAR::isError($rset)) {
	$body ="<p>Error with SQL:<br>";
	$body.=$rset->getMessagE();
	buildPage($body,$sql);
	exit();
}

header("Location: modem.php?search=modem_macaddr&value={$mac}");

?>
