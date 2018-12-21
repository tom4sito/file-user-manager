<?php
session_start();
require("../config.inc.php");

error_reporting(-1);
ini_set('display_errors', TRUE);

echo $_SESSION['usertype'];

if(!isset($_SESSION['username'])){
    header("location: /{$PROJECT_FOLDER}/login");
}

if(!($_SESSION['usertype'] == "admin" || $_SESSION['usertype'] == "superadmin")){
	header("location: /{$PROJECT_FOLDER}");
}

if(($_SESSION['usertype'] == "admin" || $_SESSION['usertype'] == "superadmin")){
	header("location: /{$PROJECT_FOLDER}/users/create");
}


?>