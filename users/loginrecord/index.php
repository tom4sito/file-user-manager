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

// records per page limit
$limit = 20;  
if (isset($_GET["page"])) { 
	$page  = $_GET["page"]; 
} else { 
	$page=1; 
}

$start_from = ($page-1) * $limit;   
// $sql = "SELECT * FROM posts ORDER BY title ASC LIMIT $start_from, $limit";

// $rs_result = mysqli_query($conn, $sql);  

// fetchs login records
if($_SESSION['usertype'] == 'superadmin'){
	$sql = "SELECT user_name, login_date, usertype 
	FROM login_tracking 
	JOIN password ON 
	login_tracking.user_id = password.id
	ORDER BY login_date DESC
	LIMIT $start_from, $limit";
}
else{
	$sql = "SELECT user_name, login_date, usertype 
	FROM login_tracking 
	JOIN password ON 
	login_tracking.user_id = password.id
	WHERE password.usertype <> 'superadmin'
	ORDER BY login_date DESC 
	LIMIT $start_from, $limit";
}

$results = mysqli_query($conn, $sql);

function renderUsersLogin($loginrecords){
	if(mysqli_num_rows($loginrecords) > 0){
		while($row = mysqli_fetch_assoc($loginrecords)){
			echo "<tr class='view-tr'>";
			echo 	"<td class='view-td'>";
			echo 		$row['user_name'];
			echo 	"</td>";
			echo 	"<td class='view-td'>";
			echo 		$row['usertype'];
			echo 	"</td>";
			echo 	"<td class='view-td'>";
			echo 		$row['login_date'];
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
	.pagination{
		margin-top: 10px;
	}
	.pages{
		padding-right: 10px;
		font-size: 20px;
		color: #6c757d;
	}
	.active{
		font-weight: bold;
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
			<h2>Login Records</h2><br>
			<h5 class="message"></h5>
			<div class="row">
				<table>
					<tr class='view-tr'>
						<th class='view-td'>User Name</th>
						<th class='view-td'>User Type</th>
						<th class='view-td'>Login Date</th>
					</tr>

					<?php renderUsersLogin($results) ?>
				</table>
			</div>
			<div>
				<?php
				if($_SESSION['usertype'] == "superadmin"){
					$sqlcount = "SELECT COUNT(password.id) FROM login_tracking
					JOIN password ON login_tracking.user_id = password.id";
				}
				else{
					$sqlcount = "SELECT COUNT(password.id) FROM login_tracking
					JOIN password ON login_tracking.user_id = password.id 
					WHERE password.usertype <> 'superadmin' ";
				}


				$rs_result = mysqli_query($conn, $sqlcount);  
				$row = mysqli_fetch_row($rs_result); 

				$total_records = $row[0];  
				$total_pages = ceil($total_records / $limit);  
				$pagLink = "<div class='row pagination'>";  
				for ($i=1; $i<=$total_pages; $i++) {  
				             $pagLink .= "<a class='pages' href='index.php?page=".$i."'>".$i."</a>";  
				};  
				echo $pagLink . "</div>";  
				?>
			</div>
		</div>

		
	</div>
	<?php include("../../foot.inc.php") ?>
	<script type="text/javascript">

		// gets url parameters
		function getUrlParam(name) {
		    var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
		    return (results && results[1]) || undefined;
		}

		
		var current_page = getUrlParam("page");
		if(!current_page) current_page = 1;


		// console.log(current_page);
		// $current_page = $_GET["page"];
		$(".pagination a").each(function(){
			if(current_page == $(this).text()){
				$(this).addClass("active");
			}
		})

	</script>
</body>
</html>