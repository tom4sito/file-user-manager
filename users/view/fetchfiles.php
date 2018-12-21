<?php
session_start();
require("../../config.inc.php");
error_reporting(-1);
ini_set('display_errors', TRUE);

if(!isset($_SESSION['username'])){
    header('location: login/');
    die("redirected to login page");
}

if(!($_SESSION['usertype'] == "admin" || $_SESSION['usertype'] == 'superadmin')){
	header("Location: /{$PROJECT_FOLDER}/files/client");
	die("redirected to client files");
}

$returnarray = array();

$config = parse_ini_file("/var/www/html/db.ini");
$conn = mysqli_connect("localhost", $config['username'], $config['password'], $config['db'] );
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


header('Content-Type: application/json');
// $sql = "SELECT * FROM `files_password` WHERE `password_id` = '{$_GET["userid"]}' ";

$sql = "SELECT `files`.`id`, `files`.`file_name`, `files`.`full_path`, `files`.`upload_timestamp` 
FROM `files` join `files_password` 
ON `files`.`id` = `files_password`.`files_id` 
WHERE `files_password`.`password_id` = {$_GET['userid']}";


$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0){
	while ($row = mysqli_fetch_assoc($result)) {
		$filearr = array();

		$id = $row['id'];
		$filename = $row['file_name'];
		array_push($filearr, $id, $filename);

		array_push($returnarray, $filearr);

	}
	echo json_encode($returnarray);
}

?>