<?php

require_once("config.php");
require_once("function_getModemInfo.php");

$cmts_list=array(
	"Des Moines"=>"38.108.136.1",
	"Minot"=>"208.107.3.58"
);
$cmts_task = array(
	'show cable modem | include $mac\nshow int $cable modem $modem' => 'Show Cable Modem Host',
	'show cable modem | include $mac' => 'Find Modem',
	'clear cable modem $mac reset' => 'Reset Modem',
	'clear cable host $mac' => 'Reset Host IP behind Modem',
	'show cable qos profile' => 'Show all Speed IDs',
	'show cable qos profile\nshow cable modem $mac verbose | incl QoS' => 'Show Speed ID',
	'sh cable modem $ip\nsh cable modem remote | incl $ip' => 'Show Cable Modem Remote',
	'sh cable modem $mac flap' => 'Show Cable Modem Flaps',
	'sh cable modem $mac conn' => 'Show Cable Modem Connectivity',
	'sh cable modem $ip verbose' => 'Show Cable Modem Verbose',
	'show run | incl power-level' => 'Show Target',
	'show run' => 'Show Running Config',
	'show contr cable 3/0 | incl SNR' => 'Show Cable 3 SNR',
	'show contr cable 4/0 | incl SNR' => 'Show Cable 4 SNR',
	'show ip nat trans' => 'Show IP Nat Translations'
);

$mac="";
if(isset($_GET['mac'])) {
	$mac=$_GET['mac'];
	$mac=formatMacCisco($mac);
	if(!isset($_GET['cmts'])) {
		foreach($cmts_list as $k=>$v) {
			if(!isset($cmts)) {
				$cmts=$v;
			}
		}
		header("Location: /monitoring/cmtsTool.php?mac={$mac}&cmts={$cmts}");
		exit();
	} else {
		$cmts=$_GET['cmts'];
	}
	$ip_addr='';
	$cable_iface='';
	$modem_id='';
	if(isset($_GET['task'])) {
		print "<pre>";
		var_dump($_GET);
		exit();
	}
	$task='show cable modem | include $mac\nshow int $cable modem $modem';
	$data=get_modem_info($mac,$cmts,$task);
} else {
	$mac=$_POST['mac'];
	$cmts=$_POST['cmts'];
	$data=get_modem_info($mac,$cmts,$_POST['task']);
}
$doTask=$data['task'];
$sql='';
if(isset($_POST['task'])) {
	$myTask=$_POST['task'];
} else {
	$myTask='';
}
if(isset($_POST['cmts'])) {
	$myCMTS=$_POST['cmts'];
} else {
	$myCMTS='';
}


$sysMac=$mac;
$sysMac=strtoupper(preg_replace("/(\.|:|-)/","",$sysMac));
$backLink="<a href=\"/monitoring/modemHistory.php?mac={$sysMac}\">Modem History</a>";
$top25Link="<a href=\"http://dashboard.visionsystems.tv/noc/getTop25Modems.php\">Biggest Data Users</a>";
$body="<form method=\"post\" action=\"/monitoring/cmtsTool.php\">\n";
$body.="<table cellpadding=\"3\" cellspacing=\"0\" border=\"1\" width=\"80%\">\n";
$body.="\t<tr><td>CMTS</td><td>".make_cmts_list($cmts_list,$myCMTS)."</td><td>CMTS Task</td><td>".make_task_list($cmts_task,$myTask)."</td></tr>\n";
$body.="\t<tr><td>Mac Addr:</td><td><input type=\"text\" name=\"mac\" value=\"{$data['mac']}\"></td><td>IP Address</td><td><input type=\"text\" name=\"ip_address\" value=\"{$data['ip_addr']}\"></td></tr>\n";
$body.="\t<tr><td>Cable Iface</td><td><input type=\"text\" name=\"cable_iface\" value=\"{$data['cable_iface']}\"></td><td>Modem ID</td><td><input type=\"text\" name=\"modem_id\" value=\"{$data['modem_id']}\"></td></tr>\n";
$body.="\t<tr><td colspan=\"4\"><input type=\"submit\" value=\"Update\"> {$backLink} | {$top25Link} </td></tr>\n";
if(isset($doTask)) {
	$result= connectCMTS($cmts, $doTask);
	$ar=preg_split("/\n/",$result);
	$result='';
	foreach($ar as $line) {
		if(preg_match("/0090\.f8..\...../",$line)) {
			print $line."<br>\n";
			print preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/",$line,$tResults);
			$tIP=$tResults[0];
			$url="<a href=\"http://dashboard.visionsystems.tv/ata/index.php?ataIP={$tIP}\">{$tIP}</a>";
			$pat="/".preg_replace("/\./","\.",$tIP)."/";
			$line=preg_replace($pat,$url,$line);
		}
		$result.=$line."\n";
	}
	if($_POST['task'] == 'show cable modem | include $mac\nshow int $cable modem $modem') {
		$body.="\t<tr><td colspan=\"4\"><pre>{$result}</pre></td></tr>";
	} else {
		$body.="\t<tr><td colspan=\"4\"><pre>{$result}</pre></td></tr>";
		//$body.="\t<tr><td colspan=\"4\"><textarea rows=\"20\" cols=\"100\" readonly=\"readonly\">{$result}</textarea></td></tr>";
	}
}


/*
$body.="<tr><td colspan=\"4\">Move Modem to New Base Frequency</a></td></tr>\n";
$body.=freqBlock($mac,$cmts,array(111,117,123,129),"C8/0/0:");
$body.=freqBlock($mac,$cmts,array(141,147,153,159),"C8/0/1:");
$body.=freqBlock($mac,$cmts,array(873,879,885,891),"C8/1/0:");
$body.=freqBlock($mac,$cmts,array(897,903,909,915),"C8/1/1:");
 */

$body.="</table>\n";
$body.="</form>\n";


buildPage($body,$sql);



function freqBlock($mac,$cmts,$freqs,$base) {
	$rv="<tr>";
	$count=0;
	foreach($freqs as $f) {
		$if="&nbsp;(".$base.$count.")&nbsp;";
		$rv.="<td><a href=\"monitoring/moveModem.php?mac={$mac}&cmts={$cmts}&freq={$f}000000\">{$f}MHz</a>{$if}</td>";
		$count++;
	}
	$rv.="</tr>\n";
	return $rv;
}

function formatMacCisco($mac) {
	$mac=strtolower($mac);
	$mac=preg_replace("/\./","",$mac);
	$mac=preg_replace("/:/","",$mac);
	$mac=preg_replace("/-/","",$mac);
	$set[0]=substr($mac,0,4);
	$set[1]=substr($mac,4,4);
	$set[2]=substr($mac,8,4);
	$mac=join(".",$set);
	return $mac;
}
function make_task_list($tasks,$myTask) {
	//print $myTask; exit();
	$rv="<select name=\"task\">";
	foreach($tasks as $k=>$v) {
		if($k == $myTask) {
			$rv.="<option value=\"{$k}\" selected=\"selected\">{$v}</option>";
		} else {
			$rv.="<option value=\"{$k}\">{$v}</option>";
		}
	}
	$rv.="</select>";
	return $rv;
}
function make_cmts_list($cmts,$myCMTS) {
	$rv="<select name=\"cmts\">";
	foreach($cmts as $k=>$v) {
		if($v == $myCMTS) {
			$rv.="<option value=\"{$v}\" selected=\"selected\">{$k}</option>";
		} else {
			$rv.="<option value=\"{$v}\">{$k}</option>";
		}
	}
	$rv.="</select>";
	return $rv;
}
?>
