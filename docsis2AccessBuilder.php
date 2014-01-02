<?php
require_once("config.php");
checkSuper();
if(isset($_POST['docsis2'])) {
	$c_id = DOCSIS2_START;
	$bot = DOCSIS2_START - 1;
	$top = DOCSIS2_END + 1;
	$docsis2 = $_POST['docsis2'];
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

	if($docsis2 == 0) {
		$comment = "Docsis 2 Disable ({$c_id})";
		$cfg_txt = 'DocsisTwoEnable 0;';
	} else {
		$comment = "Docsis 2 Enable ({$c_id})";
		$cfg_txt = 'DocsisTwoEnable 1;';

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

	$body ="<form method=\"post\" action=\"docsis2AccessBuilder.php\">\n";
	$body.="<table width=\"300\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
	$body.="<tr><td>Docsis2 Access</td><td><select name=\"docsis2\"><option value=\"1\">Yes</option><option value=\"0\">No</option></select></td></tr>\n";
	$body.="<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Submit\"></td></tr>\n";
	$body.="</table>\n";
	$body.="</form>\n";
	buildPage($body);
?>
