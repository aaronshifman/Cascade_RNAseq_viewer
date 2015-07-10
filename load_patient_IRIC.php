<?php

include_once './connection.php';
$pdo = connect();
$src = mysql_real_escape_string($_POST['source']);

$genes = $_POST['genes'];
$inQuery = implode(',', array_fill(0, count($genes), '?'));

$stmt = $pdo->prepare("select * from " . $src . "rpkm where Gene IN(".$inQuery.")");

foreach ($genes as $k => $gene){
    $stmt->bindValue(($k+1), $gene);
}
$stmt->execute();

#$q = "select * from " . $_POST['source'] . "rpkm where Gene IN('" . implode("','", $_POST['genes']) . "')";
#$query = mysql_query($q);
foreach ($stmt as $row){
    for ($i = 4; $i < count($row); $i+=2) {
        $keys = array_keys($row);
        echo $keys[$i]."\n";
        // snps and damaging (known) snps
        $newSNP = 0;
        $DMG = 0;
        $newINDEL = 0;
        if ($_POST['newsnp'] == "true") { //non damaging
            $stmt2 = $pdo->prepare("select * from " . $src . "mut WHERE gene = '" . $row[$keys[2]] . "'");
            $stmt2->execute();
            foreach($stmt2 as $row2){
                $newSNP = bindec((string)$row2[$keys[$i]])==1?1:0;
            }
        }
        if ($_POST['knownsnp'] == "true") { //damaging snps
            $stmt2 = $pdo->prepare("select * from " . $src . "mut WHERE gene = '" . $row[$keys[2]] . "'");
            $stmt2->execute();
            foreach($stmt2 as $row2){
                $DMG = bindec((string)$row2[$keys[$i]])==2?1:0;
            }
        }
        if ($_POST['newindel'] == 'true') { 
            $stmt2 = $pdo->prepare("select * from " . $src . "mut WHERE gene = '" . $row[$keys[2]] . "'");
            $stmt2->execute();
            foreach($stmt2 as $row2){
                $newINDEL = bindec((string)$row2[$keys[$i]])==3?1:0;
            }
        }
        $snp = $newSNP;
        $dmg = $DMG;
        $indel = $newINDEL;
        //$echo .= $row[$keys[1]] . "," . $row['ID_Participant'] . "," . $row['Valeur_RPKM'] . "," . $snp . "," . $snpdmj . "," . $indel . ",0,0\n";
        // sequence is gene, patient, rpkm, snps, damaging snps, indels, splice, cnv
        // get data for alt splice
        $stmt2 = $pdo->prepare("select * from " . $src . "altsplice WHERE gene = '" . $row[$keys[2]] . "'");
        $stmt2->execute();
        foreach($stmt2 as $row2)
            $altsplice = $row2[$keys[$i]];

        //get cnv data
        $stmt2 = $pdo->prepare("select * from " . $src . "cnv WHERE gene = '" . $row[$keys[2]] . "'");
        $stmt2->execute();
        foreach($stmt2 as $row2)
            $cnv = $row2[$keys[$i]];

        $echo .= $row[$keys[2]] . "," . $keys[$i] . "," . $row[$keys[$i]] . "," . $snp . "," . $dmg . "," . $indel . "," . $altsplice . "," . $cnv . "\n";
    }
}
echo $echo;

function shif_intval($string) {
    if (is_int($string))
        return intval($string);
    else
        return $string;
}

?>
