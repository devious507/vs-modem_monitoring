<?php
require_once("config.php");
checkSuper();

$extraSqlFile='/var/www/monitoring/uploadAddressPostSQL.sql';

$propSql[]="UPDATE customer_address SET property='Oakland Pointe' WHERE address like '%17TH AVE NW'";
$propSql[]="UPDATE customer_address SET property='Mansions' WHERE address like '%BRETT ASHLEY PL'";
$propSql[]="UPDATE customer_address SET property='Mansions' WHERE address like '%HEMINGWAY ST'";
$propSql[]="UPDATE customer_address SET property='Mansions' WHERE address like '%JAKE BARNES CT'";
$propSql[]="UPDATE customer_address SET property='Plaza' WHERE address = '300 WALNUT ST'";
$propSql[]="UPDATE customer_address SET property='Grays Lake' WHERE address like '%FLEUR DR'";
$propSql[]="UPDATE customer_address SET property='High Pointe' WHERE address = '1900 CEDAR ST'";
$propSql[]="UPDATE customer_address SET property='Weston Park' WHERE address like '420% PARK AVE'";
$propSql[]="UPDATE customer_address SET property='Summer Woods' WHERE address like '%ML KING JR PKWY'";
$propSql[]="UPDATE customer_address SET property='Summer Woods' WHERE address like '%MARTIN LUTHER KING JR PKWY'";
$propSql[]="UPDATE customer_address SET property='Oak Crossing' WHERE address like '%GATEWAY DR'";
$propSql[]="UPDATE customer_address SET property='Cross Creek' WHERE address like '%MEREDITH DR'";
$propSql[]="UPDATE customer_address SET property='Pleasant Court' WHERE address like '%17TH ST'";
$propSql[]="UPDATE customer_address SET property='Pleasant Court' WHERE address like '%16TH ST'";
$propSql[]="UPDATE customer_address SET property='Sun Prairie' WHERE address like '% VISTA DR'";
$propSql[]="UPDATE customer_address SET property='Sun Prairie' WHERE address like '% PRAIRIE VIEW DR'";
$propSql[]="UPDATE customer_address SET city='West Des Moines' WHERE city='WDM'";


$form = "<form action=\"monitoring/uploadAddress.php\" method=\"post\" enctype=\"multipart/form-data\"><input type=\"file\" name=\"attachement\"></input><br><input type=\"submit\" value=\"Upload\"></form>";


if(!isset($_FILES['attachement'])) {
	buildPage($form);
	exit();
}

$filename=$_FILES['attachement']['tmp_name'];
$fh=fopen($filename,'r');
$data=fread($fh,filesize($filename));
fclose($fh);

$body="<p>Insert Results</p>\n";
$lines=preg_split("/\r\n/",$data);
unset($data);
$db=connect();
$db->query('DELETE FROM customer_address');
foreach($lines as $line) {
	if($line!='') {
		$customer=array();
		$line = preg_replace("/(^\"|\"$)/","",$line);
		$elements = preg_split("/\",\"/",$line);
		$tmp=preg_split("/-/",$elements[0]);
		$customer['franch']=intval($tmp[0]);
		$customer['account']=intval($tmp[1]);
		$customer['name']=addslashes($elements[1]);
		if($elements[4] != '') {
			$customer['apartment']=$elements[2];
			$customer['address']=$elements[3];
			$tmp=preg_split("/ /",$elements[4]);
			$tmp_zip=array_pop($tmp);
			$tmp_state=array_pop($tmp);
			$customer['city']=join(" ",$tmp);
			$customer['state']=$tmp_state;
			$customer['zip']=$tmp_zip;
		} else {
			$customer['apartment']='';
			$customer['address']=$elements[2];
			$tmp=preg_split("/ /",$elements[3]);
			$tmp_zip=array_pop($tmp);
			$tmp_state=array_pop($tmp);
			$customer['city']=join(" ",$tmp);
			$customer['state']=$tmp_state;
			$customer['zip']=$tmp_zip;

		}
		$tmp=preg_split("/ /",$customer['address']);
		$customer['property']='';
		$customer['building']=$tmp[0];
		$customer['node']='';
		$sql="INSERT INTO customer_address VALUES ('".implode("','",$customer)."')";
		$rset=$db->query($sql);
		if(PEAR::isError($rset)) {
			$body.=$sql."<br>\n";
			$body.=$rset->getMessage()."<br>\n";
		}
	}
}
$sql="SELECT * FROM fake_accounts ORDER BY subnum";
$rset=$db->query($sql);
while(($row=$rset->fetchRow())==true) {
	$tmp=preg_split("/ /",$row['address']);
	$building=$tmp[0];
	$sql=sprintf("INSERT INTO customer_address (franch,subnum,name,address,city,state,building) VALUES (%d,%d,'%s','%s','%s','%s',%d)",$row['franch'],$row['subnum'],$row['name'],$row['address'],$row['city'],$row['state'],$building);
	$rrset=$db->query($sql);
	if(PEAR::isError($rrset)) {
		$body.=$sql."<br>\n";
		$body.=$rrset->getMessage()."<br>\n";
	}
}
foreach($propSql as $sql) {
	$rset=$db->query($sql);
	if(PEAR::isError($rset)) {
		$body.=$sql."<br>\n";
		$body.=$rset->getMessage()."<br>\n";
	}
}
$fp=fopen($extraSqlFile,'r');
$data=fread($fp,filesize($extraSqlFile));
fclose($fp);
$lines=preg_split("/\n/",$data);
unset($data);
foreach($lines as $line) {
	if($line != '') {
		$rset = $db->query($line);
		if(PEAR::isError($rset)) {
			$body.=$line."<br>\n";
			$body.=$rset->getMessage()."<br>\n";
		}
	}
}
if(!preg_match('/Error/',$body)) {
	$body.="OK";
}
buildPage($body);
?>
