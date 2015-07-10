<?php

include_once './connection.php';
$pdo = connect();
$stmt = $pdo->prepare("INSERT INTO `pathway` (`pathway_name`, `pathway_group`) VALUES(:name, :group)");
$stmt->execute(array('name' => $_POST['name'], 'group' => $_POST['group']));
$id = $pdo->lastInsertId();

$success = true;
if ($stmt) {
    $lines = explode("\r\n", $_POST['file']);
    for ($i = 1; $i < count($lines); $i++) {
        $lines[$i] = rtrim($lines[$i], ",");
        $parts = explode(',', $lines[$i]);
        $gns = "";
        for ($j = 1; $j < count($parts) - 2; $j++) {
            $gns.=$parts[$j] . ",";
        }
        if ($parts[count($parts) - 2] !== $parts[0])
            $gns.=$parts[count($parts) - 2];
        if ($parts[0] != "") {
            $stmt2 = $pdo->prepare("INSERT INTO genes (`pathway_id`,`gene`,`children`,`node_type`)VALUES(" . $id . ",:p0,:gns,:p1)");
            $stmt2->execute(array('p0'=>$parts[0],'gns'=>$gns,':p1'=>$parts[count($parts) - 1]));
            if (!stmt2) {
                echo "Fail";
                die();
            }
        }
    }
    if ($success)
        echo "Success";
}else {
    echo "Failed to Save... what did you do?????....";
}
?>
