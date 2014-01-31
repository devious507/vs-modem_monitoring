<?php

require_once("config.php");

if(isset($_GET['limit']) && isset($_GET['value'])) {
	$where="WHERE {$_GET['limit']}='{$_GET['value']}'";
} else {
	$where='';
}
$db=connect();

$temp_table='CREATE TEMPORARY TABLE aaa AS select d.name,d.property,d.building,d.node,c.* FROM (select b.subnum,a.* FROM (select mac,fwdrx,fwdsnr,revtx,revrx,revsnr,time FROM modem_history  WHERE time > date_add(now(), interval -30 minute)) as a LEFT OUTER JOIN docsis_modem AS b ON a.mac=b.modem_macaddr) as c LEFT OUTER JOIN customer_address AS d ON c.subnum=d.subnum';
$remove_refs="delete from aaa WHERE name like '%Reference%'";


$parts=array('fwdrx','fwdsnr','revtx','revrx','revsnr');
$fields='';
foreach($parts as $p) {
	$fields.=",min({$p}) as min_{$p}";
	$fields.=",avg({$p}) as avg_{$p}";
	$fields.=",max({$p}) as max_{$p}";
}
$select_sql="select property,building,node{$fields} FROM aaa {$where} GROUP BY property,building,node ORDER BY property,node,building";

$db->query($temp_table);
$db->query($remove_refs);
$results=$db->query($select_sql);

$body="<table cellpadding=\"3\" cellspacing=\"0\" border=\"1\">\n";
$body.="\t<tr>\n";
$body.="\t\t<td align=\"center\" colspan=\"3\">&nbsp;</td>\n";
$body.="\t\t<td align=\"center\" colspan=\"3\">FwdRX</td>\n";
$body.="\t\t<td align=\"center\" colspan=\"3\">FwdSNR</td>\n";
$body.="\t\t<td align=\"center\" colspan=\"3\">RevTX</td>\n";
$body.="\t\t<td align=\"center\" colspan=\"3\">RevRX</td>\n";
$body.="\t\t<td align=\"center\" colspan=\"3\">RevSNR</td>\n";
$body.="\t</tr>\n";

$body.="\t<tr>\n";
$body.="\t\t<td align=\"center\" colspan=\"3\">&nbsp;</td>\n";
for($i=0; $i<5; $i++) {
	$body.="\t\t<td align=\"right\">Min</td>\n";
	$body.="\t\t<td align=\"right\">Avg</td>\n";
	$body.="\t\t<td align=\"right\">Max</td>\n";
}
$body.="\t</tr>\n";

while(($row=$results->fetchRow())==true) {
	$body.="\t<tr>\n";
	$body.="\t\t<td>{$row['property']}</td>\n";
	$body.="\t\t<td>{$row['building']}</td>\n";
	$body.="\t\t<td>{$row['node']}</td>\n";
	$body.=buildCell($row['min_fwdrx'],'fwdrx');
	$body.=buildCell($row['avg_fwdrx'],'fwdrx');
	$body.=buildCell($row['max_fwdrx'],'fwdrx');
	$body.=buildCell($row['min_fwdsnr'],'fwdsnr');
	$body.=buildCell($row['avg_fwdsnr'],'fwdsnr');
	$body.=buildCell($row['max_fwdsnr'],'fwdsnr');
	$body.=buildCell($row['min_revtx'],'revtx');
	$body.=buildCell($row['avg_revtx'],'revtx');
	$body.=buildCell($row['max_revtx'],'revtx');
	$body.=buildCell($row['min_revrx'],'revrx',$row['property']);
	$body.=buildCell($row['avg_revrx'],'revrx',$row['property']);
	$body.=buildCell($row['max_revrx'],'revrx',$row['property']);
	$body.=buildCell($row['min_revsnr'],'revsnr');
	$body.=buildCell($row['avg_revsnr'],'revsnr');
	$body.=buildCell($row['max_revsnr'],'revsnr');
	$body.="\t<tr>\n";
}
$body.="</table>\n";
buildPage($body);


function buildCell($num,$hint,$property='') {
	switch($hint){
	case "fwdrx":
		$color=fwdRxColor($num);
		break;
	case "fwdsnr":
		$color=fwdSnrColor($num);
		break;
	case "revtx":
		$color=revTxColor($num);
		break;
	case "revrx":
		$color=revRxColor($num,$property);
		break;
	case "revsnr":
		$color=revSnrColor($num,$property);
		break;
	}
	$nnum=sprintf("%.1f",$num);
	return "\t\t<td align=\"right\" bgcolor=\"{$color}\">{$nnum}</td>\n";
		
}
