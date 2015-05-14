<?php

require_once("../../config.php");

$body=file_get_contents("/var/www/monitoring/BAD_FWD_SWINGS");
buildPage($body);


?>
