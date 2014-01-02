<?php
require_once("config.php");
checkSuper();
if(isset($_POST['NetworkAccess'])) {
	$c_id = NETWORK_ACCESS_START;
	$bot = NETWORK_ACCESS_START - 1;
	$top = NETWORK_ACCESS_END + 1;
	$bpi = $_POST['NetworkAccess'];
	$sql="SELECT cfg_id FROM config_modem WHERE cfg_id > {$bot} AND cfg_id < {$top} ORDER BY cfg_id";
	$conn = connect();
	$rset = $conn->query($sql);
	if(PEAR::isError($rset)) {
		buildPage($rset->getMessage(),$sql);
		exit();
	}
	$rows = $rset->numRows();
	if($rows > 0) {
		while(($row=$rset->fetchRow())==true) {
			$dbID = $row['cfg_id'];
			if($dbID == $c_id) {
				$c_id++;
			}
		}
	}

	if($bpi == 0) {
		$comment = "No Network Access({$c_id})";
		$cfg_txt = 'NetworkAccess 0;';
	} else {
		$comment = "Allow Network Access ({$c_id})";
		$cfg_txt = 'NetworkAccess 1;';

	}
	$sql="INSERT INTO config_modem (cfg_id,comment,cfg_txt,cfg_ver,cfg_update) VALUES ({$c_id},'{$comment}','{$cfg_txt}','any',Now())";
	$conn = connect();
	$rset = $conn->query($sql);
	if(PEAR::isError($rset)) {
		buildPage($rset->getMessage(),$sql);
		exit();
	}
	header("Location: modem_configs.php");
	exit();
}

	$body ="<form method=\"post\" action=\"networkAccessBuilder.php\">\n";
	$body.="<table width=\"300\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
	$body.="<tr><td>Network Access</td><td><select name=\"NetworkAccess\"><option value=\"1\">Yes</option><option value=\"0\">No</option></select></td></tr>\n";
	$body.="<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Submit\"></td></tr>\n";
	$body.="</table>\n";
	$body.="</form>\n";
	buildPage($body);
?>
