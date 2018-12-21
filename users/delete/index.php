<?php
session_start();
require("../../config.inc.php");
error_reporting(-1);
ini_set('display_errors', TRUE);

if(!isset($_SESSION['username'])){
    header("location: /{$PROJECT_FOLDER}/login/");
    die("redirected to login page");
}

if(!($_SESSION['usertype'] == "admin" || $_SESSION['usertype'] == 'superadmin')){
	header("Location: /{$PROJECT_FOLDER}/files/client");
	die("redirected to client files");
}


// gets database credentials
$config = parse_ini_file("/var/www/html/db.ini");

// create connection
$conn = mysqli_connect("localhost", $config['username'], $config['password'], $config['db']);

//check connection
if(!$conn) {
	die("connection failed: " . mysqli_connect_error());
}

$message = "";

if(isset($_POST['delete'])){
	$user_id = $_POST['sel_user'];
	$sql = "DELETE FROM `files_password` WHERE `password_id` = '$user_id' ";

	if(mysqli_query($conn, $sql)){
		$sql = "DELETE FROM `password` WHERE `id` = '$user_id' ";
		if(mysqli_query($conn, $sql)){
			$message = "user was deleted successfully";
		}
		else{
			$message =  "user could not be deleted";
		}
	}
	else{
		$message = "files associated with the userid $user_id could not be detached";
	}
}

if($_SESSION['usertype'] == "superadmin"){
	$sql = "SELECT * FROM `password` ";
}
elseif($_SESSION['usertype'] == "admin"){
	$sql = "SELECT * FROM `password` 
	WHERE `usertype` <> 'superadmin' AND `usertype` <> 'admin' ";
}

// $sql = "SELECT * FROM `password`";
$result = mysqli_query($conn, $sql);

function displayUsers($users){
	if(mysqli_num_rows($users) >= 0){
		foreach ($users as $key => $value) {
			echo "<option value='{$value['id']}'>";
			echo $value['username'];
			echo "</option>";
		}
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<?php include("../../head.inc.php"); ?>
</head>
<body>
	<div class="body">
		<?php
			// TOP NAVBAR
			include("../../header.inc.php");
		?>
		<div class="main-container">
			<h2>Delete User</h2>
			<h5 class="message"></h5>
			<form action="" method="post" id="delete-user-form">
				<select name="sel_user">
					<?php displayUsers($result) ?>
				</select>
				<input type="submit" name="delete" class="btn btn-danger" value="delete" id="deleteUserBtn">
			</form>
		</div>

		
	</div>
	<?php include("../../foot.inc.php") ?>
	<script type="text/javascript">
		$("#deleteUserBtn").on('click', function(){
			if(confirm("are you sure you want to delete this user")){
				$("#deleteUserBtn").submit();
			}
			else{
				console.log("User deletion cancelled");
				return false;
			}
		});

		$(document).ready(function(){
			// display update message
			var message = "<?php echo $message ?>";
			$(".message").text(message);
			$(".message").addClass("success-delete");
		});
	</script>
</body>
</html>