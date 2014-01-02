<?php

require_once("config.php");
checkSuper();

if(!isset($_POST['cfg_id'])) {
	buildPage("<p>Problem with options, aborting");
	exit();
}

$cfg_id=$_POST['cfg_id'];
unset($_POST['cfg_id']);
foreach($_POST as $k=>$v) {
	if($k == 'cfg_update') {
		$parts[]="{$k}=Now()";
	} elseif($v == '') {
		$parts[]="{$k}=NULL";
	} else {
		$parts[]="{$k}='{$v}'";
	}
}

$line=implode(",",$parts);
$sql="UPDATE config_modem SET {$line} WHERE cfg_id={$cfg_id}";
$conn = connect();
$rset = $conn->query($sql);
if(PEAR::isError($rset)) {
	$body="<p>SQL Error:<br>";
	$body.=$rset->getMessage();
	$body.="</p>";
	buildPage($body);
	exit();
}
header("Location: modem_configs.php");
?>
