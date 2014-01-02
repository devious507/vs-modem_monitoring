<?php

require_once("../../config.php");
$action="listByAddress.php";

if(isset($_GET['lessthan']) AND $_GET['lessthan'] == '')
	unset($_GET['lessthan']);

if(isset($_GET['greaterthan']) AND $_GET['greaterthan'] == '') 
	unset($_GET['greaterthan']);

if(!isset($_GET['type']))
	$_SERVER['QUERY_STRING']='';

if(isset($_GET['type']) AND $_GET['type']=='paste') {
	$sql="Pasted Values";
	print "<html><head><title>Export View</title></head><body><p>{$sql}</p><form method=\"post\" action=\"{$action}\"><input type=\"hidden\" name=\"sql\" value=\"{$sql}\"><textarea name=\"pastebin\" rows=\"20\" cols=\"100\">";
	print "</textarea><br><input type=\"submit\" value=\"Submit\"></form></body></html>";
	exit();
}
//exit();

if($_SERVER['QUERY_STRING'] == '') {
	print docType()."\n";
	print '<html><head><title>Generic Level Checker</title></head><body>';
	print '<form method="get" action="genericLevelChecker.php">';
	print '<table cellpadding="5" cellspacing="0" border="1">';
	print '<tr><td>Level Type</td><td><select name="type">';
	print '<option value="fwdrx">Fwd RX</option>';
	print '<option value="fwdsnr">Fwd SNR</option>';
	print '<option value="revtx">Rev TX</option>';
	print '<option value="revrx">Rev RX</option>';
	print '<option value="revsnr">Rev SNR</option>';
	print '</select></td></tr>';
	print '<tr><td>Downstream Group</td><td><select name="downstream">';
	print '<option value="">All</option>';
	print '<option value="H%">High Pointe</option>';
	print '<option value="8%">800 Mhz Frequencies</option>';
	print '<option value="1%">100 Mhz Frequencies</option>';
	print '<tr><td>Less Than</td><td><input type="text" size="3" name="lessthan"></td></tr>';
	print '<tr><td>Greater Than</td><td><input type="text" size="3" name="greaterthan"></td></tr>';
	print '<tr><td colspan="2"><input type="submit" value="Lookup"</td></tr>';
	print '</table>';
	print '</form>';
	print '</body></html>';
	exit();
}
$date = date('Y-m-d h:i:s',time()-3600);
if(isset($_GET['freq']) AND $_GET['freq']=='high') {
	$like='8%';
} elseif(isset($_GET['freq']) AND $_GET['freq']=='low') {
	$like='1%';
} else {
	$like='h%';
}
if(isset($_GET['level'])) {
	$level=$_GET['level'];
} else {
	$level=35;
}
if(isset($_GET['downstream']) AND $_GET['downstream'] != '') {
	if($_GET['downstream'] == '8%') {
		$downstream = "AND (primchannel LIKE '8%' OR primchannel LIKE '9%')";
	} else {
		$downstream = "AND primchannel LIKE '{$_GET['downstream']}'";
	}
} else {
	$downstream = '';
}
if(isset($_GET['lessthan']) AND isset($_GET['greaterthan'])) {
	$less = $_GET['lessthan'];
	$greater = $_GET['greaterthan'];
	$type = $_GET['type'];
	$sql="SELECT * FROM modem_history WHERE time > '{$date}' AND ({$type} < {$less} OR {$type} > {$greater}) {$downstream} ORDER BY {$type}";
} elseif(isset($_GET['greaterthan'])) {
	$greater = $_GET['greaterthan'];
	$type = $_GET['type'];
	$sql="SELECT * FROM modem_history WHERE time > '{$date}' AND {$type} > {$greater} {$downstream} ORDER BY {$type}";
} elseif(isset($_GET['lessthan'])) {
	$type=$_GET['type'];
	$less = $_GET['lessthan'];
	$sql="SELECT * FROM modem_history WHERE time > '{$date}' AND {$type} < {$less} {$downstream} ORDER BY {$type}";
} else {
	header("Location: genericLevelChecker.php");
	exit();
}

/*
 */
$db=connect();
$rset=$db->query($sql);
print "<html><head><title>Export View</title></head><body><p>{$sql}</p><form method=\"post\" action=\"{$action}\"><input type=\"hidden\" name=\"sql\" value=\"{$sql}\"><textarea name=\"pastebin\" rows=\"20\" cols=\"100\">";
while(($row=$rset->fetchRow())==true) {
	$mac[0]=substr($row['mac'],0,4);
	$mac[1]=substr($row['mac'],4,4);
	$mac[2]=substr($row['mac'],8,4);
	$row['mac']=strtolower(implode(".",$mac));
	print implode(" ",$row);
	print "\n";
}
print "</textarea><br><input type=\"submit\" value=\"Submit\"></form></body></html>";
?>
