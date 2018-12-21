<?php
session_start();
// require("../../../config.inc.php");
error_reporting(-1);
ini_set('display_errors', TRUE);

if(!($_SESSION['usertype'] == "admin" || $_SESSION['usertype'] == 'superadmin')){
	header("Location: /{$PROJECT_FOLDER}/files/client");
	die("redirected to client files");
}

$downloadDatesArr = [];
$config = parse_ini_file("/var/www/html/db.ini");
$conn = mysqli_connect("localhost", $config['username'], $config['password'], $config['db']);

if(!$conn){
	die("connection failed: " . mysqli_connect_error());
}

$sql = "SELECT download_date 
FROM downloads 
WHERE file_id = {$_GET['fileid']} 
AND user_id = {$_GET['userid']}";

$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0){
	while ($row = mysqli_fetch_assoc($result)){
		$downloadDatesArr[] = $row['download_date'];
	}
}
else{
	$downloadDatesArr = "no downloads";
}

echo json_encode($downloadDatesArr);

?>