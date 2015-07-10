<?php
include_once './connection.php';
$pdo = connect();
$stmt = $pdo->prepare('SELECT gene FROM target_list WHERE disease_id= :id');
//$rows = mysql_query('SELECT gene FROM target_list WHERE disease_id='.$_GET['id']);
$stmt->execute(array('id' => $_GET['id']));
$echo = "";
foreach ($stmt as $row) {
    $echo.=$row['gene']."\n";
}
echo $echo;
?>