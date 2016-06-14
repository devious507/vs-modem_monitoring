<?php

define("LOGIN_PASS","gamester");
define("ENABLE_PASS","renter30");

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
			$cable_iface=$ttmp[0]."/".$ttmp[1]."/".$ttmp[2];
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
?>
