<?php
session_start();
require("config.inc.php");
error_reporting(-1);
ini_set('display_errors', TRUE);
if(!isset($_SESSION['username'])){
    header('location: login/');
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
		 	echo "main page goes here";
		 ?>

		 <ul>
		 	<li>file1_level1.doc</li>
		 	<li>file2_level1.doc</li>
		 	<li>
		 		<span class="btn btn-primary" data-toggle="collapse" href="#dir1" role="button" aria-expanded="false" aria-controls="dir1">dir1_level1.doc</span> 
		 		<ul id="dir1"  class="collapse">
		 			<li>file1_level2.doc</li>
		 			<li>file2_level2.doc</li>
		 			<li>
		 				<span class="btn btn-primary" data-toggle="collapse" href="#dir2" role="button" aria-expanded="false" aria-controls="dir2">dir1_level2.doc</span>
		 				<ul id="dir2"  class="collapse">
		 					<li>file1_level3.doc</li>
		 				</ul>
		 			</li>
		 			<li>file3_level2.doc</li>
		 		</ul>	
		 	</li>
		 	<li>file3_level1.doc</li>
		 </ul>
		
	</div>
	<?php include("foot.inc.php") ?>
</body>
</html>
