<?php
ini_set('display_errors', 1);
$data = json_decode($_POST['json'], true);
include_once './connection.php';
$pdo = connect();
$in = implode("','", $data['genes']);
$sql = "select * from " . $data['source'] . "rpkm where Gene IN('$in')";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$patients = array();
foreach ($stmt as $row) {
    $keys = array_keys($row);
    for ($i = 4; $i < count($keys); $i += 2) {
        $patient = $keys[$i];
        $gene = $row[$keys[2]];
        if (!array_key_exists($patient, $patients)) {
            $patients[$patient] = array();
        }
        if (!array_key_exists($gene, $patients[$patient])) {
            $patients[$patient][$gene] = array();
        }
        $patients[$keys[$i]][$gene]['expression'] = $row[$patient];
    }
}
$sql = "select * from " . $data['source'] . "mut where Gene IN('$in')";
$stmt = $pdo->prepare($sql);
$stmt->execute();

##Binary structure: 0: no mut, 1: non dmg SNP, 2: dmg SNP, 4: INDEL
foreach ($stmt as $row) {
    $keys = array_keys($row);
    for ($i = 4; $i < count($keys); $i += 2) {
        $patient = $keys[$i];
        $gene = $row[$keys[2]];
        $bin_val = $row[$patient];
        $patients[$keys[$i]][$gene]['INDEL'] = 0;
        $patients[$keys[$i]][$gene]['newSNP'] = 0;
        $patients[$keys[$i]][$gene]['dmgSNP'] = 0;
        if ($bin_val - 4 >= 0) {
            $patients[$keys[$i]][$gene]['INDEL'] = 1;
            $bin_val = $bin_val - 4;
        }
        if ($bin_val - 2 >= 0) {
            $patients[$keys[$i]][$gene]['dmgSNP'] = 1;
            $bin_val = $bin_val - 2;
        }
        if ($bin_val - 1 >= 0) {
            $patients[$keys[$i]][$gene]['newSNP'] = 1;
            $bin_val = $bin_val - 2;
        }
    }
}
echo json_encode($patients);
?>