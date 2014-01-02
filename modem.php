<?php

require_once("config.php");

$myLimit=0;

$order='';
if(isset($_GET['search'])) {
	$search = $_GET['search'];
	$value  = addslashes($_GET['value']);
	switch($_GET['search']) {
	case "modem_macaddr":
		$value=preg_replace("/\./","",$value);
		$value=preg_replace("/:/","",$value);
		$value=preg_replace("/-/","",$value);
		$value=strtoupper($value);
		if(strlen($value)==12) {
			$where="WHERE d.{$search} = '{$value}'";
		} elseif(preg_match("/%/",$value)) {
			$where="WHERE d.{$search} LIKE '{$value}'";
		} else {
			$where="WHERE d.{$search} LIKE '%{$value}%'";
		}
		$order="ORDER BY d.modem_macaddr";
		$myLimit=999;
		break;
	case "node":
	case "building":
	case "property":
		$where = "WHERE c.{$search} = '{$value}'";
		$order = "ORDER BY c.node,c.address,c.apartment";
		$myLimit=999;
		break;
	case "subnum":
		$where = "WHERE d.{$search} LIKE '%{$value}%'";
		$myLimit=999;
		break;
	default:
		$where = "WHERE {$search} LIKE '%{$value}%'";
		$myLimit=999;
		break;
	}
	$sql="SELECT d.modem_macaddr,d.subnum,c.building,c.address,c.apartment,c.property,c.node FROM docsis_modem AS d LEFT OUTER JOIN customer_address AS c ON d.subnum=c.subnum {$where} {$order} LIMIT {$myLimit}";
	//$sql="SELECT a.*,m.fwdrx,m.fwdsnr,m.revtx,m.revrx,m.revsnr FROM (SELECT d.modem_macaddr,d.subnum,c.building,c.address,c.apartment,c.property,c.node FROM docsis_modem AS d LEFT OUTER JOIN customer_address AS c ON d.subnum=c.subnum {$where} {$order} LIMIT {$myLimit}) AS a LEFT OUTER JOIN modem_history AS m on a.modem_macaddr=m.mac";
} else {
	$sql="SELECT a.*,m.fwdrx,m.fwdsnr,m.revtx,m.revrx,m.revsnr FROM (SELECT d.modem_macaddr,d.subnum,c.building,c.address,c.apartment,c.property,c.node FROM docsis_modem AS d LEFT OUTER JOIN customer_address AS c ON d.subnum=c.subnum LIMIT {$myLimit}) AS a LEFT OUTER JOIN modem_history AS m on a.modem_macaddr=m.mac";
}
$mainSQL=$sql;
//print $sql; exit();
$conn = connect();
$search = mySearch();

$body="<table width=\"100%\" cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
$body.="<tr><td>Search</td><td colspan=\"7\">{$search}</td></tr>\n";
$rset = $conn->query($sql);
if(PEAR::isError($rset)) {
	$body="SQL Error: {$sql}<br>";
	$body.=$rset->getMessage();
	buildPage($body);
}
if($rset->numRows() == 0 && strlen($_GET['value']) == 12) {
	$body.="\t<tr><td colspan=\"2\"><a href=\"add_modem.php?modem_macaddr={$_GET['value']}\">Provision Modem {$_GET['value']}</a> | <a href=\"del_modem.php?modem_macaddr={$_GET['value']}\">Delete History</a></td></tr>\n";
}
$header=false;
if($rset->numRows() > 0) {
	while(($row=$rset->fetchRow())==true) {
		if($header==false) {
			$header = true;
			foreach($row as $k=>$v) {
				switch($k) {
				case "fwdrx":
				case "fwdsnr":
				case "revtx":
				case "revrx":
				case "revsnr":
					$keys2[]=$k;
					break;
				default:
					$keys[]=$k;
					break;
				}
			}
			$keys[]="&nbsp;";
			$body.="<tr><td>".join("</td><td>",$keys)."</td></tr>";
		}
		unset($vals);
		unset($vals2);
		foreach($row as $k=>$v) {
			switch($k) {
			case "node":
			case "property":
			case "building":
				$vals[]="<a class=\"blackUnderline\" href=\"monitoring/bester.php?search={$k}&value={$v}\">{$v}</a>";
				break;
			case "config_opt1":
				$vals[]="<a class=\"blackUnderline\" href=\"dhcp_options.php?server_id={$v}\">{$v}</a>";
				break;
			case "modem_macaddr":
				$vals[]="<a class=\"blackUnderline\" href=\"monitoring/modemHistory.php?mac={$v}\">{$v}</a>";
				break;
			case "subnum":
				$vals[]="<a class=\"blackUnderline\" href=\"monitoring/bester.php?search=subnum&value={$v}\">{$v}</a>";
				break;
			case "fwdrx":
				if(abs($v) >= 15) {
					$bgColor="red";
				} elseif(abs($v) >5) {
					$bgColor="yellow";
				} else {
					$bgColor="green";
				}
				$vals2[]=sprintf("<td bgcolor=\"{$bgColor}\" align=\"right\">%.1f</td>",$v);
				break;
			case "fwdsnr":
				if($v >=35) {
					$bgColor="green";
				} elseif($v >= 33) {
					$bgColor="yellow";
				} else {
					$bgColor="red";
				}
				$vals2[]=sprintf("<td bgcolor=\"{$bgColor}\" align=\"right\">%.1f</td>",$v);
				break;
			case "revtx":
				if($v > 55 OR $v < 35) {
					$bgColor="red";
				} elseif($v >50 or $v < 40) {
					$bgColor="yellow";
				} else {
					$bgColor="red";
				}
				$vals2[]=sprintf("<td bgcolor=\"{$bgColor}\" align=\"right\">%.1f</td>",$v);
				break;
			case "revrx":
				$vals2[]=sprintf("<td align=\"right\">%.1f</td>",$v);
				break;
			case "revsnr":
				$vals2[]=sprintf("<td bgcolor=\"white\" align=\"right\">%.1f</td>",$v);
				break;
			default:
				$vals[]=$v;
				break;
			}
		}
		$mac=$row['modem_macaddr'];
		$edit  ="<a class=\"tinyLink\" href=\"edit_modem.php?modem_macaddr={$mac}\">Edit</a>";
		$delete="<a class=\"tinyLink\" href=\"del_modem.php?modem_macaddr={$mac}\">Delete</a>";
		$vals[]="(".$edit." | ".$delete.")";
		$body.="\t<tr><td>".implode("</td><td>",$vals)."</td></tr>\n";
		if(isset($keys2)) {
			$body.="<tr><td align=\"right\">".join("</td><td align=\"right\">",$keys2)."</td><td colspan=\"3\">&nbsp;</td></tr>";
		} 
		if(isseT($vals2)) {
			$body.="\t<tr>".implode("",$vals2)."<td colspan=\"3\">&nbsp;</td></tr>\n";
		}
	}
}
$body.="<tr><td colspan=\"9\"><a href=\"add_modem.php\" class=\"tinyLink\">Add Modem</a></tr></td>\n";
$body.="</table>\n";


buildPage($body,$mainSQL);


function mySearch() {
	global $_GET;
	$search='modem_macaddr';
	if(isset($_GET['search'])) 
		$search=$_GET['search'];
	$rv ='';
	$rv.="<form method=\"get\" action=\"/modem.php\">";
	$sel=NULL;
	if($search=='modem_macaddr') 
		$sel = "checked=\"checked\"";
	$rv.="<input type=\"radio\" {$sel} name=\"search\" value=\"modem_macaddr\">Mac Address&nbsp;&nbsp;&nbsp;";
	$sel=NULL;
	if($search=='subnum') 
		$sel = "checked=\"checked\"";
	$rv.="<input {$sel} type=\"radio\" name=\"search\" value=\"subnum\">Subscriber ID&nbsp;&nbsp&nbsp";
	$sel=NULL;
	if($search=='property')
		$sel = "checked=\"checked\"";
	$rv.="<input {$sel} type=\"radio\" name=\"search\" value=\"property\">Property&nbsp;&nbsp&nbsp";
	$sel=NULL;
	if($search=='building')
		$sel = "checked=\"checked\"";
	$rv.="<input {$sel} type=\"radio\" name=\"search\" value=\"building\">Building&nbsp;&nbsp&nbsp";
	$sel=NULL;
	if($search=='apartment')
		$sel = "checked=\"checked\"";
	$rv.="<input {$sel} type=\"radio\" name=\"search\" value=\"apartment\">Apartment&nbsp;&nbsp&nbsp";
	$sel=NULL;
	if($search=='node')
		$sel = "checked=\"checked\"";
	$rv.="<input {$sel} type=\"radio\" name=\"search\" value=\"node\">Node&nbsp;&nbsp&nbsp<br>";
	if(isset($_GET['value'])) {
		$val = $_GET['value'];
	} else {
		$val=NULL;
	}
	$rv.="&nbsp;&nbsp;&nbsp;<input type=\"text\" name=\"value\" size=\"15\" value=\"{$val}\"><input type=\"submit\" value=\"Search\">";
	$rv.="</form>";
	return $rv;
}

?>
