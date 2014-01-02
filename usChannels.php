<?php
require_once("config.php");
checkSuper();

if(isset($_GET['channel'])) {
	//(401,'Upstream ID 1','UpstreamChannelId 1;'),
	// find first unused cfg_id, need to do a query to the DB
	$c_id  = US_CHAN_START;
	$bot   = US_CHAN_START - 1;
	$top   = US_CHAN_END + 1;
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


	$f      =$_GET['channel'];
	$comment ="Upstream ID {$f}";
	$cfg_txt ="UpstreamChannelId {$f};";
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

$body.="<form method=\"get\" action=\"usChannels.php\">\n";
$body.="<table width=\"300\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
$body.="<tr><td>US Channel Id</td><td><input type=\"text\" size=\"2\" name=\"channel\"></td></tr>\n";
$body.="<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Add US Channel Id\"></td></tr>\n";
$body.="</table>\n";
$body.="</form>\n";

buildPage($body);
?>
