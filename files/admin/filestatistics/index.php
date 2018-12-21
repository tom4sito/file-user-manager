<?php
session_start();
require("../../../config.inc.php");

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

$sql_file = "SELECT DISTINCT file_name FROM downloads";
$result = mysqli_query($conn, $sql_file);

function renderFile($sql_result, $link){
	if(mysqli_num_rows($sql_result) >= 0){
		foreach ($sql_result as $key => $value) {
			$path_formated = explode("/", $value['file_name']);
			$file_name = array_slice($path_formated, -1);
			$file_path = array_slice($path_formated, 5, -1);
			$file_path = "/".implode("/", $file_path);

			$sql_file_id = "SELECT file_id FROM downloads WHERE file_name = '{$value['file_name']}' LIMIT 1";
			$result_id = mysqli_query($link, $sql_file_id);
			$file_id = mysqli_fetch_assoc($result_id);

			$sql_count = "SELECT COUNT(*) FROM downloads  WHERE file_id = '{$file_id['file_id']}' ";
			$result_count = mysqli_query($link, $sql_count);
			$download_count = mysqli_fetch_assoc($result_count);

			$sql_users_id = "SELECT DISTINCT user_id FROM `downloads` WHERE file_id = '{$file_id['file_id']}'";
			$user_id_arr = [];
			$result_user_id = mysqli_query($link, $sql_users_id);
			while($row = mysqli_fetch_assoc($result_user_id)) {
			    $user_id_arr[] = $row["user_id"];
			}

			$users_ids = implode(",", $user_id_arr);
			$sql_users_name = "SELECT username, id FROM password WHERE id IN ($users_ids)";
			$result_users_name = mysqli_query($link, $sql_users_name);

			echo "<tr class='statistics-tr'>";
			echo 	"<td class='statistics-td'>{$file_name[0]}</td>";
			echo 	"<td class='statistics-td'>{$file_path}</td>";
			echo 	"<td class='statistics-td'>".$download_count["COUNT(*)"]."</td>";
			echo 	"<td class='statistics-td'>";
			echo 		"<h5 class='show-users'>Show</h5>";
			echo 		"<div class='file-users'>";
			while($row = mysqli_fetch_assoc($result_users_name)) {
				echo 		"<h6 class='userdownload' userid='{$row["id"]}' filetarget='{$file_name[0]}' ";
				echo 		"filetargetid='{$file_id['file_id']}'>";
			    echo 			$row["username"];
			    echo 		"</h6>";
			}
			echo 		"</div>";
			echo 	"</td>";
			echo "</tr>";
		}
	}
}

?>

<!DOCTYPE html>
<html>
<head>
	<?php include("../../../head.inc.php"); ?>
</head>
<body>
	<div class="body">
		<?php
			// TOP NAVBAR
			include("../../../header.inc.php");
		?>
		<div class="main-container">
			<h2>File Statistics</h2>
			<h5 class="message"></h5>
			<table>
				<tr class="statistics-tr">
					<th class="statistics-td">File Name</th>
					<th class="statistics-td">File Path</th>
					<th class="statistics-td">Number of Downloads</th>
					<th class="statistics-td">Who Downloaded This File</th>
				</tr>

				<?php renderFile($result, $conn); ?>
			</table>

			<!-- create dir modal ------------------------------------------------------------------->
			<div class="modal fade" id="downloadDateModal" tabindex="-1" role="dialog" aria-labelledby="createDirLabel" aria-hidden="true">
			  <div class="modal-dialog" role="document">
			    <div class="modal-content">
			      <div class="modal-header">
			        <h5 class="modal-title"><span class="file-name"></span></h5>
			        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
			          <span aria-hidden="true">&times;</span>
			        </button>
			      </div>
			      <div class="modal-body">
			      	<div class="download-dates">
			      		
			      	</div>
			      </div>
			      <div class="modal-footer">
			        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
			      </div>
			    </div>
			  </div>
			</div>
		</div>

	</div>
	<?php include("../../../foot.inc.php") ?>
	<script type="text/javascript">
		//hide users who downloaded the file
		$(".file-users").hide();

		// //show hide users on click
		$(".show-users").on("click", function(){
			if($(this).hasClass("hide-users")){
				$(this).next().hide();
				$(this).removeClass("hide-users");
				$(this).text("Show");
			}
			else{
				$(this).next().show();
				$(this).addClass("hide-users");
				$(this).text("Hide");
			}
		});

		// populate download modal 
		$(".userdownload").on("click", function(){
			$(".file-name").text($(this).attr("filetarget")+" Downloads By " + $(this).text());
			ajaxDownloadDates($(this).attr("filetargetid"), $(this).attr("userid"));
		});

		$(document).ready(function(){
			// display update message
			var message = "<?php echo $message ?>";
			$(".message").text(message);
			$(".message").addClass("success-delete");
		});

		function ajaxDownloadDates(fileId, userId){
			$.ajax({
				url: "fetchdates.php",
				method: "get",
				data: "fileid=" + fileId +"&userid=" + userId,
				dataType: "JSON",
				success: function(data){
					if(data){
						$('#downloadDateModal').modal('show');
						renderDownloadDates(data);
					}
				}
			}).fail(function() {
    			alert( "looks like this file was not downloaded by this user." );
 		 });
		}

		function renderDownloadDates(datesArray){
			$(".download-dates").empty();
			var dates_html = "";
			datesArray.forEach(function(date){
				dates_html += "<h5>";
				dates_html += date;
				dates_html += "</h5>";
			});
			console.log(dates_html);
			$(dates_html).appendTo(".download-dates");
		}
	</script>
</body>
</html>