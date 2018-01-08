<?php

require_once("defines.php");

$data=file_get_contents(INFILE);

$data=preg_replace("/\r/","",$data);
$arr=preg_split("/\n/",$data);
$fh=fopen(OUTFILE,'w');
foreach($arr as $line) {
	if(preg_match('/^....\.....\...../',$line)) {
		fwrite($fh,$line."\n");
		$p=parseLine($line);
		saveLine($line,$p);
		$count++;
	}
}
fclose($fh);


?>
