<?php
session_start();
require("../../config.inc.php");

error_reporting(-1);
ini_set('display_errors', TRUE);

if(!isset($_SESSION['username'])){
    header("location: /{$PROJECT_FOLDER}/login");
    die("user not logged in");
}

if(!($_SESSION['usertype'] == "admin" OR $_SESSION['usertype'] == "superadmin")){
	header("Location:  /{$PROJECT_FOLDER}/files/client");
	die("you do not have permission to view this page");
}


$config = parse_ini_file("/var/www/html/db.ini");

$conn = mysqli_connect("localhost", $config['username'], $config['password'], $config['db'] );

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

//handles the removal of files
if(isset($_POST['delete'])){
	$message = "";
	if(!empty($_POST['files']))
	{
		foreach ($_POST['files'] as $key => $value) {
			// echo $value."<br>";
			$file_path = "/var/www/html/uploads/".$value;
			// echo $file_path."<br>";
			if(unlink($file_path)){
				echo $value." deleted successfully<br>";

				$sql = "SELECT `id` FROM `files` WHERE `full_path` = '$file_path' ";
				$result = mysqli_query($conn, $sql);

				if(mysqli_num_rows($result) > 0){
					$row = mysqli_fetch_assoc($result);
					$sql = "DELETE FROM `files_password` WHERE `files_id` =  '{$row['id']}'";

					if(mysqli_query($conn, $sql)){
						$sql = "DELETE  FROM `files` WHERE `id` = '{$row['id']}' ";
						if(mysqli_query($conn, $sql)){
							// $message .= "$value file deleted from db";
							$message .= " $value deleted,";
							// header("Location: ./?message=$message");
							// die($message);
						}
						else{
							$message .= " $value fail,";
							// $message = "file could not be deleted from db". mysqli_error($conn);
							// header("Location: ./?message=$message");
							// die($message);
						}
					}
					else{
						$message .= " $value diss,";
						// $message = "$file could not be dissasociated from user to complete deletion". mysqli_error($conn);
						// header("Location: ./?message=$message");
						// die($message);
					}

				}
				else{
					$message .= " $value noexists,";
					// $message = "Such file does not exist in the database";
					// header("Location: ./?message=$message");
					// die($message);
				}
			}
			else{
				$message .= " $value unexpected,";
				// $message =  "unexpected error while deleting the file(s)";
				// header("Location: ./?message=$message");
				// die($message);
			}
		}
	}
	else{
		$message .= "No files were selected to delete";
		header("Location: ./?message=$message");
		die($message);
	}

	header("Location: ./?message=$message");
	die($message);
}

//handles the assignment of files to users
if(isset($_POST['assign'])){
	$message = "";
	if(!empty($_POST['files'])){

		foreach ($_POST['files'] as $key => $value) {
			// echo $value." - from assign <br>";
			$file_path = "/var/www/html/uploads/".$value;
			// echo $file_path."<br>";

			$sql = "SELECT `id` FROM `files` WHERE `full_path` = '$file_path' ";
			$result = mysqli_query($conn, $sql);

			if(mysqli_num_rows($result) > 0){
				$row = mysqli_fetch_assoc($result);
				// echo "id: " . $row["id"] . "<br>";
				// echo "user id: " . $_POST['sel_user'];

				// $sql_check_dup = "SELECT `password_id` FROM  `files_password` 
				// WHERE `files_id` = '{$row["id"]}' AND `password_id` = '{$_POST['sel_user']}' ";

				$sql_assign = "INSERT INTO `files_password` (files_id, password_id) VALUES ('{$row['id']}', '{$_POST['sel_user']}')";

				if(mysqli_query($conn, $sql_assign)){
					$message .=  " $value assigned,";
					echo $message."<br>";
				}
				else{
					if (strpos(mysqli_error($conn), 'Duplicate') !== false) {
					    $message .= " $value duplicate,";
					}
					else{
						$message .=  " $value failed,";
					}
					echo $message."<br>";
				}
			}
			else{
				$message .=  " $value noexist,";
				echo $message."<br>";
			}

		}
	}
	else{
		$message = "No files were selected to assign";
		header("Location: ./?message=$message");
	}

	header("Location: ./?message=$message");
	die($message);
}

?>