<?php

require_once("../../config.php");
$body='<ul>';
$body.='<li><a href="monitoring/troubleshooter/lastDhcpEntries.php">Last Dhcp Entries</a></li>';
$body.='<li><a href="monitoring/flapcheck/index.php">Flap Listing</a></li>';
$body.="<li>------------------------------------------------------------------------------------------------------------</li>";
$body.='<li><a href="monitoring/troubleshooter/genericLevelChecker.php?type=fwdsnr&lessthan=33&greaterthan=">All Channels Bad Fwd SNR</a></li>';
$body.='<li><a href="monitoring/troubleshooter/genericLevelChecker.php?type=fwdsnr&downstream=8%&lessthan=33&greaterthan=">High Channels Bad Fwd SNR</a></li>';
$body.='<li><a href="monitoring/troubleshooter/genericLevelChecker.php?type=fwdsnr&downstream=1%&lessthan=33&greaterthan=">Low Channels Bad Fwd SNR</a></li>';
$body.="<li>------------------------------------------------------------------------------------------------------------</li>";
$body.='<li><a href="monitoring/troubleshooter/genericLevelChecker.php?type=fwdrx&lessthan=-15&greaterthan=15">All Channels Bad Fwd RX</a></li>';
$body.='<li><a href="monitoring/troubleshooter/genericLevelChecker.php?type=fwdrx&downstream=8%&lessthan=-15&greaterthan=15">High Channels Bad Fwd RX</a></li>';
$body.='<li><a href="monitoring/troubleshooter/genericLevelChecker.php?type=fwdrx&downstream=1%&lessthan=-15&greaterthan=15">Low Channels Bad Fwd RX</a></li>';
$body.="<li>------------------------------------------------------------------------------------------------------------</li>";
$body.='<li><a href="monitoring/troubleshooter/genericLevelChecker.php?type=revsnr&downstream=&lessthan=33&greaterthan=">Bad Rev SNR</a></li>';
$body.='<li><a href="monitoring/troubleshooter/genericLevelChecker.php?type=revsnr&downstream=&lessthan=&greaterthan=33">Good Rev SNR</a></li>';
$body.="<li>------------------------------------------------------------------------------------------------------------</li>";
$body.='<li><a href="monitoring/troubleshooter/genericLevelChecker.php?type=revtx&downstream=&lessthan=38&greaterthan=55">Bad Reverse TX</a></li>';
$body.="<li>------------------------------------------------------------------------------------------------------------</li>";
$body.='<li><a href="monitoring/troubleshooter/genericLevelChecker.php">Generic Level Checker</a></li>';
$body.='<li><a href="monitoring/troubleshooter/genericLevelChecker.php?type=paste">Modem ID Tool</a></li>';
$body.='<li><a href="monitoring/troubleshooter/nodeBuildingPropertyAvgs.php?type=node">Averages - Node</a></li>';
$body.='<li><a href="monitoring/troubleshooter/nodeBuildingPropertyAvgs.php?type=property">Averages - Property</a></li>';
$body.='<li><a href="monitoring/troubleshooter/nodeBuildingPropertyAvgs.php?type=building">Averages - Building</a></li>';
$body.='<li><a href="monitoring/troubleshooter/report_downstreamsByConfig.php">Modem Configuration Counts</a></li>';
$body.="<li><a href=\"monitoring/troubleshooter/badFwdSwings.php\">Bad Forward Swings</a></li>";

$body.="<li>-----------Development--------------------------------------------------------------------------------------</li>";
$body.="<li><a href=\"monitoring/troubleshooter/duplicateAcctIDs.php\">Accounts w/ More than 1 Modem</a></li>";
$body.="<li><a href=\"monitoring/troubleshooter/unMatchedModems.php\">Unmatched Accounts</a></li>";
$body.="<li><a href=\"monitoring/troubleshooter/referenceOverview.php\">All Reference Reverse</a></li>";
$body.="<li><a href=\"monitoring/troubleshooter/referenceOverview.php?property=Sun%20Prairie\">SP Reference Reverse</a></li>";

$body.="</ul>";
buildPage($body);


?>
