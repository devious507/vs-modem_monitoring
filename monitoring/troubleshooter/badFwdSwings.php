<?php

require_once("../../config.php");

$body="<a href=\"/monitoring/troubleshooter/badFwdSwings.php?menu=false\">Hide Menus</a><br>&nbsp;<hr>";
$body.=file_get_contents("/var/www/monitoring/BAD_FWD_SWINGS");


if(isset($_GET['menu'])) {
	buildPage2($body);
	exit();
} else {
	buildPage($body);
	exit();
}

function buildPage2($body) {
	print "<html>";
	print "<head><title>Bad Forward Swings</title></head>";
	print "<body>";
	print $body;
	print "</body>";
	print "</html>";
	exit();
}

?>
