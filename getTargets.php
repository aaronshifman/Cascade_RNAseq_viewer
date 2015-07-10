<?php
include_once './connection.php';
$pdo = connect();
$stmt = $pdo->prepare('SELECT * FROM custom_targets');
$stmt->execute(array());

$echo = array();
foreach ($stmt as $row) {
    $stmt2 = $pdo->prepare('SELECT gene FROM target_list WHERE disease_id= :id');
    $stmt2->execute(array('id' => $row['disease_id']));
    $genes = '';
    foreach ($stmt2 as $row2) {
        $genes.=$row2['gene'] . ",";
    }
    $row['genes'] = $genes;
    array_push($echo, $row);
}
echo json_encode($echo);
?>