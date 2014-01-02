<?php

require_once("config.php");

checkSuper();
if( (!isset($_POST['server_id'])) || (!isset($_POST['opt_id'])) ) {
	$error=serialize($_POST);
	buildPage("Error with Variables",$error);
	exit();
}
$s_id=$_POST['server_id'];
$o_id=$_POST['opt_id'];
$o_type=$_POST['opt_type'];
unset($_POST['server_id']);
unset($_POST['opt_id']);
unset($_POST['opt_type']);


foreach($_POST as $k=>$v) {
	switch($k) {
	case "sub_opt":
	case "opt_type":
		$parts[]=$k."=".$v;
		break;
	default:
		$parts[]=$k."='".$v."'";
		break;
	}
}
$sql="UPDATE config_opts SET ".implode(", ",$parts)." WHERE server_id={$s_id} AND opt_id={$o_id} AND opt_type={$o_type}";
$conn = connect();
$rset = $conn->query($sql);
header("Location: dhcp_options.php");
?>
