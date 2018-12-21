<?php
session_start();
require("../../config.inc.php");

error_reporting(-1);
ini_set('display_errors', TRUE);

if(!isset($_SESSION['username'])){
    header("location: /{$PROJECT_FOLDER}/login");
}

if(!($_SESSION['usertype'] == "client" || $_SESSION['usertype'] == "temporary")){
	header("location: /{$PROJECT_FOLDER}");
}

$config = parse_ini_file("/var/www/html/db.ini");
// Create connection
$conn = mysqli_connect("localhost", $config['username'], $config['password'], $config['db']);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// $sql = "SELECT * FROM `files` WHERE `id` = '{$_SESSION['user_id']}' ";

$sql = "SELECT `files`.`id`, `files`.`file_name`, `files`.`full_path`, `files`.`upload_timestamp` 
FROM `files` join `files_password` 
ON `files`.`id` = `files_password`.`files_id` 
WHERE `files_password`.`password_id` = {$_SESSION['user_id']}";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>
<html>
<head>
	<?php include("../../head.inc.php"); ?>
	<style type="text/css">
		.main-container {
			margin-left: 20px;
		}
	</style>
</head>
<body>
	<div class="body">
		<?php
			// TOP NAVBAR
			include("../../header.inc.php");
		?>
		<div class="main-container">
			<h1>Files Available to Download</h1>
			<table>
				<tr>
					<th>File Name</th>
					<th>File Size</th>
					<th>Upload Date</th>
				</tr>
				<?php
				if (mysqli_num_rows($result) > 0) {
				    while($row = mysqli_fetch_assoc($result)) {
				    	$file_size = filesize($row['full_path']);
				    	$datetime = DateTime::createFromFormat ( "Y-m-d H:i:s", $row["upload_timestamp"] );

				    	echo "<tr>";
				    	echo 	"<td>";
					    echo 		"<a href='download.php?fileid=".
					    			$row['id']."''>".$row['file_name']." </a>";
					    echo 	"</td>";
					    echo 	"<td>";
					    echo 		" $file_size KB";
					    echo 	"</td>";
					    echo 	"<td>";
					    // echo 		" {$row['upload_timestamp']}";
					    echo 	$datetime->format('m/d/y, H:i:s');
					    echo 	"</td>";
					    echo "</tr>";
				    }
				} else {
				    echo "<h5>No files Available</h5>";
				}

				?>
			</table>


		</div>

	</div>
	<?php include("../../foot.inc.php") ?>
</body>
</html>

<!-- SELECT `files`.* FROM `files` join `files_password` ON `files`.`id` = `files_password`.`files_id` WHERE `files_password`.`password_id` = 2 -->

<!-- SELECT `files`.`id`, `files`.`file_name` 
FROM `files` join `files_password` 
ON `files`.`id` = `files_password`.`files_id` 
WHERE `files_password`.`password_id` = 2 -->

<!-- SELECT `files`.`id`, `files`.`file_name`, `files`.`full_path`, `files`.`upload_timestamp` 
FROM `files` join `files_password` 
ON `files`.`id` = `files_password`.`files_id` 
WHERE `files_password`.`password_id` = 2 -->