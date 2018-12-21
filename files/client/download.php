<?php
session_start();
require("../../config.inc.php"); 
error_reporting(-1);
ini_set('display_errors', TRUE);

if(!isset($_SESSION['username'])){
	header("Location: /{$PROJECT_FOLDER}/login");
}

if(!($_SESSION['usertype'] == "client" || $_SESSION['usertype'] == "temporary")){
	header("location: /{$PROJECT_FOLDER}");
}

$file_id = $_POST["file"];
$message = "";

// gets db credentials
$config = parse_ini_file("/var/www/html/db.ini");
// Create connection
$conn = mysqli_connect("localhost", $config['username'], $config['password'], $config['db']);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT * FROM `files_password` 
WHERE `files_id` = '$file_id' 
AND `password_id` = '{$_SESSION['user_id']}'";

$result = mysqli_query($conn, $sql);
if(mysqli_num_rows($result) > 0){
	$sql = "SELECT * FROM `files` WHERE `id` = '$file_id'";

	$result = mysqli_query($conn, $sql);
	if(mysqli_num_rows($result) > 0){
		$row = mysqli_fetch_assoc($result);
		$public_path = array_slice(explode("/", $row['full_path']), 5);
		$public_path = implode("/", $public_path);
		$file_name = $row['file_name'];
		$file = "/var/www/html/uploads/".$public_path;
		
		$sql_download = "INSERT INTO `downloads` (file_id, user_id, file_name) VALUES ('{$row['id']}', '{$_SESSION['user_id']}', '{$row['full_path']}')";
		if(mysqli_query($conn, $sql_download)){
			$message = "download successful: ";
		}
		else{
			$message = "failed download count: ".mysqli_error($conn);
		}

	}
}
else{
	$message = "you do not have rights to download this file";
	header("Location: ./?message=$message");
	die($message);
}




mysqli_close($conn);


if(!is_file($file)){
    header("{$_SERVER['SERVER_PROTOCOL']} 404 Not Found");
    header("Status: 404 Not Found");
    echo 'File not found!';
    die;
}

if(!is_readable($file)){
    header("{$_SERVER['SERVER_PROTOCOL']} 403 Forbidden");
    header("Status: 403 Forbidden");
    echo 'File not accessible!';
    die;
}

header('Content-Description: File Transfer');
header('Content-Type: application/zip');
header("Pragma: public");
header("Expires: 0");
header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
header("Content-Type: application/force-download");
header("Content-Type: application/download");
header("Content-Disposition: attachment;filename={$file_name}");
header("Content-Transfer-Encoding: binary ");
header('Content-Length: ' . filesize($file));
while(ob_get_level()) ob_end_clean();
flush();
readfile($file);


die;


?>