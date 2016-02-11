<?php

include_once './connection.php';
$pdo = connect();
$stmt = $pdo->prepare('SELECT gene,children,node_type FROM genes WHERE pathway_id = :id');
$stmt->execute(array('id' => $_GET['path']));

$nodes = "header\n";
foreach($stmt as $row) {
    $nodes.= $row[0].",".$row[1].",".$row[2]."\n";
}
echo $nodes
?>