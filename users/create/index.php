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


if(isset($_POST['submit'])){
	if(!isset($_POST['user_name']) || trim($_POST['user_name']) == '' ||
		!isset($_POST['password']) || trim($_POST['password']) == '' ||
		!isset($_POST['user_type']) || trim($_POST['user_type']) == ''){
			$message = "unsuccessful user creation, either the password or username were blank.";
			header("location: ./?message=".$message);
			die($message);
	}

	$username =  trim($_POST['user_name']);
	$password =  trim($_POST['password']);
	$usertype =  trim($_POST['user_type']);
	$login_qty = trim($_POST['login_qty']);

	if((strlen($username) < 6) || (strlen($password) < 8) ){
		$message = "user creation unsuccessful, <br>username needs to be at least 6 characters long and password 8.";
		header("location: ./?message=".$message);
		die($message);
	}

	// gets database credentials
	$config = parse_ini_file("/var/www/html/db.ini");

	// create connection
	$conn = mysqli_connect("localhost", $config['username'], $config['password'], $config['db']);

	//check connection
	if(!$conn) {
		die("connection failed: " . mysqli_connect_error());
	}

	// checks for duplicate users
	$sql_dup_user = "SELECT * FROM password WHERE `username` = '{$username}' ";
	$user_result =  mysqli_query($conn, $sql_dup_user);
	if(mysqli_num_rows($user_result) >= 1){
		$message = "unsuccessful action, user '".$username.
		"' already exists please pick a different username";
		header('location: ./?message='.$message);
		die("user already exists: ");
	}
	
	if(($usertype == "temporary") AND (empty($_POST['login_qty']))){
		$message =  "Login quantity was not provided for the temporary user";
		echo "$message";
		header('location: ./?message='.$message);
		die($message);
	}

	if(isset($_POST['login_qty']) AND (!empty($_POST['login_qty']))){
		if($usertype == "temporary"){
			$sql = "INSERT INTO password (username, password, usertype, login_num)
			VALUES ('$username', '$password', '$usertype', '$login_qty')";

			if (mysqli_query($conn, $sql)) {
			    $message =  "New user ($username) created successfully";
			    header('location: ./?message='.$message);
			    die($message);
			} 
			else {
			    $message =  "User creation unsuccessful, please try again. db query";
			    header('location: ./?message='.$message);
			}
		}
		else {
			$message =  "User creation unsuccessful, please try again. post = temporary";
			header('location: ./?message='.$message);
		}

	}
	else{
		$sql = "INSERT INTO password (username, password, usertype, login_num)
		VALUES ('$username', '$password', '$usertype', '1000000')";

		if (mysqli_query($conn, $sql)) {
		    $message =  "New user ($username) created successfully";
		    header('location: ./?message='.$message);
		} 
		else {
		    $message =  "User creation unsuccessful, please try again.";
		    header('location: ./?message='.$message);
		}
	}

	mysqli_close($conn);
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
			<h2>Create New User <i class="fas fa-user-plus"></i></h2><br>

			<h5 class="message new-user"></h5>

			<form action="" method="post" id="create-user-form">
				<h4>
					<label class="labeltag" for="u">Username: </label>
					<input type="text" name="user_name" id="u" size="10">
				</h4>
				<h4>
					<label class="labeltag" for="p">Password:&nbsp;</label>
					<input type="password" name="password" id="p" size="10">
				</h4>

				<h4>
					<label class="labeltag" for="t">User Type: </label>
					<select name="user_type" id="t">
					  <option value="client">client</option>
					  <option value="temporary">temporary</option>  
					  <?php
					  if($_SESSION['usertype'] == 'superadmin'){
					  	echo "<option value='admin'>admin</option>";
					  }
					  ?>
					  
					</select>
				</h4>

				<h4 id="login_qty" >
					<label class="labeltag" for="login_qty">Number of Logins: </label>
					<input type="number" name="login_qty"  min="1" max="100">
				</h4>
				<input type="submit" name="submit" class="btn btn-primary" id="create-user-btn" value="Create">
			</form>
		</div>

		
	</div>
	<?php include("../../foot.inc.php") ?>
	<script type="text/javascript">
		$( document ).ready(function(){
			$("#login_qty").hide();
			$("#t").change(function(){
				if($("#t").val() == "temporary"){
					// console.log("you selected temporary");
					$("#login_qty").show();
				}
				else if($("#t").val() != "temporary"){
					$("input[name='login_qty']").val('');
					$("#login_qty").hide();
				}
			});


			var message = decodeURI(getUrlParam('message'));
			if ((message > "" ) && (message != "undefined")) {
				$(".message").text(message);
			} else {
			    console.log("no message available");
			}

		});

		// gets url parameters
		function getUrlParam(name) {
		    var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
		    return (results && results[1]) || undefined;
		}
	</script>
</body>
</html>