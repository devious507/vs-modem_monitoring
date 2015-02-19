<?php

require_once("config.php");

define("LOGIN_PASS","gamester");
define("ENABLE_PASS","renter30");

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
$body="<form method=\"post\" action=\"/monitoring/cmtsTool.php\">\n";
$body.="<table cellpadding=\"3\" cellspacing=\"0\" border=\"1\" width=\"80%\">\n";
$body.="\t<tr><td>CMTS</td><td>".make_cmts_list($cmts_list,$myCMTS)."</td><td>CMTS Task</td><td>".make_task_list($cmts_task,$myTask)."</td></tr>\n";
$body.="\t<tr><td>Mac Addr:</td><td><input type=\"text\" name=\"mac\" value=\"{$data['mac']}\"></td><td>IP Address</td><td><input type=\"text\" name=\"ip_address\" value=\"{$data['ip_addr']}\"></td></tr>\n";
$body.="\t<tr><td>Cable Iface</td><td><input type=\"text\" name=\"cable_iface\" value=\"{$data['cable_iface']}\"></td><td>Modem ID</td><td><input type=\"text\" name=\"modem_id\" value=\"{$data['modem_id']}\"></td></tr>\n";
$body.="\t<tr><td colspan=\"4\"><input type=\"submit\" value=\"Update\"> {$backLink}</td></tr>\n";
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
$body.="</table>\n";
$body.="</form>\n";

buildPage($body,$sql);



function get_modem_info($mac,$cmts,$task) {
	$mac=formatMacCisco($mac);
	$rv['mac']=$mac;
	$cmd="show cable modem {$mac}  verbose | incl (Host Interface|IP Address|Prim Sid)";
	$info=connectCMTS($cmts,$cmd);
	$ar=preg_split("/\n/",$info);
	unset($info);
	foreach($ar as $line) {
		$line=preg_replace("/ +/"," ",$line);
		$line=preg_replace("/\n/","",$line);
		$line=preg_replace("/\r/","",$line);
		if(preg_match("/^IP Address/",$line)) {
			$tmp=preg_split("/:/",$line);
			$rv['ip_addr']=preg_replace("/ /","",$tmp[1]);
		} elseif(preg_match("/^Prim Sid/",$line)) {
			$tmp=preg_split("/:/",$line);
			$rv['modem_id']=preg_replace("/ /","",$tmp[1]);
		} elseif(preg_match("/^Host Interface/",$line)) {
			$tmp=preg_split("/:/",$line);
			$ttmp=preg_split("/\//",$tmp[1]);
			$cable_iface=$ttmp[0]."/".$ttmp[1];
			$rv['cable_iface']=preg_replace("/ /","",$cable_iface);
		}
	}
	$task=preg_replace("/\\\$mac/",$rv['mac'],$task);
	$task=preg_replace("/\\\$cable/",$rv['cable_iface'],$task);
	$task=preg_replace("/\\\$modem/",$rv['modem_id'],$task);
	$task=preg_replace("/\\\$ip/",$rv['ip_addr'],$task);
	$task=preg_replace("/\\\\n/","\n",$task);
	$rv['task']=$task;
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
function connectCMTS($ipaddr, $cmtscmd="", $noerr=false, $debug=0) {
	$loginpw=LOGIN_PASS;
	$enablepw=ENABLE_PASS;

	if (!$cmtscmd) {
		return "";
	}
	if ($debug) {
		echo "<pre>";
	}
	$sfp = fsockopen($ipaddr, 23, $errno, $errstr, 30);
	if (!$sfp) {
		if (!$noerr) {
			echo "ERROR: $errno - $errstr<br />\n";
		} else {
			return "ERROR: $errno - $errstr<br />\n";
		}
	} else {
		$sfpdata = fgets($sfp);//  var_ddump($sfpdata, $debug);
		//    echo "<pre>telnet ";var_dump($sfpdata);echo "</pre>";

		//  Looks like we have to handle Telnet negotiations:
		//      IAC DO = chr(255); chr(253) => IAC WONT = chr(255); chr(252);
		//    $retst = fwrite($sfp, str_replace(chr(253), chr(252), $sfpdata));
		//    echo "<pre>retst ";var_dump($retst);echo "</pre>";
		$sfpdata = fgets($sfp);//  var_ddump($sfpdata, $debug);
		$sfpdata = fgets($sfp);//  var_ddump($sfpdata, $debug);
		$sfpdata = fgets($sfp);//  var_ddump($sfpdata, $debug);
		//    echo "<pre>telnet ";var_dump($sfpdata);echo "</pre>";

		$retst = fwrite($sfp, $loginpw . "\nen\n" . $enablepw . "\nterminal length 0\nterminal width 0\n");
		if ($retst == FALSE) {
			die("SocketError, can't write:1");
		}
		$retst = fwrite($sfp, $cmtscmd);
		if ($retst == FALSE) {
			die("SocketError, can't write:2");
		}
		$sfpdata = fgets($sfp);     // First "Password: " prompt
		var_ddump($sfpdata, $debug);
		$sfpline = explode("\r\n", $sfpdata);
		$sfpdata = $sfpline[0];
		//    echo "<pre>telnet ";var_dump($sfpdata);echo "</pre>";
		$retst = fwrite($sfp, "\nexit\n");
		if ($retst == FALSE) {
			die("SocketError, can't write:3");
		}
		$sfpdata = fgets($sfp);
		var_ddump($sfpdata, $debug);
		do {
			$sfpdata = fgets($sfp);   // Second "Password: " prompt
			var_ddump($sfpdata, $debug);
			$sfpline = explode("\r\n", $sfpdata);
			$sfpdata = $sfpline[0];
			//    echo "<pre>telnet ";var_dump($sfpdata);echo "</pre>";
		} while ($sfpdata != "Password: ");
		$sfpdata = fgets($sfp);  var_ddump($sfpdata, $debug);
		$sfpdata = fgets($sfp);  var_ddump($sfpdata, $debug);
		$sfpdata = "";  $sfpdata2 = ""; $sfpdata3 = "";
		do {
			$sfpdata .= $sfpdata2;
			if ($sfpdata3) {
				$sfpdata2 = $sfpdata3;
			}
		} while ($sfpdata3 = fgets($sfp));
		fclose($sfp);
		if ($debug) {
			echo "</pre>";
		}
		return $sfpdata;
	}
	if ($debug) {
		echo "</pre>";
	}
	return "";
}
function var_ddump($data, $debug=0) {
	if ($debug) {
		var_dump($data);
	}
}

