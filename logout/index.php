<?php 
session_start();
require("../config.inc.php");

if(!isset($_SESSION['username'])){ 
    header("Location: /{$PROJECT_FOLDER}");
    die("user not logged in");
}
echo $_SESSION['username']."<br>";
echo $PROJECT_FOLDER;
unset($_SESSION['user_id']);
unset($_SESSION['username']);
session_destroy();
header("Location: /{$PROJECT_FOLDER}");

?>