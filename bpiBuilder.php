<?php
require_once("config.php");
checkSuper();
if(isset($_POST['GlobalPrivacyEnable'])) {
	$c_id = BPI_START;
	$bot = BPI_START - 1;
	$top = BPI_END + 1;
	$bpi = $_POST['GlobalPrivacyEnable'];
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
		$comment = "No BPI ({$c_id})";
		$cfg_txt = "GlobalPrivacyEnable 0;";
	} else {
		$comment = "Enable BPI ({$c_id})";
		$cfg_txt = "GlobalPrivacyEnable 1; ";
		$cfg_txt.= "BaselinePrivacy { ";
		$cfg_txt.= "AuthTimeout {$_POST['AuthTimeout']}; ";
		$cfg_txt.= "ReAuthTimeout {$_POST['ReAuthTimeout']}; ";
		$cfg_txt.= "AuthGraceTime {$_POST['AuthGraceTime']}; ";
		$cfg_txt.= "OperTimeout {$_POST['OperTimeout']}; ";
		$cfg_txt.= "ReKeyTimeout {$_POST['ReKeyTimeout']}; ";
		$cfg_txt.= "TEKGraceTime {$_POST['TEKGraceTime']}; ";
		$cfg_txt.= "AuthRejectTimeout {$_POST['AuthRejectTimeout']}; }";
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

	$body ="<form method=\"post\" action=\"bpiBuilder.php\">\n";
	$body.="<table width=\"100%\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
	$body.="<tr><td>Enable BPI</td><td><select name=\"GlobalPrivacyEnable\"><option value=\"0\">No</option><option value=\"1\">Yes</option></select></td></tr>\n";
	$body.=entryLine('AuthTimeout','10');
	$body.=entryLine('ReAuthTimeout','10');
	$body.=entryLine('AuthGraceTime','600');
	$body.=entryLine('OperTimeout','10');
	$body.=entryLine('ReKeyTimeout','10');
	$body.=entryLine('TEKGraceTime','600');
	$body.=entryLine('AuthRejectTimeout','60');
	$body.="<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Submit\"></td></tr>\n";
	$body.="</table>\n";
	$body.="</form>\n";
	buildPage($body);
?>
