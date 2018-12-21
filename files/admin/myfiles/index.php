<?php
session_start();
require("../../../config.inc.php");

error_reporting(-1);
ini_set('display_errors', TRUE);

if(!isset($_SESSION['username'])){
    header("location: /{$PROJECT_FOLDER}/login");
}

if(!($_SESSION['usertype'] == "superadmin" || $_SESSION['usertype'] == "admin")){
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
	<?php include("../../../head.inc.php"); ?>
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
			include("../../../header.inc.php");
		?>
		<div class="main-container">
			<h1>Files Available to Download</h1>
			<h5 class="message"></h5>
			<form action="download.php" method="post" id="downloadform">
				<input type='hidden' name='file' value="" id="inputfileid">
			</form>	
			<table>
				<tr>
					<th class="download-td">File Name</th>
					<th class="download-td">File Size</th>
					<th class="download-td">Upload Date</th>
				</tr>
				<?php
				if (mysqli_num_rows($result) > 0) {
				    while($row = mysqli_fetch_assoc($result)) {
				    	$file_size = filesize($row['full_path']);
				    	$datetime = DateTime::createFromFormat ( "Y-m-d H:i:s", $row["upload_timestamp"] );

				    	echo "<tr>";
				    	echo 	"<td class='download-td'>";
					    echo 		"<span class='file-link' fileid='{$row['id']}' >".$row['file_name']." </span>";
					    // echo 		"<input type='hidden' name='files[]' value='{$row['id']}'>";
					    echo 	"</td>";
					    echo 	"<td class='download-td'>";
					    echo 		" $file_size KB";
					    echo 	"</td>";
					    echo 	"<td class='download-td'>";
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
	<?php include("../../../foot.inc.php") ?>
	<script type="text/javascript">
		//updates input value when file is click and sends form
		$(".file-link").click(function(){
			$("#inputfileid").val($(this).attr("fileid"));
			$("#downloadform").submit();
		});

		// display error messages-------------------------
		$(document).ready(function(){
			var message = decodeURI(getUrlParam('message'));
			if(message.includes("not have rights")){
				$(".message").addClass("no-rights");
			}
			if ((message > "" ) && (message != "undefined")) {
				$(".message").text(message);
				$('.message').fadeIn(function () {
				    $(this).delay(2000).fadeOut(1600, function () {
				    });
				});
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
