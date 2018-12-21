<?php 
session_start();
require("../config.inc.php");
error_reporting(-1);
ini_set('display_errors', TRUE);

if(isset($_SESSION['username'])){
    if($_SESSION['usertype'] != 'admin' || $_SESSION['usertype'] != 'superadmin' ){
        header("Location:  /{$PROJECT_FOLDER}/files/admin");
    }
    else{
        header("Location:  /{$PROJECT_FOLDER}/files/client");
    }
}


if(isset($_POST["submit"]) && !empty($_POST['u']) && !empty($_POST['p'])){

    $conn = mysqli_connect("localhost", "root", "asdfasdf", "app_db");
    if(!$conn){
        die("connection failed". mysqli_connect_error());
    }

    // $login_failed = false;

    $username = "";
    if(isset($_POST['u'])){
        $username = $_POST['u'];
    }

    $password = "";
    if(isset($_POST['p'])){
        $password = $_POST['p'];
    }

    $sql = "SELECT * FROM `password` WHERE `username` = '$username' ";

    $result = mysqli_query($conn, $sql);
    $user_info = mysqli_fetch_assoc($result);

    if($user_info['usertype'] == 'temporary'){
        if($user_info['login_num'] <= 0){
            $message="user ran out of logins, contact the system administrator for more logins";
            header("location: ./?message=".$message);
            die($message);
        }
    }

    if(strlen($username) > 0 && strlen($password) > 0){
        if( strtolower($username) == $user_info['username']){
            if($password == $user_info['password']){
                $_SESSION['user_id'] = $user_info['id'];
                $_SESSION['username'] = $user_info['username'];
                $_SESSION['usertype'] = $user_info['usertype'];

                if($user_info['usertype'] == 'temporary'){
                    if($user_info['login_num'] > 0){
                        $sql = "UPDATE `password` SET `login_num` = `login_num` - 1 WHERE `id` = '{$user_info['id']}' ";
                        echo $sql;
                        mysqli_query($conn, $sql);
                    }
                }

                $sql = "INSERT INTO `login_tracking` (user_id, user_name) VALUES ('{$user_info['id']}', '{$user_info['username']}')";
                mysqli_query($conn, $sql);

                echo "successfully logged in";
                header('Location: ../');
            }
            else{
                $message = "Wrong user name or password";
                header("Location: ./?message=$message");
                die($message);
                // $login_failed = true;
            }
        }
        else{
            $message =  "Wrong user name or password";
            header("Location: ./?message=$message");
            die($message);
            // $login_failed = true;
        }
    }

}
?>

<html>
    <head>
        <?php include("../head.inc.php"); ?>
        <style type="text/css">
            .form-class{
                margin-top: 40px;
            }
            label{
                font-size: 20px;
                font-weight: bold;
                margin-bottom: .2rem;
            }          
        </style>
    </head>
    <body>
        <div class="body">
             <?php
                // TOP NAVBAR
                include("../header.inc.php");
             ?>
             <div class="container h-100">
                <div class="row h-100 justify-content-center">
                    

                    <form action="" method="post" class="form-class">
                        <div class="message danger-message"></div>
                        <div class="form-group">
                            <label for="u">USERNAME:</label><br>
                            <input type="text" name="u" size="40" id="u"><br>
                        </div>
                        <div class="form-group">
                            <label for="p">PASSWORD:</label><br>
                            <input type="password" name="p" size="40" id="p"><br>
                        </div>

                        <input type="submit" name="submit" class="btn btn-primary">
                    </form>
                 </div>
             </div>

        </div>
        <?php include("../foot.inc.php") ?>

        <script type="text/javascript">
            $(document).ready(function(){
                var message = decodeURI(getUrlParam("message"));
                if(message != 'undefined'){
                    $(".message").text(message);
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
