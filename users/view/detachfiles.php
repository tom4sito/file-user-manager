<?php
session_start();

$config = parse_ini_file("/var/www/html/db.ini");
$conn = mysqli_connect("localhost", $config['username'], $config['password'], $config['db'] );
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if(isset($_GET['ids'])){
	// $ids = join(', ', $_GET['ids']);
	// $sqlDetachFiles = "DELETE FROM `files_password` WHERE  files_id IN ({$_GET['ids']})";
	// if(mysqli_query($conn, $sqlDetachFiles)){
	// 	echo "files detached successfully";
	// }
	// else{
	// 	echo "problem encountered when detaching files.";
	// }
	echo "successful request";
}

mysqli_close($conn)

?>