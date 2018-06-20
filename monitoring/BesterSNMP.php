<?php
//mysql> select ip from modem_history WHERE mac='0017EE46C52A';

require_once("config.php");
	//define('PATH','/var/www/monitoring/mibs/');
	define("PATH","/usr/share/snmp/mibs/");
	define('LOGPATH','.1.3.6.1.2.1.69.1.5.8.1.7');
	$ipaddr='';
	if(isset($_GET['mac'])) {
		$db = connect();;
		if(PEAR::isError($db)) {
			print "Error connecting to db reveting to manual IP entry!<br>\n";
		} else {
			$sql="SELECT ip FROM modem_history WHERE mac='{$_GET['mac']}'";
			$rSet=$db->query($sql);
			if(PEAR::isError($rSet)) {
				print "Query Failure: Reverting to manual IP entry!<br>\n";
			} else {
				$row=$rSet->fetchRow();
				$ipaddr=$row['ip'];
				$loc=$_SERVER['PHP_SELF']."?ip=".$ipaddr;
				header("Location: {$loc}");
				exit();
			}
		}
	}
?>
<html>
<head>
<title>Modem Information Readout</title>
</head>
<body>
<p>Processing...</p>
<?php
	flush();
	if(isset($_GET['ip'])) {
		$ipaddr=$_GET['ip'];
	} else {
		$self=$_SERVER['PHP_SELF'];
		print "<html><head><title>Enter Ip Address</title></head><body><form method=\"{$self}\">";
		print "<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\"><tr><td>IP Address to Query</td><td><input type=\"text\" name=\"ip\" size=\"20\"></td></tr>";
		print "<tr><td colspan=\"2\"><input type=\"submit\" value=\"submit\"></td></tr></table></form></body></html>";
		exit();
	}
	loadMibs();
	$oids=getOids();
	if(isset($_GET['log']) && ($_GET['log'] == 'true')) {
		$oidLog=snmpwalk($ipaddr,'public',LOGPATH);
		$logfile="<tr><td colspan=\"6\">";
		foreach($oidLog as $o) {
			$logfile.="{$o}<br>";
		}
		$logfile.="</td></tr>\n";
	} else {
		$logfile="<tr><td colspan=\"6\"><a href=\"{$_SERVER['PHP_SELF']}?ip={$ipaddr}&amp;log=true\">Reveal Logs</a></td></tr>\n";
	}
	foreach($oids as $k=>$v) {
		if(($k != 'modemmac') && ($k != 'modem_resets') && ($k != 'last_powercycle')) {
			$valT = snmpwalk($ipaddr,'public',$v);
			if(isset($valT[0])) {
				$val=$valT[0];
			} else {
				$val='';
			}
		} else {
			$val = snmpget($ipaddr,'public',$v);
		}
		print $k." ".$val."<br>\n";
		$vv=explode(':',$val);
		switch($k) {
		case "fwdsnr":
			$vals[$k]=sprintf("%.1f dB",$vv[1]/10);
			break;
		case "fwdrx":
			$vals[$k]=sprintf("%.1f dBmV",$vv[1]/10);
			break;
		case "modem_resets":
			$vals[$k]=$vv[1];
			break;
		case "last_powercycle":
			array_shift($vv);
			$myVal=implode(":",$vv);
			$vv=preg_split("/\)/",$myVal);
			unset($myVal);
			$vals[$k]=$vv[1];
			break;
		case "downstreamfrequency":
			$vals[$k]=hzToMhz(trim($vv[1]));
			break;
		case "downstreamwidth":
			$vals[$k]=hzToMhz(trim($vv[1]));
			break;
		case "upstreamfrequency":
			$vals[$k]=hzToMhz(trim($vv[1]));
			break;
		case "upstreamwidth":
			$vals[$k]=hzToMhz(trim($vv[1]));
			break;
		case "downstreammodulation":
			$vals[$k]=stripResult(trim($vv[1]));
			break;
		case "downstreamannex":
			switch(trim($vv[1])) {
			case "3":
				$vals[$k]='annexA';
				break;
			case "4":
				$vals[$k]='annexB';
				break;
			case "5":
				$vals[$k]='annexC';
				break;
			}
			break;
		case "downstreaminterleave":
			$vals[$k]=stripResult(trim($vv[1]));
			break;
		case "downstreambandwidth":
			//$vals[$k]=bytesToMbytes(trim($vv[1]));
			break;
		case "upstreambandwidth":
			//$vals[$k]=bytesToMbytes(trim($vv[1]));
			break;
		case "upstreambandwidthG":
			//$vals[$k]=bytesToMbytes(trim($vv[1]));
			break;
		case "modemmac":
			$vals[$k]=makeMacString(trim($vv[1]));
			break;
		default:
			$vals[$k]=trim($vv[1]);
			break;
		}
	}
	//print "<pre>"; var_dump($vals); print "</pre>";

	function makeMacString($string) {
		$t=explode(' ',$string);
		$string=implode(':',$t);
		return $string;
	}

	function stripResult($string) {
		$t=preg_split('/\\(/',$string);
		return $t[0];
	}

	function bytesToMbytes($val) {
		$val/=1024;
		$val/=1000;
		return $val;
	}
	function hzToMhz($string) {
		$t=preg_split('/ /',$string);
		$t[0]/=1000;
		$t[0]/=1000;
		return $t[0]." Mhz";
	}
	function loadMibs() {
		if(is_dir(PATH)) {
			if($dh = opendir(PATH)) {
				while(($file = readdir($dh)) != false) {
					if( ($file != '.') && ($file != '..') ) {
						//print PATH.$file." ";
						//print snmp_read_mib(PATH.$file);
						snmp_read_mib(PATH.$file);
						//print "<br>\n";
					} 
				}
			} else {
				print "Unable to open ".PATH."\n";
			}
		} else {
			print PATH." is not a directory\n";
		}
		//snmp_set_enum_print(1);
	}
	function getOids() {
		$oids['modem_resets']				='.1.3.6.1.2.1.10.127.1.2.2.1.4.2';
		$oids['last_powercycle']			='.1.3.6.1.2.1.1.3.0';
		$oids['fwdsnr']					='DOCS-IF-MIB::docsIfSigQSignalNoise';
		$oids['fwdsnr']					='.1.3.6.1.2.1.10.127.1.1.4.1.5';
		$oids['fwdrx']					='.1.3.6.1.2.1.10.127.1.1.1.1.6';
		$oids['revtx']					='.1.3.6.1.2.1.10.127.1.2.2.1.3';
		//$oids['docsismode']				='.1.3.6.1.2.1.10.127.1.2.2.1.15.2';

		$oids['downstreamchannelid']			='.1.3.6.1.2.1.10.127.1.1.1.1.1';
		$oids['downstreamfrequency']			='.1.3.6.1.2.1.10.127.1.1.1.1.2';
		$oids['downstreamwidth']			='.1.3.6.1.2.1.10.127.1.1.1.1.3';
		$oids['downstreammodulation']			='.1.3.6.1.2.1.10.127.1.1.1.1.4';
		$oids['downstreaminterleave']			='.1.3.6.1.2.1.10.127.1.1.1.1.5';
		$oids['downstreamannex']			='.1.3.6.1.2.1.10.127.1.1.1.1.7';
		$oids['downstreambandwidth']			='.1.3.6.1.2.1.10.127.1.1.3.1.5';

		$oids['upstreamchannelid']			='.1.3.6.1.2.1.10.127.1.1.2.1.1';
		$oids['upstreamfrequency']			='.1.3.6.1.2.1.10.127.1.1.2.1.2';
		$oids['upstreamwidth']				='.1.3.6.1.2.1.10.127.1.1.2.1.3';
		$oids['upstreambandwidth']			='.1.3.6.1.2.1.10.127.1.1.3.1.3';
		$oids['upstreambandwidthG']			='.1.3.6.1.2.1.10.127.1.1.3.1.4';

		$oids['modemmac']				='.1.3.6.1.2.1.17.1.1.0';
		return $oids;
	}


?>
<table cellpadding="5" cellspacing="0" border="1">
<tr><td colspan="6"><a href="<?php echo $_SERVER['PHP_SELF']; ?>">New Ip Address</a>&nbsp;&nbsp;&nbsp;<a href="/modem/upmodem.php?mac=<?php echo $vals['modemmac']; ?>">Upmodem</a></td></tr>
<tr><td colspan="3">Modem: <b><?php echo $vals['modemmac']; ?></b></td><td colspan="3">Ip Address <b><?php echo $ipaddr; ?></b></td></tr>
<tr><td colspan="3">Last Powercycle</td><td colspan="3"><?php echo $vals['last_powercycle'];?></td></tr>
<tr><td colspan="3"># of Resets</td><td colspan="3"><?php echo $vals['modem_resets'];?></td></tr>
<tr><td colspan="6"><b>Downstream</b></td></tr>
<tr>
	<td>SNR</td><td><?php echo $vals['fwdsnr']; ?></td>
	<td>Rx Level</td><td><?php echo $vals['fwdrx']; ?></td>
	<td>Channel ID</td><td><?php echo $vals['downstreamchannelid']; ?></td>
</tr>
<tr>
	<td>Frequency</td><td><?php echo $vals['downstreamfrequency']; ?></td>
	<td>Width</td><td><?php echo $vals['downstreamwidth']; ?></td>
	<td>Modulation</td><td><?php echo $vals['downstreammodulation']; ?></td>
</tr>
<tr>
	<td>Annex</td><td><?php echo $vals['downstreamannex']; ?></td>
	<td>Interleave</td><td colspan="3"><?php echo $vals['downstreaminterleave']; ?></td>
</tr>

<tr><td colspan="6"><b>Upstream</b></td></tr>
<tr>
	<td>Channel ID</td><td><?php echo $vals['upstreamchannelid']; ?></td>
	<td>Frequency</td><td colspan="1"><?php echo $vals['upstreamfrequency']; ?></td>
	<td>Width</td><td colspan="1"><?php echo $vals['upstreamwidth']; ?></td>
</tr>
<tr>
	<td>Rev TX</td><td colspan="5"><?php echo $vals['revtx']; ?></td>
</tr>

<tr><td colspan="6">&nbsp;</td></tr>
<tr><td colspan="6"><b>Logfile</b></td></tr>
<?php echo $logfile; ?>
</table>
</body>
</head>
</html>
