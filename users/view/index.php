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

// handles form that detaches files from users
if(isset($_POST['submitfileform'])){
	$filtered_arr = array_filter($_POST['files']);
	if(empty($filtered_arr)){
		$message = "you did not selected any files to be detached.";
		header("Location: ./?message=$message");
		die($message);
	}
	$ids = join(', ', $filtered_arr);
	$sqlDetachFiles = "DELETE FROM `files_password` WHERE  files_id IN ($ids)";
	if(mysqli_query($conn, $sqlDetachFiles)){
		$message = "files detached successfully";
		header("Location: ./?message=$message");
		die($message);
	}
	else{
		$message =  "problem encountered when detaching files.";
		header("Location: ./?message=$message");
		die($message);
	}
}

if($_SESSION['usertype'] == "superadmin"){
	$sql = "SELECT `id`, `username`, `usertype`, `login_num` FROM `password` ";
}
else{
	$sql = "SELECT `id`, `username`, `usertype`, `login_num` 
	FROM `password` 
	WHERE `usertype` = 'client' OR `usertype` = 'temporary'";
}

$result = mysqli_query($conn, $sql);

function renderUsers($usersArr){
	if(mysqli_num_rows($usersArr) > 0){
		foreach ($usersArr as $key => $value) {
			echo "<tr class='view-tr'>";
			echo 	"<td class='view-td'>";
			echo 		$value['username'];
			echo 	"</td>";
			echo 	"<td class='view-td'>";
			echo 		$value['usertype'];
			echo 	"</td>";
			echo 	"<td class='view-td'>";
			echo 		$value['login_num'];
			echo 	"</td>";
			echo 	"<td class='view-td'>";
			echo 		"<p class='show-files-btn' userid='{$value['id']}'>Show User Files</p>";
			echo 	"</td>";
			echo "</tr>";
		}
	}

}
?>

<!DOCTYPE html>
<html>
<head>
	<?php include("../../head.inc.php"); ?>
	<style type="text/css">
		.show-files-btn{
			cursor: pointer;
		}
		.users-list-container {
		    -ms-flex: 0 0 600px;
		    flex: 0 0 600px;
		}
		.dismiss-form{
			cursor: pointer;
		}
		.show-files-btn {
			color: #007bff;
		}
	</style>
</head>
<body>
	<div class="body">
		<?php
			// TOP NAVBAR
			include("../../header.inc.php");
		?>
		<div class="main-container container-fluid">
			<h2>View Users</h2><br>
			<h5 class="message new-user"></h5>
			<div class="row">
				<div class="users-list-container ">
					<table>
						<tr class="view-tr">
							<th class="view-td">Username</th>
							<th class="view-td">User Type</th>
							<th class="view-td">Logins Remaining</th>
							<th class="view-td">Files</th>
						</tr>
						<?php renderUsers($result); ?>
					</table>
				</div>
				<div class="files-panel" id="filepanel">

				</div>
			</div>
		</div>

		
	</div>
	<?php include("../../foot.inc.php") ?>
	<script type="text/javascript">
		$( document ).ready(function(){
			$(".files-panel").hide();

			// displays url message
			var message = decodeURI(getUrlParam('message'));
			if ((message > "" ) && (message != "undefined")) {
				$(".message").text(message);
				$(".message").fadeOut(6000);
			} else {
			    console.log("no message available");
			}

		});


		$(".show-files-btn").on("click", function(){
			$(".files-panel").show();
			ajaxUserFiles($(this).attr("userid"));
		});


		//fetch user files
		function ajaxUserFiles(userid){
			$.ajax({
				url: "fetchfiles.php",
				type: "get",
				data: "userid="+ userid,
				dataType: "JSON",
				success: function(data){
					if(data){
						console.log(data[0][1]);
						createFileForm(data);
					}
				}

			}).fail(function() {
    			alert( "error, looks like user has no files." );
    			clearFileForm();
 		 });
		}

		//populate file form
		function createFileForm(userfiles){
			$(".files-panel").empty();
			var formhtml = "<form action='' method='post' id='userfileform' >";

			userfiles.forEach(function(file){
				console.log(file[1]);
				formhtml += "<input classs='checkboxclass' type='checkbox' name='files[]' value='"+file[0]+"' >";
				formhtml += "<span> "+file[1]+"</span><br>";
			});

			formhtml += "<div class='form-btns-container'>";
			formhtml += "<button class='btn btn-danger dismiss-form'>Cancel</button>";
			formhtml += "<input class='btn btn-primary' type='submit' name='submitfileform' value='Detach Files'>";
			formhtml += "</div>";
			formhtml += "</form>";

			$(formhtml).appendTo(".files-panel");
		}

		// clear form if user has no files
		function clearFileForm(){
			$(".files-panel").empty();
		}

		//dismiss form
		$(".dismiss-form").on("click", function(){
			$("#filepanel").empty();
		});

		// gets url parameters
		function getUrlParam(name) {
		    var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
		    return (results && results[1]) || undefined;
		}
	</script>
</body>
</html>