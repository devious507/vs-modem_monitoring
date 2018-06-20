<?php

$to=$_POST['to'];
//$to="paulmoster@gmail.com";
$subject=$_POST['subject'];
$message=$_POST['body'];
//$headers="From: {$_POST['from']}\r\n";
$headers="Cc: {$_POST['cc']}\r\n";

mail($to,$subject,$message,$headers);
header("Location: http://www.visionsystems.tv/~paulo/dmca/");
?>
