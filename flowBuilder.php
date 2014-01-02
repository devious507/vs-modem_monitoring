<?php
require_once("config.php");
checkSuper();


if(isset($_GET['direction'])) {
	if($_GET['flowref'] == 'auto') {
		if($_GET['direction'] == 'DS') {
			$bot = DS_FLOW_START - 1;
			$top = DS_FLOW_END + 1;
		} else {
			$bot = US_FLOW_START - 1;
			$top = US_FLOW_END + 1;
		}
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
		if($_GET['direction'] == 'DS')
			$flowref = DS_FLOW_START;
		if($_GET['direction'] == 'US') 
			$flowref = US_FLOW_START;
		if($rows > 0) {
			while(($row=$rset->fetchRow())==true) {
				$dbID = $row['cfg_id'];
				if($dbID == $flowref) {
					$flowref++;
				}
				print "$flowref<br>";
			}
		}
	} else {
		$flowref = $_GET['flowref'];
	}
	$QosParamSetType = $_GET['QosParamSetType'];
	$TrafficPriority = $_GET['TrafficPriority'];
	$MaxRateSustained = $_GET['MaxRateSustained'];
	if($_GET['direction'] == 'US') {
		$multiplier = 1.1;
	} else {
		$multiplier = 1;
	}
	if( preg_match("/.*M$/",$MaxRateSustained) || preg_match("/.*m/",$MaxRateSustained) ) {
		$MaxRateSustained = substr($MaxRateSustained,0,-1)*1024000*$multiplier;
	} elseif( preg_match("/.*K$/",$MaxRateSustained) || preg_match("/.*k/",$MaxRateSustained) ) {
		$MaxRateSustained = substr($MaxRateSustained,0,-1)*1000*$multiplier;
	}
	$MaxTrafficBurst = $_GET['MaxTrafficBurst'];
	$MinReservedRate = $_GET['MinReservedRate'];
	$MaxConcatenatedBurst = $_GET['MaxConcatenatedBurst'];
	if($_GET['direction'] == 'DS') {
		$text = 'DsServiceFlow { DsServiceFlowRef ';
		$text .= "{$flowref}; QosParamSetType {$QosParamSetType}; TrafficPriority {$TrafficPriority}; ";
		$text .= "MaxRateSustained {$MaxRateSustained}; MaxTrafficBurst {$MaxTrafficBurst}; MinReservedRate {$MinReservedRate}; }";
	} else {
		$text = 'UsServiceFlow { UsServiceFlowRef ';
		$text .= "{$flowref}; QosParamSetType {$QosParamSetType}; TrafficPriority {$TrafficPriority}; ";
		$text .= "MaxRateSustained {$MaxRateSustained}; MaxTrafficBurst {$MaxTrafficBurst}; MaxConcatenatedBurst {$MaxConcatenatedBurst}; MinReservedRate {$MinReservedRate}; }";
	}
	$body  = "{$text}<hr><a href=\"add_modem_configs.php?cfg_id={$flowref}&cfg_txt={$text}\">Add Modem Config Stanza</a>";
	buildPage($body);
	exit();
}


$body.="<form method=\"get\" action=\"flowBuilder.php\">\n";
$body.="<table width=\"800\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
$body.="<tr><td>Direction</td><td><select name=\"direction\"><option value=\"US\">Upstream</option><option value=\"DS\">Downstream</option></select></td></tr>\n";
$body.="<tr><td>Flow Reference #</td><td><input type=\"text\" size=\"3\" name=\"flowref\" value=\"auto\"></td></tr>\n";
$body.="<tr><td>QoS Parameter</td><td><input type=\"text\" size=\"3\" name=\"QosParamSetType\" value=\"7\"></td></tr>\n";
$body.="<tr><td>Traffic Priority</td><td><input type=\"text\" size=\"3\" name=\"TrafficPriority\" value=\"1\"></td></tr>\n";
$body.="<tr><td>MaxRateSustained (256K or 1M Acceptable)</td><td><input type=\"text\" size=\"10\" name=\"MaxRateSustained\"></td></tr>\n";
$body.="<tr><td>MaxTrafficBurst (20000 US / 1522 DS)</td><td><input type=\"text\" size=\"10\" value=\"20000\" name=\"MaxTrafficBurst\"></td></tr>\n";
$body.="<tr><td>MaxConcatenatedBurst (20000 US)</td><td><input type=\"text\" size=\"10\" value=\"20000\" name=\"MaxConcatenatedBurst\"></td></tr>\n";
$body.="<tr><td>MinReservedRate</td><td><input type=\"text\" size=\"10\" value=\"0\" name=\"MinReservedRate\"></td></tr>\n";
$body.="<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Generate\"></td></tr>\n";
$body.="</table>\n";
$body.="</form>\n";

buildPage($body);
?>
