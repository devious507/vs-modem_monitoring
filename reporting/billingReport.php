<?php

require_once("../config.php");

if(!isset($_GET['month']) || !isset($_GET['year'])) {
	$month=date("m");
	$year=date("Y");
	if($month == 1) {
		$month=12;
	} else {
		$month--;
	}
	if($month==12) {
		$year--;
	}
	print "<html><head><title>Enter Dates</title></head><body>\n";
	print "<form method=\"get\" action=\"billingReport.php\">";
	print "Month: <input type=\"text\" size=\"2\" name=\"month\" value=\"{$month}\"><br>";
	print "Year: <input type=\"text\" size=\"4\" name=\"year\" value=\"{$year}\"><br>";
	print "<input type=\"submit\">";
	print "</form>";
	print "</body></html>";
	exit();
} else {
	$month=$_GET['month'];
	$year=$_GET['year'];
}


if(( $month == date('m') ) && ( $year == date('Y')) ) {
	$mtd=true;
} else {
	$mtd=false;
}
print "<html><head><title>Monthly Billing Report</title></head><body><table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">\n";
if($mtd) {
	print "<tr><td colspan=\"9\" align=\"center\"><strong>Estimate Only Incomplete Month ({$month}/{$year})</strong></td></tr>\n";
	print "<tr><td>Name</td><td>Wincable</td><td>Down</td><td>Up</td><td>Total</td><td>Quota</td><td>Projection</td><td colspan=\"2\">Current Status</td></tr>\n";
} else {
	print "<tr><td>Name</td><td>Wincable</td><td>Down</td><td>Up</td><td>Total</td><td>Quota</td><td>Over</td><td>Buckets Over</td><td>$ Amount</td></tr>\n";
}
$count=0;
$db=connect();
getList($db,$month,$year,112,350,$mtd);		// Config 112 is 30Meg, has 350GB Quota
getList($db,$month,$year,114,500,$mtd);		// Config 114 is 50Meg, has 500GB Quota
getList($db,$month,$year,109,1500,$mtd);	// Config 109 is 100Meg, has 1500GB Quota
getList($db,$month,$year,113,4000,$mtd);	// Config 113 is 125Meg, has 4000GB Quota
print "</table></body></html>";

function getList($db,$month,$year,$config_piece,$quota,$mtd) {
	$sql="select distinct(subnum) from docsis_modem WHERE config_file='auto' and dynamic_config_file like '%,{$config_piece},%' ORDER BY subnum";
	$results=$db->query($sql);
	while(($row=$results->fetchRow()) == true) {
		$subnum=$row['subnum'];
		$name=getName($db,$subnum);
		$data=getUsage($db,$subnum,$month,$year);
		$down=$data['down'];
		$up=$data['up'];
		$total=$down+$up;
		$down=sprintf("%.1f",$down/1024/1024/1024);
		$up=sprintf("%.1f",$up/1024/1024/1024);
		$total=sprintf("%.1f",$total/1024/1024/1024);
		$over=$quota-$total;
		if($over < 0) {
			$over=abs($over);
		} else {
			$over=0;
		}
		$buckets=ceil($over/50);
		$amount=sprintf("$%.2f",$buckets*10);
		if($buckets==0) {
			$buckets="&nbsp;";
			$amount="&nbsp;";
		}
		print "<tr>";
		print "<td>{$name}</td>";
		print "<td>{$subnum}</td>";
		print "<td align=\"right\">{$down}GB</td>";
		print "<td align=\"right\">{$up}GB</td>";
		print "<td align=\"right\">{$total}GB</td>";
		print "<td align=\"right\">{$quota}GB</td>";
		if($mtd) {
			$tmp=preg_split("/ /",$name);
			$lname=$tmp[count($tmp)-1];
			$used = getMtdUsage($db,$subnum);
			$projected=sprintf("%.1f",$used/date('d')*date('t'));
			if($projected > $quota) {
				$pTail="bgcolor=\"red\"";
			} elseif($projected > $quota*.9) {
				$pTail="bgcolor=\"yellow\"";
			} else {
				$pTail='';
			}
			if($over < 0 ) {
				$over=0;
			}
			print "<td align=\"right\" {$pTail}>{$projected}GB</td>";
			$img="<img src=\"http://www.visionsystems.tv/quota/quotaGraph.php?quota={$quota}&use={$used}\">";
			print "<td align=\"right\">{$img}</td>";
		} else {
			print "<td align=\"right\">{$over}GB</td>";
			print "<td align=\"right\">{$buckets}</td>";
			print "<td align=\"right\">{$amount}</td>";
		}
		print "</tr>\n";
	}
}

function getMtdUsage($db,$sub) {
	$sql="select (sum(down_delta)+sum(up_delta))/1024/1024/1024 AS used FROM cable_usage WHERE sub_id={$sub}";
	$res=$db->query($sql);
	$row=$res->fetchRow();
	return sprintf("%.1f",$row['used']);
}
function getUsage($db,$subnum,$month,$year) {
	$sql="select sum(down_delta) as down, sum(up_delta) AS up FROM monthly_usage WHERE month={$month} AND year={$year} AND sub_id=$subnum";
	$res=$db->query($sql);
	$row=$res->fetchRow();
	return $row;
}

function getName($db,$subnum) {
	$sql="select name from customer_address where subnum='{$subnum}'";
	$res=$db->query($sql);
	$row=$res->fetchRow();
	return $row['name'];
}
