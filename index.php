<?php
session_start();
require("config.inc.php");
error_reporting(-1);
ini_set('display_errors', TRUE);
if(!isset($_SESSION['username'])){
    header('location: login/');
    die("redirect to login");
}

if($_SESSION['usertype'] == 'admin' || $_SESSION['usertype'] == 'superadmin' ){
	header('Location: files/admin');
	die("redirect to file manager"); 
}
else{
	header('Location: files/client');
	die("redirect to user files");
}

?>

<!DOCTYPE html>
<html>
<head>
	<?php include("head.inc.php"); ?>
</head>
<body>
	<div class="body">
		 <?php
		 	// TOP NAVBAR
		 	include("header.inc.php");
		 ?>
		 <h1>Main Page</h1>
		
	</div>
	<?php include("foot.inc.php") ?>
</body>
</html>
