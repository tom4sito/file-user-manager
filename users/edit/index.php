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

$message = "";

//check connection
if(!$conn) {
	die("connection failed: " . mysqli_connect_error());
}

if(isset($_POST['edit-user'])){
	if($_POST['edit-sel-usertype'] == 'temporary'){
		$sql = "UPDATE `password` SET `username`='{$_POST['edit-username']}',
		`password` = '{$_POST['edit-password']}',
		`usertype` = '{$_POST['edit-sel-usertype']}',
		`login_num`= '{$_POST['edit-numlogins']}' 
		WHERE `id` = '{$_POST['edit-user-id']}' ";
	}else{
		$sql = "UPDATE `password` SET `username`='{$_POST['edit-username']}',
		`password` = '{$_POST['edit-password']}',
		`usertype` = '{$_POST['edit-sel-usertype']}',
		`login_num`= 1000000 
		WHERE `id` = '{$_POST['edit-user-id']}' ";
	}


	if(mysqli_query($conn, $sql)){
		$message = "successful update";
	}
	else{
		echo "failed update".mysqli_error($conn);
	}

}

if($_SESSION['usertype'] == "superadmin"){
	$sql = "SELECT * FROM `password` ";
}
elseif($_SESSION['usertype'] == "admin"){
	$sql = "SELECT * FROM `password` 
	WHERE `usertype` <> 'superadmin' AND `usertype` <> 'admin' ";
}

$result = mysqli_query($conn, $sql);

function displayUsers($users){
	if(mysqli_num_rows($users) >= 0){
		foreach ($users as $key => $value) {
			echo "<option value='{$value['id']}' user-type='{$value['usertype']}' num-logins='{$value['login_num']}' user-pass='{$value['password']}'>";
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
			<h2>Edit Users <i class="fas fa-user-plus"></i></h2><br>

			<h5 class="message new-user"></h5>

			<select name="sel-user" id="sel-user">
				<option disabled selected value> -- select a user to edit -- </option>
				<?php displayUsers($result); ?>
			</select>

			<form action="" method="post" id="create-user-form">
				<div class="form-group">
					<label>Username:</label><br>
					<input type="text" name="edit-username">
				</div>
				<div class="form-group">
					<label>Password:</label><br>
					<input type="text" name="edit-password">
				</div>
				<div class="form-group">
					<label>User type:</label><br>
					<select id="edit-sel-usertype" name="edit-sel-usertype" >
						<option>admin</option>
						<option>client</option>
						<option>temporary</option>
					</select>
				</div>

				<div class="form-group" id="edit-login-container" >
					<label>Number of logins:</label><br>
					<input type="number" name="edit-numlogins">
				</div>

				<input type="hidden" name="edit-user-id" value="">
				<input type="submit" name="edit-user" class="btn btn-primary" id="create-user-btn" value="Update">
			</form>
		</div>

		
	</div>
	<?php include("../../foot.inc.php") ?>
	<script type="text/javascript">
		$( document ).ready(function(){
			$("#edit-login-container").hide();

			// shows number of login input if the user to edit is of temporary type
			$("#edit-sel-usertype").change(function(){
				if($("#edit-sel-usertype").val() == "temporary"){
					$("#edit-login-container").show();
				}
				else {
					$("input[name='edit-numlogins']").val('');
					$("#edit-login-container").hide();
				}
			});

			// display current user info on input fields
			$("#sel-user").change(function(){
				$(".message").text("");
				$("input[name='edit-username']").val($("#sel-user option:selected").text());
				$("input[name='edit-password']").val($("#sel-user option:selected").attr("user-pass"));
				$("#edit-sel-usertype").val($("#sel-user option:selected").attr("user-type"));
				$("input[name='edit-user-id']").val($("#sel-user option:selected").val());

				if($("#edit-sel-usertype").val() == "temporary"){
					$("#edit-login-container").show();
					$("input[name='edit-numlogins']").val($("#sel-user option:selected").attr("num-logins"));
				}
				else{
					$("input[name='edit-numlogins']").val('');
					$("#edit-login-container").hide();
				}
			});

			// display update message
			var message = "<?php echo $message ?>";
			$(".message").text(message);

		});

	</script>
</body>
</html>