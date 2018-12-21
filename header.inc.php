
<nav class="navbar navbar-expand-md navbar-dark bg-dark">
	<a class="navbar-brand" href="#">ADDC</a>
	<!-- <img src="<?php echo $BASE_URL ?>$PROJECT_FOLDER /images/logo-gray.png"> -->
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
	<span class="navbar-toggler-icon"></span>
	</button>
	<div class="collapse navbar-collapse" id="navbarCollapse">
		<ul class="navbar-nav mr-auto">
<!-- 			<li class="nav-item active">
			 	<a class="nav-link" href="/uploads/">Home <span class="sr-only">(current)</span></a>
			</li> -->
			<?php
			if(isset($_SESSION['username'])){
				// echo "<li class='nav-item'>";
				// echo 	"<a class='nav-link' href='{$BASE_URL}$PROJECT_FOLDER/uploads'><i class='fas fa-upload'></i> Uploads</a>";
				// echo "</li>";
				if($_SESSION['usertype'] == "admin" || $_SESSION['usertype'] == "superadmin"){
				// echo "<li class='nav-item'>";
				// echo 	"<a class='nav-link' href='{$BASE_URL}$PROJECT_FOLDER/files'><i class='fas fa-file'></i> File Manager</a>";
				// echo "</li>";
				echo "<li class='nav-item dropdown'>";
					echo "<a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>";
						echo "<i class='fas fa-file'></i> Files";
					echo "</a>";
					echo "<div class='dropdown-menu' aria-labelledby='navbarDropdown'>";
						echo "<a class='dropdown-item' href='{$BASE_URL}$PROJECT_FOLDER/files'>Manage Files</a>";
						echo "<a class='dropdown-item' href='{$BASE_URL}$PROJECT_FOLDER/files/admin/myfiles'>My Files</a>";
						echo "<a class='dropdown-item' href='{$BASE_URL}$PROJECT_FOLDER/files/admin/filestatistics'>Files Statistics</a>";
					echo "</div>";
				echo "</li>";

				echo "<li class='nav-item dropdown'>";
					echo "<a class='nav-link dropdown-toggle' href='#' id='navbarDropdown' role='button' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>";
						echo "<i class='fas fa-users-cog'></i> Users Manager";
					echo "</a>";
					echo "<div class='dropdown-menu' aria-labelledby='navbarDropdown'>";
						echo "<a class='dropdown-item' href='{$BASE_URL}$PROJECT_FOLDER/users/create'>Create Users</a>";
						echo "<a class='dropdown-item' href='{$BASE_URL}$PROJECT_FOLDER/users/delete'>Delete Users</a>";
						echo "<a class='dropdown-item' href='{$BASE_URL}$PROJECT_FOLDER/users/edit'>Edit Users</a>";
						echo "<a class='dropdown-item' href='{$BASE_URL}$PROJECT_FOLDER/users/view'>View Users</a>";
						echo "<a class='dropdown-item' href='{$BASE_URL}$PROJECT_FOLDER/users/loginrecord'>Login Record</a>";
						// echo "<div class='dropdown-divider'></div>";
						// echo	"<a class='dropdown-item' href='#'>Something else here</a>";
					echo "</div>";
				echo "</li>";
				}
				else{
					echo "<li class='nav-item'>";
					echo	"<a class='nav-link' href='{$BASE_URL}$PROJECT_FOLDER/files/client'><i class='fas fa-file'></i> Files</a>";
					echo "</li>";
				}

			}
			?>

		</ul>
		<ul class="navbar-nav ml-auto">
			<li class="nav-item">
				<?php
				if(!isset($_SESSION['username'])){
					echo "<a class='nav-link' href='{$BASE_URL}$PROJECT_FOLDER/login'><i class='fas fa-sign-in-alt'></i> Login</a>";
				}
				else{
					echo "<a class='nav-link' href='{$BASE_URL}$PROJECT_FOLDER/logout'><i class='fas fa-sign-in-alt'></i> Logout</a>";
				}
				?>
			</li>
		</ul>
	</div>
 </nav>
