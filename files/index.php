<?php
session_start();
require("../config.inc.php");

if(!isset($_SESSION['username'])){
    header("Location: /{$PROJECT_FOLDER}/login");
}
else{
    if($_SESSION['usertype'] == "admin" || $_SESSION['usertype'] == "superadmin"){
        header("Location: /{$PROJECT_FOLDER}/files/admin");
        die("could not redirect to appropiate page");
    }
    elseif($_SESSION['usertype'] == "client" || $_SESSION['usertype'] == "temporary"){
        header("Location: /{$PROJECT_FOLDER}/files/client");
        die("could not redirect to appropiate page");
    }
    else{
         header("Location: /{$PROJECT_FOLDER}");
         die("could not redirect to appropiate page");
    }
}

die("could not redirect to appropiate page");
?>