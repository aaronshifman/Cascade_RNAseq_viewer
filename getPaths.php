<?php
include_once './connection.php';
$pdo = connect();
$stmt = $pdo->prepare('SELECT pathway_id,pathway_name,type FROM pathway,path_types where path_types.type_id = pathway_group;');
$stmt->execute(array());


$echo = array();
foreach ($stmt as $row) {
    $stmt2 = $pdo->prepare('SELECT gene FROM genes WHERE pathway_id= $id');
    $stmt2->execute(array('id'=> $row['pathway_id']));

    $genes='';
    foreach($stmt2 as $row2){
        $genes.=$row2['gene'].",";
    }
    $row['genes'] = $genes;
    array_push($echo,$row);
}
echo json_encode($echo);
die();
?>