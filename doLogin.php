<?php

$expires=time()+(8*60*60);
setcookie('username',$_POST['username'],$expires);
setcookie('password',$_POST['password'],$expires);
if(isset($_POST['thispage'])) {
	$uri=$_POST['thispage'];
} else {
	$uri='/index.php';
}
	header("Location: {$uri}");
?>
