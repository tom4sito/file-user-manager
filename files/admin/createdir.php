<?php
session_start();
require("../../config.inc.php");
if(!isset($_SESSION['username'])){
    header("location: /{$PROJECT_FOLDER}/login");
    die("user not logged in");
}

if(!($_SESSION['usertype'] == "admin" OR $_SESSION['usertype'] == "superadmin")){
	header("Location:  /{$PROJECT_FOLDER}/files/client");
	die("you do not have permission to view this page");
}

if(isset($_GET["path"])){
	$dirname = $_GET["path"];

	$dirname_formatted = "/var/www/html/uploads/".$dirname;
	if(!file_exists($dirname_formatted)){
		if (!mkdir($dirname_formatted, 0757)) {
			echo "Failed to create directory";
			// die('Failed to create directorys...');
		}
		else{
			echo "created: ". $dirname;
		}
	}
	else{
		echo "Directory already exists";
		die("Directory already exists");
	}

	

}
else{
	echo "directory path is not set";
}

?>