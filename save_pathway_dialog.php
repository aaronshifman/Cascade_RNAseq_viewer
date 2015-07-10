Path Name<input type="text" id='path_name'/><br/>
Research Group<input type="text" id='group_name'/><br/>
Type<select id="path_type">
    <?php
    
include_once './connection.php';
$pdo = connect();

    $stmt = $pdo->prepare("SELECT * FROM path_types");
    $stmt->execute();
    foreach ($stmt as $row){
        ?> <option id = '<?php echo $row['type_id']; ?>'><?php echo $row['type']; ?> </option><?php
    }
    ?>
</select>
<input type="button" value ="save" id="save_button"onclick="save_nodes()"/>
<script>
    function save_nodes() {
        $.post("save_pathway.php", {file: origNodes, name: $("#path_name").val() + "_" + $("#group_name").val(), group: $('#path_type :selected').attr("id")}, function(data) {
            $("#save_button").replaceWith(data)
        })
    }
</script>