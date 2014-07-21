<?php

require_once("../config.php");

if((isset($_GET['date'])) && ($_GET['date'] != '')) {
	$date=$_GET['date'];
	$myDate=date("Y-m-d H:i:s",strtotime($date));
} else {
	if(isset($_GET['complaint'])) {
		$temp=preg_split("/\r\n/",$_GET['complaint']);
		foreach($temp as $line) {
			if((preg_match("/^Timestamp:/",$line)) || (preg_match("/^Initial Infringement Timestamp:/",$line))) {
				$line=preg_replace("/^Timestamp:/","",$line);
				$line=preg_replace("/^Initial Infringement Timestamp:/","",$line);
				$line=preg_replace("/ /","",$line);
				$myDate=date("Y-m-d H:i:s",strtotime($line));
			}
		}
	}
	if(!isset($myDate)) {
		$myDate='';
	}
}

if((isset($_GET['ipaddr'])) && ($_GET['ipaddr']!='')){
	$ipaddr=$_GET['ipaddr'];
} else {
	if(isset($_GET['complaint'])) {
		$temp=preg_split("/\r\n/",$_GET['complaint']);
		foreach($temp as $line) {
			if((preg_match("/^IP Address:/",$line)) ||
				(preg_match("/^Infringers IP Address:/",$line)) ||
				(preg_match("/^Unauthorized IP Address:/",$line))
			) {
				$line=preg_replace("/^IP Address:/","",$line);
				$line=preg_replace("/^Infringers IP Address:/","",$line);
				$line=preg_replace("/^Unauthorized IP Address:/","",$line);
				$line=preg_replace("/ /","",$line);
				$ipaddr=$line;
			}
		}
	} else {
		$ipaddr='';
	}
}

if(isset($_GET['subnum'])) {
	$subnum=$_GET['subnum'];
} else {
	$subnum='';
}

if(isset($_GET['complaint'])) {
	$complaint=$_GET['complaint'];
} else {
	$complaint='';
}

$dataTable=renderForm($subnum,$myDate,$ipaddr,$complaint);
if($subnum != '') {
	$url="Subscriber: <a href=\"/modem.php?search=subnum&value={$subnum}\">{$subnum}</a>";
	print '<html><head><title>DMCA Results</title></head><body>';
	print "<pre>";
	print "DMCA Complaint\n";
	print "Date: {$myDate}\n";
	print "IP Address: {$ipaddr}\n";
	print "</pre>{$url}<pre>\n";
	print "\n</pre>\n";
	$sql="select subnum,start_time,end_time,ipaddr from dmca_ip_tracking WHERE start_time < '{$myDate}' AND end_time > '{$myDate}' AND ipaddr='{$ipaddr}' AND subnum='{$subnum}' GROUP BY start_time,end_time,ipaddr";
	print getSqlResults($sql,true);
	print "<pre>\n\n{$complaint}\n\n</pre>\n";
	print "<a href=\"tagEvent.php\">Back</a>";
	print "</body></html>";
	exit();
} else {
	$sql="select macaddr,start_time,end_time,subnum,modem_macaddr,tstamp from dmca_ip_tracking WHERE start_time < '{$myDate}' AND end_time > '{$myDate}' AND ipaddr='{$ipaddr}'";
} 

if(isset($myDate) && ($myDate!='') && isset($ipaddr) && $ipaddr!='') {
	$dataTable.=getSqlResults($sql);
}
buildPage($dataTable,$sql);


function getSqlResults($sql,$header=false) {
	$conn=connect();
	$res=$conn->query($sql);
	if(PEAR::isError($res)) {
		buildPage($res->getMessage());
		exit();
	}
	$rv="<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
	while(($row=$res->fetchRow(MDB2_FETCHMODE_ASSOC))==true) {
		if($header == true) {
			$rv.="<tr>";
			foreach($row as $k=>$v) {
				$rv.="<td>{$k}</td>";
			}
			$header=false;
			$rv.="</tr>\n";
		}
		$rv.="<tr>";
		foreach($row as $k=>$v) {
			$rv.="<td>{$v}</td>";
		}
		$rv.="</tr>\n";
	}
	$rv.="</table>\n";
	return $rv;
}
function renderForm($subnum='',$date='',$ipaddr='',$complaint='') {
	$body="<form method=\"get\" action=\"/dmca/tagEvent.php\">";
	$body.="<table>";
	$body.="<tr><td>Subnum</td><td><input type=\"text\" size=\"10\" name=\"subnum\" value=\"{$subnum}\"></td></tr>";
	$body.="<tr><td>Date:</td><td><input type=\"text\" name=\"date\" value=\"{$date}\"></td></tr>";
	$body.="<tr><td>IP Addess:</td><td><input type=\"text\" name=\"ipaddr\" size=\"21\" value=\"{$ipaddr}\"></tr>";
	$body.="<tr><td colspan=\"2\">Complaint</td></tr>";
	$body.="<tr><td colspan=\"2\"><textarea name=\"complaint\" rows=\"15\" cols=\"75\">{$complaint}</textarea></td></tr>";
	$body.="<tr><td colspan=\"2\"><input type=\"submit\" value=\"Lookup\"></td></tr>";
	$body.="</table></form>";
	return $body;
}
?>
