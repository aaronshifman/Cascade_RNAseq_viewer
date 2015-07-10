<?php

function connect() {
    $server = "localhost";
    $username = "username";
    $password = "passwd";
    $db = "database_name";
    $pdo = new PDO("mysql:host=$server;dbname=$db",$username,$password);
//    mysql_connect($server, $username, $password);
//    mysql_select_db($db);
    
    return $pdo;
}
?>
