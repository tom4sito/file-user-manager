<?php
session_start();
require("../../config.inc.php");

error_reporting(-1);
ini_set('display_errors', TRUE);

if(!isset($_SESSION['username'])){
    header("location: /{$PROJECT_FOLDER}/login");
}

if(!($_SESSION['usertype'] == "admin" OR $_SESSION['usertype'] == "superadmin")){
	header("Location:  /{$PROJECT_FOLDER}/files/client");
}

$directory    = '/var/www/html/uploads';

$config = parse_ini_file("/var/www/html/db.ini");
$conn = mysqli_connect("localhost", $config['username'], $config['password'], $config['db'] );
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$sql = "SELECT * FROM `password`";
$result = mysqli_query($conn, $sql);


// creates an array with all file extension available and their (enable) status
$extensions_panel = [];
$sql_file_ext = "SELECT `file_extension`, `enabled` FROM `allowed_file_types` ORDER BY `file_extension` ASC";
$result_file_ext = mysqli_query($conn, $sql_file_ext);
if (!$result_file_ext) { die("File Extension Query Failed.".mysqli_error($conn)); }
while($row = mysqli_fetch_array($result_file_ext)){ $extensions_panel[] = $row; }

// adds file extension to database
if(isset($_POST['addExtSubmit'])){
	$sql_add_ext = "INSERT INTO `allowed_file_types` (`file_extension`, `file_type`, `enabled`)
	SELECT * FROM (SELECT '{$_POST['extName']}', '{$_POST['selExtType']}', 1) AS tmp
	WHERE NOT EXISTS (
    SELECT `file_extension` FROM `allowed_file_types` 
    WHERE `file_extension` = '{$_POST['extName']}') LIMIT 1";

	if(mysqli_query($conn, $sql_add_ext)){

		$num = mysqli_affected_rows($conn);
		echo "rows affected: $num";
		if($num > 0){
			$message = "extension added successfully";
		}
		else{
			$message = "extension already exists";
		}
		header("Location: ./?message=$message");
		die($message);
		
	}
	else{
		$message = "extension could not be added";
		header("Location: ./?message=$message");
	}
	die($message);
}

// enable extensions--------------------------------
if(isset($_POST['enablextension'])){
	$message = "";
	foreach($_POST['file-ext'] as $key => $value){
		$sql = "UPDATE `allowed_file_types` 
		SET `enabled` = 1 
		WHERE `file_extension` = '$value' ";

		if (mysqli_query($conn, $sql)) {
			$message .= "{$value}: enabled, ";
		}
		else{
			$message .= "{$value}: error, ";
		}
	}

	header("Location: ./?message=$message");

}

// disable extensions--------------------------------
if(isset($_POST['disablextension'])){
	$message = "";
	foreach($_POST['file-ext'] as $key => $value){
		$sql = "UPDATE `allowed_file_types` 
		SET `enabled` = 0 
		WHERE `file_extension` = '$value' ";

		if (mysqli_query($conn, $sql)) {
			$message .= "{$value}: disabled, ";
		}
		else{
			$message .= "{$value}: error, ";
		}
	}

	header("Location: ./?message=$message");

}


// file uploads handler --------------------------------------
if(isset($_FILES['fileup'])){
	$errors = array();
	$file_name = $_FILES['fileup']['name'];
	$file_size = $_FILES['fileup']['size'];
	$file_tmp = $_FILES['fileup']['tmp_name'];
	$file_type = $_FILES['fileup']['type'];

	// $overwrite = $_POST['overwrite'];
	$file_path = $_POST['upload_input_path'];

	if($file_path == "/"){
		$full_path = "/var/www/html/uploads/".$file_name;
		$file_db_path = "/var/www/html/uploads/";
	}
	else{
		$full_path = "/var/www/html/uploads/".$file_path."/".$file_name;
		$file_db_path = "/var/www/html/uploads/".$file_path;
	}

	$tmp_name =explode('.',$_FILES['fileup']['name']);
	$file_ext = strtolower(end($tmp_name));

	$extensions = [];

	// pull only the enabled file extensions from the DB
	$sql_file_ext = "SELECT `file_extension` 
	FROM `allowed_file_types`
	WHERE `enabled` = 1
	ORDER BY `file_extension` ASC";
	$result_file_ext = mysqli_query($conn, $sql_file_ext);
	if (!$result_file_ext) { die("File Extension Query Failed."); }
	while($row = mysqli_fetch_array($result_file_ext))
	{
	    $extensions[] = $row["file_extension"];
	}


	if(!isset($_POST['overwrite'])){
		if(file_exists("/var/www/html/uploads/".$file_name)){
			$errors[] = "Unsuccessful upload, a file with this name already exists";
		}
		if(file_exists($full_path)){
			$errors[] = "Unsuccessful upload, a file with this name already exists";
		}
	}

	if(in_array($file_ext, $extensions) === false){
		$errors[]= "Unsuccessful upload file type not allowed";
	}

	if($file_size > 100000000){
		$errors[]='File size must be less than 100MB';
	}

	if(empty($errors)==true){
		$move_file_status = move_uploaded_file($file_tmp, $full_path);
		if($move_file_status){
			
			$sql = "INSERT INTO `files` (file_name, file_path, full_path) 
			VALUES ('$file_name', '$file_db_path', '$full_path')";

			if (mysqli_query($conn, $sql)) {
				$message = "$file_name file uploaded successfully";
			    header('location: ./?message='.$message);
			    die($message);
			} 
			else {
				$message = "$file_name file was uploaded but info was not stored in database";
				// $message = mysqli_error($conn);
			    header('location: ./?message='. $message);
			    die($message);
			}
		}
		else {
			$message = "$file_name file could not be uploaded";
			header("Location: ./?message=".$message);
			die($message);
		}
	}
	else{
		header("Location: ./?message={$errors[0]}");
		die("not a valid extension");
	}


}


function displayUsers($users){
	if(mysqli_num_rows($users) >= 0){
		foreach ($users as $key => $value) {
			echo "<option value='{$value['id']}'>";
			echo $value['username'];
			echo "</option>";
		}
	}
}

function displayFileExtensions($extensions_array){
	foreach ($extensions_array as $key => $value) {
		echo "<tr>";
		if($value['enabled'] == 0){
			echo "<td>";
			echo "<input type='checkbox' ";
			echo "name='file-ext[]' ";
			echo "value='{$value['file_extension']}' ";
			echo "isenabled='{$value['enabled']}'> ";
			echo "</td><td>";
			echo "{$value['file_extension']}";
			echo "</td><td>";
			echo "<span class='disabled-ext'> disabled</span>";
			echo "</td>";
		}
		else{
			echo "<td>";
			echo "<input type='checkbox' ";
			echo "name='file-ext[]' ";
			echo "value='{$value['file_extension']}' ";
			echo "isenabled='{$value['enabled']}'> ";
			echo "</td><td>";
			echo "{$value['file_extension']}";
			echo "</td><td>";
			echo "<span class='enabled-ext'> enabled</span>";
			echo "</td>";
		}
		echo "</tr>";
	}
}


function listFolderFiles($dir){
    $folder_files = scandir($dir);

    unset($folder_files[array_search('.', $folder_files, true)]);
    unset($folder_files[array_search('..', $folder_files, true)]);


    // prevent empty ordered elements
    if (count($folder_files) < 1)
        return;

    foreach($folder_files as $ff){
    	if(is_dir($dir.'/'.$ff)){
    		$dir_path = $dir.'/'.$ff;
    		$dir_path = explode("/", $dir_path);
    		$dir_path = array_slice($dir_path, 5);
    		$dir_path = implode("/", $dir_path);

    		echo "<li>";
    		echo "<i class='fas fa-folder'></i><span class='directory context-menu-one' data-toggle='collapse' href='#{$ff}' aria-expanded='false' aria-controls='{$ff}' path='$dir_path'> ";
    		// echo $ff." <i class='fas fa-caret-down'></i> </span>";
    		echo $ff." </span>";
    		// echo "<i class='fas fa-folder-plus' path='$dir_path' data-toggle='modal' data-target='#createDirModal' title='click to create new folder'></i> ";
    		// echo "<input type='text' class='display dir-name'>";
    		// echo "<span  class='create-dir-btn display $dir_path'>Create Directory</span>";
    		// echo "<span  class='create-dir-btn display' path='$dir_path'>Create Directory</span>";

    		// echo "<i class='fas fa-folder-minus $dir_path'></i> ";
    		// echo "<i class='fas fa-folder-minus' path='$dir_path' title='click here to remove folder'></i> ";
    		// echo "<i class='fas fa-file-upload $dir_path' path='$dir_path' data-toggle='modal'";
    		// echo "data-target='#uploadModal' title='click here to upload file in this folder'></i>";
    		echo "<ul id='$ff' class='collapse dir-ul' >";

    		listFolderFiles($dir.'/'.$ff);
    		echo "</ul>";
    	}
    	else{
    		$dir_path = $dir.'/'.$ff;
    		$dir_path = explode("/", $dir_path);
    		$dir_path = array_slice($dir_path, 5);
    		$dir_path = implode("/", $dir_path);

    		echo "<li class='file'><input type='checkbox' name='files[]' value='$dir_path' > <i class='far fa-file'></i> {$ff}  ";
    	}

        echo '</li>';
    }
}


?>

<!DOCTYPE html>
<html>
<head>
	<?php include("../../head.inc.php"); ?>
	<link rel="stylesheet" type="text/css" href="<?php echo $BASE_URL ?><?php echo $PROJECT_FOLDER ?>/css/jquery.contextMenu.min.css">
	<style type="text/css">
/*		.fa-file-upload {
		    color: #007bff;
		    cursor: pointer;
		}*/
	</style>
</head>
<body>
	<div class="body">
		<?php
			// TOP NAVBAR
			include("../../header.inc.php");
		?>

		<div class="main-container">
			<h2>UPLOAD DELETE AND ASSIGN FILES</h2>

			<div class="row">
				<div class="col-md-auto root-tree-panel">
					<!-- <h2>UPLOAD DELETE AND ASSIGN FILES</h2> -->
					<form action="delete_assign_file.php" method="post">
						<i class='fas fa-folder-open'></i>
						<span class='directory context-menu-one'> Root Directory</span>
							<ul id="file-tree" class='dir-ul'>
							<?php listFolderFiles($directory); ?>
							</ul>

						<div class="row">
							<div class="col-3 col-sm-3 col-md-3 del-btn-div">
								<button type="submit" class="btn btn-danger" name="delete">
								Delete</button>
							</div>

							<div class="col-9 col-sm-9 col-md-9" id="assignfiles">
								<div class="assign-to-user">
									<button type="submit" class="btn btn-primary" name="assign">
									Assign File(s) to</button>
									<i class="fas fa-arrow-right"></i>
									<select name="sel_user">
										<?php displayUsers($result) ?>
									</select>
								</div>
							</div>

						</div>


					</form>
				</div>


				<?php if($_SESSION['usertype'] == "superadmin"){ ?>
				<!-- file extension manager interface only seen by superadmin-->
				<div class="col-md-auto">
					
					<div class="file-extensions-panel">
						<h3>
						  <a class="btn btn-primary" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
						    Edit File Extensions 
						    <i class="fas fa-caret-down"></i>
						  </a>
						</h3>
						<div class="collapse" id="collapseExample">
						  <div class="card card-body">
						  	<form action="" method="post">
						  		<table id="extensions-table">
						  		<?php displayFileExtensions($extensions_panel); ?>
						  		</table>
						  		<div class="extension-btns">
						  			<button type="button" class='btn btn-primary' path='' data-toggle='modal' data-target='#addExtModal' title='click to add file extension'>
						  				Add
						  			</button>
						  			<input class="btn btn-success" type="submit" name="enablextension" value="enable">
						  			<input class="btn btn-danger" type="submit" name="disablextension" value="disable">
						  		</div>
						  	</form>
						  </div>
						</div>
					</div>
				</div>
				<?php } ?>

				<!-- messages ----------------------->
				<div class="col-md-auto">
					<!-- callback messages -->
					<ul class="message">	
					</ul>
				</div>


				<!-- upload modal ----------------------------------------->
				<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
				  <div class="modal-dialog" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title">Upload File</h5>
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				        <div class="upload-container col-md-auto">
				        	<h4>Upload to this directory path: </h4>
				        	<h5><i class='fas fa-folder'></i>: /<span id="upload-path-id"></span></h5>
				        	

				        	<form action="" method="post" enctype="multipart/form-data" id="uploadform">
				        		<input type="file" name="fileup" /><br><br>
				        		<input type="hidden" value="placeholder" name="upload_input_path" id="upload-path"/>
				        		<input type="checkbox" name="overwrite" value="true"> Check this checkbox <b>only</b> if you wish to <b>overwrite</b> an existing file.<br><br>

				        	</form>
				        </div>
				      </div>
				      <div class="modal-footer">
				      		<button type="button" class="btn btn-primary" id="uploadfilebtn">Upload</button>
				        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
				      </div>
				    </div>
				  </div>
				</div>

				<!-- create dir modal ------------------------------------------------------------------->
				<div class="modal fade" id="createDirModal" tabindex="-1" role="dialog" aria-labelledby="createDirLabel" aria-hidden="true">
				  <div class="modal-dialog" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title">Create New Folder</h5>
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				        <div class="upload-container col-md-auto">
				        	<h4>New Folder Name</h4>
				        	<i class='fas fa-folder create-folder'> </i> <input type='text' class='dir-name' id="dir-name-id"><br><br>
				        	<!-- <button id="createDirBtn" path="" class="btn btn-primary">Create</button> -->
				        </div>
				      </div>
				      <div class="modal-footer">
				      	<button type="button" class="btn btn-primary" id="createDirBtn" path="">Create</button>
				        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
				      </div>
				    </div>
				  </div>
				</div>

				<!-- add extension modal ------------------------------------------------------------------->
				<div class="modal fade" id="addExtModal" tabindex="-1" role="dialog" aria-labelledby="addExtModalLabel" aria-hidden="true">
				  <div class="modal-dialog" role="document">
				    <div class="modal-content">
				      <div class="modal-header">
				        <h5 class="modal-title">Add Extension</h5>
				        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
				          <span aria-hidden="true">&times;</span>
				        </button>
				      </div>
				      <div class="modal-body">
				        <div class="upload-container col-md-auto">
				        	<form action="" method="post" id="extensionform">
				        		<label>Extension Name:</label>
				        		<input type="text" name="extName"><br>
				        		<label>Extension Type:</label>
				        		<select name="selExtType">
				        			<option value="unknown">unknown</option>
				        			<option value="document">document</option>
				        			<option value="image">image</option>
				        			<option value="compressed">compressed</option>
				        			<option value="binary">binary</option>
				        			<option value="data">data</option>
				        			<option value="executable">executable</option>
				        			<option value="text">text</option>
				        		</select><br>
				        		<input type="hidden" name="addExtSubmit">
				        	</form>
				        </div>
				      </div>
				      <div class="modal-footer">
				      	<button type="button" class="btn btn-primary" id="addExtBtn">Add</button>
				        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
				      </div>
				    </div>
				  </div>
				</div>
				
			</div>

		</div>

	</div>
	<?php include("../../foot.inc.php") ?>
	<script src="<?php echo $BASE_URL ?><?php echo $PROJECT_FOLDER ?>/js/jquery.ui.position.min.js"></script>
	<script src="<?php echo $BASE_URL ?><?php echo $PROJECT_FOLDER ?>/js/jquery.contextMenu.min.js"></script>
</body>
<script type="text/javascript">

	// display callback messages
	$( document ).ready(function() {
		var message = decodeURI(getUrlParam('message'));
		message = message.split(',');
		message = message.filter(Boolean);

		if ((message > "" ) && (message != "undefined")) {
		    message.forEach(function(e){
		    	console.log(e.trim());
		    	if(e.includes("duplicate")){
		    		$(".message").append("<li class='duplicate'>"+e.trim()+" <i class='fas fa-times'></i></li>");
		    		$(".message").fadeOut(9000);
		    	}
		    	else if(e.includes("assigned")){
		    		$(".message").append("<li class='assigned'>"+e.trim()+" <i class='fas fa-check'></i></li>");
		    		$(".message").fadeOut(9000);
		    	}
		    	else if(e.includes("deleted")){
		    		$(".message").append("<li class='deleted'>"+e.trim()+" <i class='fas fa-check'></i></li>");
		    		$(".message").fadeOut(9000);
		    	}
		    	else if(e.includes("enabled")){
		    		$(".message").append("<li class='enabled'>"+e.trim()+"</li>");
		    		$(".message").fadeOut(9000);
		    	}
		    	else if(e.includes("disabled")){
		    		$(".message").append("<li class='disabled'>"+e.trim()+"</li>");
		    		$(".message").fadeOut(9000);
		    	}
		    	else{
		    		$(".message").append("<li class=''>"+e.trim()+"</li>");
		    		$(".message").fadeOut(9000);
		    	}
		    	
		    });
		    
		} else {
		    console.log("no message available");
		}
	});

	// executes ajax function tha creates directory
	$("#createDirBtn").click(function(){
		$dir_path = $(this).attr('path') + "/" +$('#dir-name-id').val();
		ajaxCreateDir($dir_path);
	});

	// ajax function handles creation of new directory
	function ajaxCreateDir(className){
		$.ajax({
			url: "createdir.php",
			method:"GET",
			data: "path=" + className,
			dataType:"text",
			success:function(data){
				if(data){
					// clears url parameters
					var url = window.location.href.replace(window.location.search,'');  
					if(data.includes("exists")){
						url += "?message=this folder name already exists or provided name is blank.";
						window.location.href = url;
					}else{
						url += "?message=" + data;
						window.location.href = url;
					}

				}
				else{
					console.log("ajax unsuccessful");
				}
			}
		}).fail(function() {
    		alert( "error folder name blank" );
 		 });
	}

	function ajaxDeleteDir(className){
		$.ajax({
			url: "deletedir.php",
			method:"GET",
			data: "path=" + className,
			dataType:"text",
			success:function(data){
				var url = window.location.href.replace(window.location.search,'');
				url += "?message=folder%20deleted";
				window.location.href = url;
				console.log("ajax unsuccessful");
			}
		});
	}


	// updates folder icon status
	$(".directory").on("click", function(){
		if($(this).attr("aria-expanded") == "true"){
			console.log($(this).prev().attr("class"));
			$(this).prev().removeClass("fa-folder-open");
			$(this).prev().addClass("fa-folder");
		}
		if($(this).attr("aria-expanded") == "false"){
			console.log($(this).prev().attr("class"));
			$(this).prev().removeClass("fa-folder");
			$(this).prev().addClass("fa-folder-open");
		}

	});

	// submits form that uploads selected file -----------------------
	$("#uploadfilebtn").on("click", function(){
		$("#uploadform").submit();
	});

	// submit file extension form -------------------
	$("#addExtBtn").on("click", function(){
		$("#extensionform").submit();
	});




	// gets url parameters
	function getUrlParam(name) {
	    var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
	    return (results && results[1]) || undefined;
	}

	// CONTEXT MENU -------------------------------------------
	$(function() {
	    $.contextMenu({
	        selector: '.context-menu-one', 
	        items: {
	            "uploadfile": {
	            	name: "upload file",
	            	icon: "fas fa-file-upload",
	            	callback: function(e, opt) {
	            		$("#upload-path-id").text(opt.$trigger.attr("path"));
	            		$("#upload-path").val(opt.$trigger.attr("path"));
	            		$("#uploadModal").modal("show");
	            	}
	            },
	            "sep1": "---------",
	            "createnewfolder": {
	            	name: "new folder", 
	            	icon: "fas fa-folder-plus",
		            callback: function(e, opt){
		            	$("#createDirModal").modal("show");
		            	$("#createDirBtn").attr("path", opt.$trigger.attr("path"));
		            }
	        	},
	            "deletefolder": {
	            	name: "delete folder",
	            	icon: "fas fa-folder-minus",
	            	callback: function(e, opt){
	            		if(confirm("Are you sure you want to delete this folder?")){
	            			ajaxDeleteDir(opt.$trigger.attr("path"));
	            		}
	            	}
	            }
	        }
	    });   
	});

</script>
</html>