<?php

define("INFILE","/var/www/monitoring/FLAP/LIST");
define("OUTFILE","/var/www/monitoring/FLAP/OUT");
define("FILEDIR","/var/www/monitoring/FLAP/history/");
define("MINFLAPS","10");


function saveLine($line,$p) {
	$line=preg_replace("/\n/","",$line);
	$filename=FILEDIR.$p['mac'];
	print $filename."\n";
	if(file_exists($filename)) {
		$myData=file_get_contents($filename);
		$data=preg_split("/\n/",$myData);
		while(count($data) > 14) {
			array_shift($data);
		}
	}
	//$data[]=$line;
	if(count($data) >0) {
		array_unshift($data,$line);
	} else {
		$data[]=$line;
	}
	$myData=implode("\n",$data);
	unset($data);
	$fh=fopen($filename,'w');
	fwrite($fh,$myData);
	fclose($fh);
	return;
}

function parseLine($line) {
	$line=preg_replace("/ +/"," ",$line);
	$p=preg_split("/ /",$line);
	$rv['mac']=array_shift($p);
	$rv['upstream']=array_shift($p);
	$rv['ins']=array_shift($p);
	$rv['hit']=array_shift($p);
	$rv['miss']=array_shift($p);
	$rv['crc']=array_shift($p);
	$rv['padj']=array_shift($p);
	$rv['flap']=array_shift($p);
	$rv['tstamp']=implode(" ",$p);
	return $rv;
}


?>
