<?php

require_once("MDB2.php");
define("SPA_TARGET",15);
define("SPB_TARGET",15);
//define("WESTON_TARGET",0);		Changed to 5db on 9/19/2017
define("WESTON_TARGET",5);
define("OTHER_TARGET",16);  // Was 7
define("GRAYS_TARGET",16);

$config['dtypes']=array("IP","2IP","INT8","UINT8","INT16","UINT16","INT32","UINT32","CHAR","SUB-OPT");
$config['nettypes']=array("CM","CPE","MTA");

function docType() {
	return '<!DOCTYPE html>';
	//return '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">';
}
function dbTimestampConvert($val) {
	$t1=preg_split("/ /",$val);
	$t2=preg_split("/-/",$t1[0]);
	$t3=preg_split("/:/",$t1[1]);
	$then=mktime($t3[0],$t3[1],$t3[2],$t2[1],$t2[2],$t2[0]);
	return $then;
}

function fwdRxColor($val) {
	if(abs($val) < 10) {
		return "lightgreen";
	} elseif(abs($val) < 15){
		return "yellow";
	} else {
		return "#cc3333";
	}
}
function fwdSnrColor($val) {
	if($val >= 35) {
		return "lightgreen";
	} elseif($val >= 33) {
		return "yellow";
	} else {
		return "#cc3333";
	}
}
function revTxColor($val) {
	if(($val >= 40) AND ($val <= 50)) {
		return "lightgreen";
	} elseif(($val >=38) AND ($val <= 55)) {
		return "yellow";
	} else {
		return "#cc3333";
	}
}
function revRxColor($val,$property,$node=0) {
	if($property == 'Sun Prairie') {
		switch($node) {
		case 5:
		case 6:
		case 7:
		case 8:
		case 9:
			$target=SPB_TARGET;
			break;
		default:
			$target=SPA_TARGET;
			break;
		}
	} elseif($property == "Weston Park") {
		$target=WESTON_TARGET;
	} elseif($property == "Oakland Pointe") {
		$target=WESTON_TARGET;
	} elseif($property == "Grays Lake") {
		$target=GRAYS_TARGET;
	} else	{
		$target=OTHER_TARGET;
	}
	if(abs($target-$val) <= 1) {
		return "lightgreen";
	} elseif(abs($target-$val) <=2) {
		return "yellow";
	} elseif($val == 0)  {
		return "white";
	} else {
		return "#cc3333";
	}
}
function revSnrColor($val) {
	// New Color Schema for QPSK
	if($val >=30) {
		return "lightgreen";
	} elseif($val >=25) {
		return "yellow";
	} elseif($val >=20) {
		return "pink";
	} else {
		return "red";
	}
	/*
	// Color Schema for QAM
	if($val >= 33) {
		return "lightgreen";
	} elseif($val >= 31) {
		return "yellow";
	} elseif($val >=29) {
		return "pink";
	} else {
		return "#cc3333";
	}
	 */
}
function getHeaderRow($row) {
	$rv="<tr>";
	foreach($row as $k=>$v) {
		$rv.="<td>{$k}</td>";
	}
	$rv.="</tr>\n";
	return $rv;
}
function dynamicConfig1($name,$value='') {
	$ips = genConfigSelect('ips',1,99,$value);
	$docsis1 = genConfigSelect('docsis1',SVC_CLASS_START,SVC_CLASS_END,$value);
	$networkAccess = genConfigSelect('networkaccess',NETWORK_ACCESS_START,NETWORK_ACCESS_END,$value);
	$rv = "<table width=\"100%\">\n";
	$rv.= "<tr><td colspan=\"2\"><b>Docsis 1.0 Options</b></td></tr>\n";
	$rv.= $networkAccess;
	$rv.= $ips;
	$rv.= $docsis1;
	$rv.= "</table>\n";
	return $rv;
}
function dynamicConfig($name,$value='',$tlink=true) {
	$ips = genConfigSelect('ips',1,99,$value);
	$networkAccess = genConfigSelect('networkaccess',NETWORK_ACCESS_START,NETWORK_ACCESS_END,$value);
	$downflow = genConfigSelect('down',DS_FLOW_START,DS_FLOW_END,$value);
	$upflow= genConfigSelect('up',US_FLOW_START,US_FLOW_END,$value);
	$downfreq = genConfigSelect('downfreq',DS_FREQ_START,DS_FREQ_END,$value);
	$upchan   = genConfigSelect('upchan',US_CHAN_START,US_CHAN_END,$value);
	$bpi      = genConfigSelect('bpi',BPI_START,BPI_END,$value);
	$snmp     = genConfigSelect('snmp',SNMP_START,SNMP_END,SNMP_START);
	$docsis2  = genConfigSelect('docsis2Enable',DOCSIS2_START,DOCSIS2_END,$value);
	$confSvr  = genConfigSelect('configServer',CONFIG_SERVER_START,CONFIG_SERVER_END,$value);
	$confFile = genConfigSelect('firmwareUpgrade',CONFIG_FILE_START,CONFIG_FILE_END,$value);
	$cvcStanza= genConfigSelect('cvc',CVC_START,CVC_END,$value);
	$link="<a class=\"tinyLink\" href=\"{$_SERVER['REQUEST_URI']}&mode=1\">1.0 Mode</a>";
	$rv = "<table width=\"100%\">\n";
	if($tlink == true) {
		$rv.= "<tr><td colspan=\"2\"><b>Docsis 1.1+ Configuration</b> {$link}</td></tr>\n";
	} else {
		$rv.= "<tr><td colspan=\"2\"><b>Docsis Configuration</b></td></tr>\n";
	}
	$rv.= $networkAccess;
	$rv.= $ips;
	$rv.= $downflow;
	$rv.= $upflow;
	$rv.= $downfreq;
	$rv.= $upchan;
	$rv.= $bpi;
	$rv.= $snmp;
	$rv.= $docsis2;
	$rv.= $confSvr;
	$rv.= $confFile;
	$rv.= $cvcStanza;
	$rv.= "</table>\n";
	return $rv;
}
function genConfigSelect($name,$cfg_start,$cfg_end,$value) {
	if($value != '') {
		$vs=explode(",",$value);
		foreach($vs as $v) {
			if( ($v >= $cfg_start) && ($v <= $cfg_end) ) {
				$selected = $v;
			}
		}
	}
	if(!isset($selected)) {
		$selected = 0;
	}
	$sql="SELECT cfg_id,comment FROM config_modem WHERE cfg_id >= {$cfg_start} AND cfg_id <= {$cfg_end} ORDER BY cfg_id";
	$sql="SELECT cfg_id,comment FROM config_modem WHERE cfg_id >= {$cfg_start} AND cfg_id <= {$cfg_end} ORDER BY sortorder,cfg_id";
	//phpinfo();exit();
	$name_popup=$name;
	$name_popup="<a class=\"blackNoDecoration\" onmouseover=\"popup('{$name}')\" href=\"{$_SERVER['REQUEST_URI']}\">{$name}</a>";
	$rv = "<tr><td>{$name_popup}</td><td><select name=\"$name\">";
	switch($name) {
	case "networkaccess":
	case "ips":
	case "down":
	case "up":
	case "bpi":
	case "docsis2Enable":
		break;
	default:
		$rv.= "<option value=\"\">NONE</option>";
	}
	$conn = connect();
	$rset = $conn->query($sql);
	if(PEAR::isError($rset)) {
		buildPage($rset->getMessage(),$sql);
		exit();
	}
	while(($row=$rset->fetchRow())==true) {
		$v = $row['cfg_id'];
		$txt = $row['comment'];
		if($row['cfg_id'] == $selected) {
			$rv.="<option value=\"{$v}\" selected=\"selected\">{$txt}</option>";
		} else {
			$rv.="<option value=\"{$v}\">{$txt}</option>";
		}
	}
	$rv.= "</select></td></tr>\n";
	return $rv;
}
function staticConfig($name,$value='',$pup='') {
	if($pup != '') {
		$rv ="<tr><td>{$pup}</td><td><select name=\"{$name}\">";
	} else {
		$rv ="<tr><td>{$name}</td><td><select name=\"{$name}\">";
	}
	$rv.="<option value=\"auto\">auto</option>";
	// Read the docsis_server config to locate the TFTP directory
	$fh=fopen(DOCSIS_SERVER_CONFIG,'r');
	while(($line=fgets($fh,256))==true) {
		if(preg_match('/^TFTP-Dir/',$line)) {
			$line = preg_replace('/\s+/',' ',$line);
			$dir = preg_replace('/^TFTP-Dir /','',$line);
			$dir =trim($dir);
		}
	}
	fclose($fh);
	if(is_dir($dir)) {
		$dh = opendir($dir);
		while(($file=readdir($dh))==true) {
			switch($file) {
			case ".":
			case "..":
				break;
			default:
				if($file != $value) {
					$rv .= "<option value=\"{$file}\">{$file}</option>";
				} else {
					$rv .= "<option value=\"{$file}\" selected=\"selected\">{$file}</option>";
				}
				break;
			}
		}
	} else {
		buildPage($dir." is not a directory!  Cannot find static config-files");
	}
	$rv.="</select></td></tr>\n";
	return $rv;
}
function checkSuper() {
	if(($_COOKIE['username'] != SUPER_USER) || ($_COOKIE['password'] != SUPER_PASS) ) {
		header("Location: logout.php");
	}
}
function doLoginPage() {
	$uri=$_SERVER["REQUEST_URI"];
	$body ="<form method=\"post\" action=\"doLogin.php\">\n";
	$body.="<input type=\"hidden\" name=\"thispage\" value=\"{$uri}\">\n";
	$body.="<table width=\"300\" cellspacing=\"0\" cellpadding=\"5\" border=\"1\">\n";
	$body.="<tr><td>Username</td><td><input type=\"text\" name=\"username\" size=\"10\"></td></tr>\n";
	$body.="<tr><td>Password</td><td><input type=\"password\" name=\"password\" size=\"10\"></td></tr>\n";
	$body.="<tr><td>&nbsp;</td><td><input type=\"submit\" value=\"Login\"></td></tr>\n";
	$body.="</table>\n";
	$body.="</form>\n";
	buildPage($body);
	exit();
}
function OptionYesNo($name,$value="NO") {
	$uri=$_SERVER["REQUEST_URI"];
	$lbl="<a class=\"blackNoDecoration\" onmouseover=\"popup('{$name}')\" href=\"{$uri}\">{$name}</a>";
	$rv='';
	if($value == "NO") {
		$rv.="<tr><td>{$lbl}</td><td>";
		$rv.="<select name=\"{$name}\">";
		$rv.="<option value=\"YES\">YES</option>";
		$rv.="<option value=\"NO\" selected=\"selected\">NO</option>";
		$rv.="</select></td></tr>\n";
		return $rv;
	} else {
		$rv.="<tr><td>{$lbl}</td><td>";
		$rv.="<select name=\"{$name}\">";
		$rv.="<option value=\"YES\" selected=\"selected\">YES</option>";
		$rv.="<option value=\"NO\">NO</option>";
		$rv.="</select></td></tr>\n";
		return $rv;
	}
}
function connect() {
	$dsn="mysql://".MYSQL_USER.":".MYSQL_PASS."@".MYSQL_HOST."/".MYSQL_DB;
	$conn=MDB2::singleton($dsn);
	if(PEAR::isError($conn)) {
		print "<pre>";
		print "Error Connecting to DB: {$dsn}\n";
		print $conn->getMessage();
		print "</pre>";
		exit();
	}
	$conn->setFetchMode(MDB2_FETCHMODE_ASSOC);
	return $conn;
}

function buildPage($body,$comment="") {
	global $menu1;
	global $menu2;
	$js="<script type=\"text/javascript\" src=\"/jquery-1.8.0.js\"></script>\n<script type=\"text/javascript\" src=\"/popup.js\"></script>\n";
	if(preg_match("/iPhone/",$_SERVER["HTTP_USER_AGENT"])) {
		$js='';
	}
	$fp = fopen(BASE_DIR."/HEAD","r");
	$head = fread($fp, filesize(BASE_DIR."/HEAD"));
	fclose($fp);
	$fp = fopen(BASE_DIR."/FOOT","r");
	$foot = fread($fp, filesize(BASE_DIR."/FOOT"));
	fclose($fp);
	$head=preg_replace('/\$MENU1\$/',$menu1,$head);
	$head=preg_replace('/\$MENU2\$/',$menu2,$head);
	$head=preg_replace('/\$JS\$/',$js,$head);
	echo $head;
	echo $body;
	if($comment != "") {
		echo "\n\n<!-- Auto Comment\n";
		echo $comment;
		echo "\n-->\n\n";
	}
	echo $foot;
	exit();
}

function entryLine($lbl,$value='') {
	$uri=$_SERVER["REQUEST_URI"];
	$lbl2="<a class=\"blackNoDecoration\" onmouseover=\"popup('{$lbl}')\" href=\"{$uri}\">{$lbl}</a>";

	if($value != '') {
		return "<tr><td>{$lbl2}</td><td><input type=\"text\" name=\"{$lbl}\" size=\"15\" value=\"{$value}\"></td></tr>\n";
	} else {
		return "<tr><td>{$lbl2}</td><td><input type=\"text\" name=\"{$lbl}\" size=\"15\"></td></tr>\n";
	}
}

function date_picker($name, $month=NULL, $day=NULL, $year=NULL,$minus=0)
{
	$startyear = date('Y')-2;
	$endyear = date('Y');
	$epoch=time()-$minus;
	if($month==NULL)
		$month=date('m',$epoch);
	if($day==NULL) 
		$day=date('d',$epoch);
	if($year==NULL) 
		$year=date('Y',$epoch);

	$months=array('','January','February','March','April','May',
		'June','July','August', 'September','October','November','December');

	// Month dropdown
	$html="<select name=\"".$name."month\">";

	for($i=1;$i<=12;$i++)
	{
		if($i == $month) {
			$selected = "selected=\"selected\"";
		} else {
			$selected=NULL;
		}
		$html.="<option $selected value='$i'>$months[$i]</option>";
	}
	$html.="</select> ";

	// Day dropdown
	$html.="<select name=\"".$name."day\">";
	for($i=1;$i<=31;$i++)
	{
		if($i == $day) {
			$selected="selected=\"selected\"";
		} else {
			$selected=NULL;
		}
		$html.="<option $selected value='$i'>$i</option>";
	}
	$html.="</select> ";

	// Year dropdown
	$html.="<select name=\"".$name."year\">";

	for($i=$startyear;$i<=$endyear;$i++)
	{      
		if($i==$year) {
			$selected="selected=\"selected\"";
		} else {
			$selected=NULL;
		}
		$html.="<option $selected value='$i'>$i</option>";
	}
	$html.="</select> ";

	return $html;
}

?>
