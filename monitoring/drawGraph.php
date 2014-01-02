<?php

$c[]="00ff00";
$c[]="0000ff";
$c[]="000000";

require_once("config.php");
$type=$_GET['type'];
if(isset($_GET['type2'])) 
	$type2=$_GET['type2'];
if(isset($_GET['type3']))
	$type3=$_GET['type3'];

$start=$_GET['start'];
$end=$_GET['end'];
$mac=$_GET['mac'];
if($start > $end) {
	$a=$start;
	$start=$end;
	$end=$a;
	unset($a);
}
if($end > time()) {
	$end=time();
}
$dataFile=BASE_DIR."/monitoring/rrd/{$mac}.rrd";

$startString=date('m/d/Y H:i:s',$start);
$endString  =date('m/d/Y H:i:s',$end);

$title=$mac." (".$startString." - ".$endString.")";
$rrd=rrdTool;
$rrd.=" graph - -a PNG --start {$start} --end {$end} --width 700 --height 300 ";
$rrd.=" -A -t '{$title}' ";
$rrd.="DEF:{$type}a={$dataFile}:{$type}:AVERAGE ";
if(isset($type2))
	$rrd.="DEF:{$type2}a={$dataFile}:{$type2}:AVERAGE ";
if(isset($type3))
	$rrd.="DEF:{$type3}a={$dataFile}:{$type3}:AVERAGE ";

if($end-$start < 86400) {
	$line="LINE2";
} else {
	$line="LINE1";
}

$lbl=sprintf("%6s",$type);
if(isset($type2))
	$lbl2=sprintf("%6s",$type2);
if(isset($type3))
	$lbl3=sprintf("%6s",$type3);
$rrd.="{$line}:{$type}a#{$c[0]}:\"{$lbl}\" ";
$rrd.="VDEF:{$type}max={$type}a,MAXIMUM ";
$rrd.="VDEF:{$type}avg={$type}a,AVERAGE ";
$rrd.="VDEF:{$type}min={$type}a,MINIMUM ";
$rrd.="GPRINT:{$type}min:\"Minimum %6.1lf\" ";
$rrd.="GPRINT:{$type}avg:\"Average %6.1lf\" ";
$rrd.="GPRINT:{$type}max:\"Maximum %6.1lf\l\" ";
if(isset($type2)) {
	$rrd.="{$line}:{$type2}a#{$c[1]}:\"{$lbl2}\" ";
	$rrd.="VDEF:{$type2}max={$type2}a,MAXIMUM ";
	$rrd.="VDEF:{$type2}avg={$type2}a,AVERAGE ";
	$rrd.="VDEF:{$type2}min={$type2}a,MINIMUM ";
	$rrd.="GPRINT:{$type2}min:\"Minimum %6.1lf\" ";
	$rrd.="GPRINT:{$type2}avg:\"Average %6.1lf\" ";
	$rrd.="GPRINT:{$type2}max:\"Maximum %6.1lf\l\" ";
}
if(isset($type3)) {
	$rrd.="{$line}:{$type3}a#{$c[2]}:\"{$lbl3}\" ";
	$rrd.="VDEF:{$type3}max={$type3}a,MAXIMUM ";
	$rrd.="VDEF:{$type3}avg={$type3}a,AVERAGE ";
	$rrd.="VDEF:{$type3}min={$type3}a,MINIMUM ";
	$rrd.="GPRINT:{$type3}min:\"Minimum %6.1lf\" ";
	$rrd.="GPRINT:{$type3}avg:\"Average %6.1lf\" ";
	$rrd.="GPRINT:{$type3}max:\"Maximum %6.1lf\l\" ";
}



header("Content-type: image/png"); system($rrd);
//print $rrd;
?>
