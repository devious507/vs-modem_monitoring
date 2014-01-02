<?php

require_once("config.php");
checkSuper();

foreach($_POST as $k=>$v) {
	if($v != '') {
		$ks[]=$k;
		$vs[]="'".$v."'";
	} 
}
$kline="(".implode(",",$ks).")";
$vline="(".implode(",",$vs).")";

$sql="INSERT INTO config_opts {$kline} VALUES {$vline}";
//print "<pre>"; var_dump($_POST); exit();
if( (!isset($_POST['server_id'])) || (!isset($_POST['opt_id'])) || ($_POST['server_id'] == '') || ($_POST['opt_id'] == '')  ) {
	buildPage("Incomplete Entry!",$sql);
	exit();
}

$conn = connect();
$rset = $conn->query($sql);
if(PEAR::isError($rset)) {
	$body = "SQL Error:<br>\n";
	$body.= $rset->getMessage();
	buildPage($body,$sql);
	exit();
}
header("Location: dhcp_options.php");

?>
