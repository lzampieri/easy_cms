<?php

// Configuration parameters
$access_password = "15e559ae3df4f3d8cce60e4324880aa84a0325969686a2b42325f722acdbbb9a";
$db_server = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "my_galielo";
$tables = array(

);

// Bootstrap head
?>
<html>
<head>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
</head><body class="container-fluid text-center">
<?php

// Check if login
session_start();
if( array_key_exists('plain_password',$_POST) ) {
    $hashed_password = hash("sha256",$_POST['plain_password']);
    if( $hashed_password == $access_password ) {
        $_SESSION['access_password'] = $hashed_password;
    } else {
        echo <<<HTML
        <div class="alert col-md-4 alert-danger mx-auto" role="alert">
            Password errata!
        </div>
HTML;
    }
}
if( !array_key_exists('access_password',$_SESSION) || $_SESSION['access_password'] != $access_password ) { echo <<<HTML
    <form class="card col-md-4 mx-auto" method="post">
        <label for="plain_password">Password:</label>
        <input type="password" class="form-control" id="plain_password" name="plain_password">
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
HTML;
    exit();
}
if( array_key_exists('logout',$_POST) ) {
    session_destroy();
    header('Location: '.$_SERVER['REQUEST_URI']);
    exit(); 
}
echo <<<HTML
    <form class="card col-md-4 mr-0 ml-auto" method="post">
        <input type="hidden" id="logout" name="logout">
        <button type="submit" class="btn btn-primary">Logout</button>
    </form>
HTML;
?>
Accesso garantito
