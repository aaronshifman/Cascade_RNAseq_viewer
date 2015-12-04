<script src='js/spectrum.js'></script>
<link rel='stylesheet' href='css/spectrum.css' />

<?php
include './config.php';
?>
<table id="color_table">
    <thead>
        <tr>
            <td>Item</td>
            <td>Color</td>
            <td>Appearance</td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                Background Color
            </td>
            <td>
                <span id="bg_color"><?php echo $bg_color; ?></span>
            </td>
            <td>
                <input index="0" col_type="bg_color" dflt_color = "<?php echo $bg_color; ?>"type="text" class='basic'/>
            </td>
        </tr>
        <tr>
            <td>
                Family Color
            </td>
            <td>
                <span id="fam_color"><?php echo $fam_node_color; ?></span>
            </td>
            <td>
                <input index="1" col_type="fam_color" dflt_color = "<?php echo $fam_node_color; ?>"type="text" class='basic'/>
            </td>
        </tr>
        <tr>
            <td>
                Outlier Color
            </td>
            <td>
                <span id="out_color"><?php echo $out_color; ?></span>
            </td>
            <td>
                <input index="2" col_type="out_color" dflt_color = "<?php echo $out_color; ?>"type="text" class='basic'/>
            </td>
        </tr>
        <tr>
            <td>
                Regular Color
            </td>
            <td>
                <span id="reg_color"><?php echo $reg_color; ?></span>
            </td>
            <td>
                <input index="3" col_type="reg_color" dflt_color = "<?php echo $reg_color; ?>"type="text" class='basic'/>
            </td>
        </tr>
        <tr>
            <td>
                Ring Color
            </td>
            <td>
                <span id="ring_color"><?php echo $ring_color; ?></span>
            </td>
            <td>
                <input index="4" col_type="ring_color" dflt_color = "<?php echo $ring_color; ?>"type="text" class='basic'/>
            </td>
        </tr>
        <tr>
            <td>
                Name Color
            </td>
            <td>
                <span id="name_color"><?php echo $node_name_color; ?></span>
            </td>
            <td>
                <input index="5" col_type="name_color" dflt_color = "<?php echo $node_name_color; ?>"type="text" class='basic'/>
            </td>
        </tr>
        <tr>
            <td>
                Link Color
            </td>
            <td>
                <span id="link_color"><?php echo $link_color; ?></span>
            </td>
            <td>
                <input index="6" col_type="link_color" dflt_color = "<?php echo $link_color; ?>"type="text" class='basic'/>
            </td>
        </tr>
        <tr>
            <td>
                Low Color
            </td>
            <td>
                <span id="low_color"><?php echo $node_color_low; ?></span>
            </td>
            <td>
                <input index="7" col_type="low_color" dflt_color = "<?php echo $node_color_low; ?>"type="text" class='basic'/>
            </td>
        </tr>
        <tr>
            <td>
                Medium Color
            </td>
            <td>
                <span id="med_color"><?php echo $node_color_med; ?></span>
            </td>
            <td>
                <input index="8" col_type="med_color" dflt_color = "<?php echo $node_color_med; ?>"type="text" class='basic'/>
            </td>
        </tr>
        <tr>
            <td>
                High Color
            </td>
            <td>
                <span id="high_color"><?php echo $node_color_high; ?></span>
            </td>
            <td>
                <input index="9" col_type="high_color" dflt_color = "<?php echo $node_color_high; ?>"type="text" class='basic'/>
            </td>
        </tr>       
        <tr>
            <td>
                Font Size
            </td>
            <td>
                <input type="text" style="width:20px;" id="text_size" value="<?php echo $text_size; ?>"/>pt
            </td>
            <td></td>
        </tr>  
        <tr>
            <td>
                Outlier Threshold
            </td>
            <td>
                <input type="text" style="width:20px;" id="out_thresh" value="<?php echo $mml; ?>"/>
            </td>
            <td></td>
        </tr> 
    </tbody>
</table>

<script type="text/javascript">
    $(document).ready(function () {
        $.fn.dataTableExt.sErrMode = "throw";
        $("#color_table").dataTable({
            "bJQueryUI": true,
            "scrollY": "200px",
            "scrollCollapse": true,
            "paging": false
        });
        $("input[index]").each(function (idx, val) {
            $(this).attr('dflt_color', settings[$(this).attr("index")])
            $(this).parent().parent().children().eq(1).children().eq(0).text(settings[$(this).attr("index")])
        })
        $("#text_size").val(settings[10])
        $("#out_thresh").val(settings[11])
        $("#text_size").bind("change", function () {
            settings[10] = $("#text_size").val()
        });
        $("#out_thresh").bind("change", function () {
            settings[11] = $("#out_thresh").val()
        });
        $(".basic").each(function () {
            $(this).spectrum({
                color: "#" + $(this).attr('dflt_color'),
                change: function (color) {
                    val = color.toHexString().slice(1)
                    $("#" + $(this).attr('col_type')).text(val);
                    index = parseInt($(this).attr('index'))
                    settings[index] = val
                }
            });
        });
    });
</script>