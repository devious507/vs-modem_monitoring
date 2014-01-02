<?php
require_once("config.php");
checkSuper();

if(isset($_GET['freq'])) {
	// find first unused cfg_id, need to do a query to the DB
	$c_id   =DS_FREQ_START;
	$bot = DS_FREQ_START - 1;
	$top = DS_FREQ_END + 1;
	$sql="SELECT cfg_id FROM config_modem WHERE cfg_id > {$bot} AND cfg_id < {$top} ORDER BY cfg_id";
	$conn = connect();
	$rset = $conn->query($sql);
	if(PEAR::isError($rset)) {
		$body="<p>SQL ERROR:</p>";
		$body.=$rset->getMessage();
		buildPage($body,$sql);
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


	$f      =$_GET['freq'];
	$comment ="Down {$f} Mhz";
	$cfg_txt ="DownstreamFrequency {$f}000000;";
	$sql="INSERT INTO config_modem (cfg_id,comment,cfg_txt,cfg_ver,cfg_update) VALUES ('{$c_id}','{$comment}','{$cfg_txt}','any',Now())";
	$conn=connect();
	$rset = $conn->query($sql);
	if(PEAR::isError($rset)) {
		buildPage($rset->getMessage());
		exit();
	}
	header("Location: modem_configs.php");
	exit();
}

$body.="<form method=\"get\" action=\"dsFrequency.php\">\n";
$body.="<table width=\"300\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
$body.="<tr><td>DS Frequency MHZ</td><td><input type=\"text\" size=\"4\" name=\"freq\"></td></tr>\n";
$body.="<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Add DS Frequency\"></td></tr>\n";
$body.="</table>\n";
$body.="</form>\n";

buildPage($body);
?>
