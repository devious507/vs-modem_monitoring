<?php


$sender_email="paulo@visionsystems.tv";

$complaint=$_GET['complaint'];
$cc='paulo@visionsystems.tv,david@visionsystems.tv';

$from=getEmailPiece($complaint,"/^From: /");
$subject=getEmailPiece($complaint,"/^Subject: /");

$body="The referenced compalint has been addressed, the offending subscriber(s) have had their Internet Services suspended pending contact with us for customer education.  \n\nComplaint Summary Follows:\n\n";
$body.=$complaint;
$clickies = clickies();

print "<html><head><title>Email Sender</title></head><body>";
print "<form method=\"post\" action=\"doSendEmail.php\">";
print "<table cellpadding=\"5\" cellspacing=\"0\" border=\"1\">";
print "<tr><td colspan=\"2\">&nbsp;</td><td rowspan=\"6\" align=\"center\" valign=\"top\">{$clickies}</a></td></tr>";
print getHeaderLines("To:",$from,"to");
print getHeaderLines("From:",$sender_email,"from");
print getHeaderLines("Bcc:",$cc,"cc");
print getHeaderLines("Subject:",$subject,"subject");
print "<tr><td colspan=\"2\"><textarea rows=\"25\" cols=\"80\" name=\"body\">{$body}</textarea></td></tr>";
print "<tr><td colspan=\"3\"><input type=\"submit\"></td></tr>";
print "</table>";
print "</form>";
print "</body></html>";

function clickies() {
	$c[]="<a href=\"#\" onclick=\"document.forms[0].to.value='copyright@cogentco.com,dmca@digitalrightscorp.com'\">Digital Rights Corp</a>";
	$rv=implode("<br>",$c);
	return $rv;
}
function getHeaderLines($left,$right,$name) {
	$rv="<tr><td>{$left}</td><td><input type=\"text\" name=\"{$name}\" id=\"{$name}\" value=\"{$right}\" size=\"68\"></td></tr>";
	return $rv;
}
function getEmailPiece($data,$pat) {
	$arr=preg_split("/\n/",$data);
	foreach($arr as $line) {
		if(preg_match($pat,$line)) {
			$rv=preg_replace($pat,"",$line);
			return $rv;
		}
	}
}
?>
