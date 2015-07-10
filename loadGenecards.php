<?php
$gene = $_GET['gene'];

include_once './connection.php';
$pdo = connect();
$stmt = $pdo->prepare('select Entrez_Gene from Gene WHERE Gene_Name = :gene');
$stmt->execute(array('gene' => $gene));

foreach($stmt as $row){
    $id = $row[0];
}
$url ="http://ncbi.nlm.nih.gov/gene/".$id;
$html= file_get_contents($url);
echo $html;
?>
