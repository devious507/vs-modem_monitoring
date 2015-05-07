<?php
define("LOGIN_PASS","gamester");
define("ENABLE_PASS","renter30");

require_once("../../config.php");

if(isset($_GET['property'])) {
	$extra=" AND c.property='{$_GET['property']}'";
} else {
	$extra='';
}

$all_reference_sql=" select d.modem_macaddr,c.name,c.property FROM customer_address AS c LEFT OUTER JOIN docsis_modem AS d ON c.subnum=d.subnum WHERE c.name like '%Refer%' ".$extra." ORDER BY c.property,c.name";

$db = connect();

$rset=$db->query($all_reference_sql);
if(PEAR::isError($rset)) {
	print $rset->getMessage()."<br>\n";
	print $sql."<br>\n";
}

$body="<table cellpadding=\"3\" cellspacing=\"0\" border=\"1\">\n";
while(($row=$rset->fetchRow())==true) {
	if($row['modem_macaddr'] != '') {
		$revsnr=getRevSNR($row['modem_macaddr']);
		$body.="<tr>";
		foreach($row as $k=>$v) {
			if($k == 'modem_macaddr') {
				$v=strtolower($v);
				$sets[0]=substr($v,0,4);
				$sets[1]=substr($v,4,4);
				$sets[2]=substr($v,8,4);
				$v=implode(".",$sets);
				$href="<a href=\"monitoring/cmtsTool.php?mac={$v}&cmts=38.108.136.1\">{$v}</a>";
				$body.="<td>{$href}</td>";
			} else {
				$body.="<td>{$v}</td>";
			}
		}
		$body.=$revsnr;
		$body.="</tr>\n";
	}
}
$body.="<tr><td colspan=\"9\"><pre>".connectCMTS('38.108.136.1','show controller cable 3/0 | incl SNR')."</pre></td></tr>";
$body.="<tr><td colspan=\"9\"><pre>".connectCMTS('38.108.136.1','show controller cable 4/0 | incl SNR')."</pre></td></tr>";
$body.="</table>\n";


buildPage($body);


function getRevSNR($mac) {
	// Conveting mac to cmts format
	$mac=strtolower($mac);
	$oct[0]=substr($mac,0,4);
	$oct[1]=substr($mac,4,4);
	$oct[2]=substr($mac,8,4);
	$mac=implode(".",$oct);
	$cmd='show cable modem '.$mac.' verbose';
	$results=connectCMTS('38.108.136.1',$cmd);
	$res=preg_split("/\r\n/",$results);
	foreach($res as $r) {
		if(preg_match("/^Host Interface/",$r)) {
			$r=trim(preg_replace("/\s+/"," ",$r));
			$r=preg_replace("/^Host Interface : /","",$r);
			$iface=$r;
		}
		if(preg_match("/^Upstream SNR/",$r)) {
			$arr=preg_split("/:/",$r);
			$arr[1]=trim(preg_replace("/\s+/",' ',$arr[1]));
			$arr[1]=preg_replace("/\s/","</td><td>",$arr[1]);
			return "<td>{$iface}</td><td>".$arr[1]."</td>";
		}
	}
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

function var_ddump($data,$debug=0) {
	if($debug) {
		print "<pre>"; var_dump($data); print "</pre>";
	}
}
