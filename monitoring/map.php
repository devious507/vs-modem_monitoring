<?php

require_once("../config.php");
$body='<ul>';
$body.='<li><a href="monitoring/map/index.php">All Modems</a></li>';
$body.='<li><a href="monitoring/map/index.php?mode=offline">All Offline Modems</a></li>';
$body.='<li><a href="monitoring/map/index.php?mode=offline24">All Recent Offline Modems</a></li>';
$body.="</ul>";
buildPage($body);


?>
