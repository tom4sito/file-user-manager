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

	$dirpath = $_GET["path"];

    $dirpath_formatted = "/var/www/html/uploads/".$dirpath;

    $config = parse_ini_file("/var/www/html/db.ini");

    $conn = mysqli_connect("localhost", $config['username'], $config['password'], $config['db'] );

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    deleteDirectory($dirpath_formatted, $conn);
}

function deleteDirectory($dir, $db){
    if (!file_exists($dir)){
        return true;
    }
    if (!is_dir($dir)){
        $sql = "SELECT * FROM `files` WHERE `full_path` = '$dir' ";
        $result = mysqli_query($db, $sql);
        
        if(mysqli_num_rows($result) > 0){
            $row = mysqli_fetch_assoc($result);
            $sql_file_password = "DELETE FROM `files_password` WHERE `files_id` =  '{$row['id']}'";

            if(mysqli_query($db, $sql_file_password)){
                echo "file associations were detached";

                $sql_file = "DELETE  FROM `files` WHERE `id` = '{$row['id']}' ";
                if(mysqli_query($db, $sql_file)){
                    echo "file path removed from database";
                }
                else{
                    echo "file path could not be removed from database: ". mysqli_error($conn);
                }
            }
            else{
                echo "file associations could not be detached: ". mysqli_error($conn);
            }
        }
        else{
            echo "$dir does not exist.";
        }

        return unlink($dir);
    }

    foreach (scandir($dir) as $item){
        if ($item == '.' || $item == '..'){
            continue;
        }
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item, $db)){
            return false;
        }
    }

    return rmdir($dir);
}

?>