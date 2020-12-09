<?php
// Configuration parameters
// Hashed (sha256) password to access control panel
$access_password = "15e559ae3df4f3d8cce60e4324880aa84a0325969686a2b42325f722acdbbb9a";
// Database connection parameters
$db_server = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "my_galielo";
// Editable fields. An associative array, in which each key is the name of a table.
// Each element is itself an associative array, in which each key is the name of an
// editable field and its value is the shown name, plus the '__name' field with the
// shown name of the table and the '__unique' field with the name of the unique field
// in the table. An * at the end of the shown name signal visible-but-non-editable fields.
// As a choice of the author, no removing of rows is available
$tables = array(
    "giocatori" => array(
        "__name" => "Lista giocatori",
        "__unique" => "ID",
        "Nome" => "Nome",
        "PuntiA" => "PuntiA*",
        "PuntiD" => "PuntiD*"
    ),
    "ccup" => array(
        "__name" => "Coppe dei Campioni",
        "__unique" => "ID",
        "Hidden" => "Nascosto?"
    )
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
    <form class="col-md-4 mr-0 ml-auto" method="post">
        <input type="hidden" id="logout" name="logout">
        <button type="submit" class="btn btn-primary">Logout</button>
    </form>
    <a href="?"><button class="btn btn-primary col-md-4">Home</button></a>
HTML;

// If no table selected, show the list of available tables to edit
if( !array_key_exists('table',$_GET) ) {
    echo <<<HTML
        <div class="card col-md-4 mx-auto">
        Prego selezionare la tabella che si vuole modificare<br/>
HTML;
    foreach( array_keys($tables) as $t ) {
        $name = 0;
        echo <<<HTML
        <br/><a href="?table=$t"><button class="btn btn-primary">{$tables[$t]['__name']}</button></a>
HTML;
    }
    exit();
}
$table = $_GET['table'];

// Connect to database
$db_handle = mysqli_connect($db_server, $db_username, $db_password);
echo mysqli_error($db_handle);
mysqli_select_db($db_handle, $db_name);
echo mysqli_error($db_handle);

// If no row selected, show the list of available rows
if( !array_key_exists('row',$_GET) ) {
    $result = mysqli_query($db_handle,'SELECT * FROM '.$table);
    echo mysqli_error($db_handle);
    echo  <<<HTML
    <div class="card col-md-4 mx-auto">
    Prego selezionare la riga che si vuole modificare<br/>
    <table><tr><th>Edit</th>
HTML;
    // TODO: magari qui è utile mostrare __tutti__ i campi
    foreach( array_keys($tables[$table]) as $field )
        if( ! strpos(".".substr($field,0,2),"__") )
            echo "<th>".$tables[$table][$field]."</th>";
    echo "</tr>";
    while( $row = mysqli_fetch_assoc($result) ) {
        echo "<tr><td><a href=\"?table=".$table."&row=".$row[$tables[$table]['__unique']]."\">".$row[$tables[$table]['__unique']]."</a></td>";
        foreach( array_keys($tables[$table]) as $field )
            if( ! strpos(".".substr($field,0,2),"__") )
                echo "<td>".$row[$field]."</td>";
        echo "<tr>";
    }
    echo "</table>";
    exit();
}
echo "<br/><a href=\"?table=".$table."\"><button class=\"btn btn-primary col-md-4\">".$tables[$table]['__name']."</button></a>";

// If it's a module saving, save the data
if( array_key_exists('__table',$_POST) ) {
    $sql = "UPDATE ".$_POST['__table']." SET ";
    foreach( array_keys($tables[$table]) as $field )
        if( ! strpos(".".substr($field,0,2),"__") )
            if( strpos( substr($tables[$table][$field], -1), "*") === false )
                $sql .= $field." = '".mysqli_real_escape_string($db_handle,$_POST[$field])."', ";
    $sql = substr($sql,0,-2);
    $sql .= " WHERE ".$tables[$table]['__unique']." = ".$_POST['__row'];
    if ( mysqli_query($db_handle,$sql) ) {
        header('Location: ?table='.$table);
    } else {
        echo mysqli_error($db_handle);
    }
    exit();
}

// If row selected, show the row
$row = $_GET['row'];
$result = mysqli_query($db_handle,'SELECT * FROM '.$table.' WHERE '.$tables[$table]['__unique'].' = '.$_GET['row']);
echo mysqli_error($db_handle);
$thisrow = mysqli_fetch_assoc($result);
echo "<form class=\"card col-md-4 mx-auto\" method=\"post\">";
foreach( array_keys($tables[$table]) as $field )
    if( ! strpos( ".".substr($field,0,2), "__" ) ) {
        if( strpos( substr($tables[$table][$field], -1), "*") === false )
            echo <<<HTML
                <label for="$field">{$tables[$table][$field]}</label>
                <input type="text" class="form-control" id="$field" name="$field" value="{$thisrow[$field]}" >
HTML;
        else echo <<<HTML
            <p><b>{$tables[$table][$field]}:</b> {$thisrow[$field]}</p>
HTML;
    } 
echo <<<HTML
    <input type="hidden" id="__table" name="__table" value="$table">
    <input type="hidden" id="__row" name="__row" value="$row">
    <button type="submit" class="btn btn-primary">Salva</button>
    </form>
HTML;

?>
