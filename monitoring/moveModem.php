<?php

require_once("config.php");
require_once("function_getModemInfo.php");

$cmd="test cable dcc {$_GET['mac']} frequency {$_GET['freq']}";

if(isset($_GET['cmts']) && isset($_GET['mac']) && isset($_GET['confirm']) && $_GET['confirm']=='true') {
	connectCMTS($_GET['cmts'],$cmd);
	header("Location: /monitoring/cmtsTool.php?mac={$_GET['mac']}&cmts={$_GET['cmts']}");
	exit();
}

$confirm="<a href=\"/monitoring/moveModem.php?mac={$_GET['mac']}&cmts={$_GET['cmts']}&freq={$_GET['freq']}&confirm=true\">Confirm Moving {$_GET['mac']} to {$_GET['freq']}</a>";
?>
<html>
<head><title>Confirm Modem Move</title></head>
<body>
<?php echo $confirm; ?>
</body>
</html>
