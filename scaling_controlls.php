<script type="text/javascript">
    function onSlide(e) {
        var id = ($(e.currentTarget).attr("id"))
        var columns = $(e.currentTarget).find("td");
        var ranges = [], total = 0, i, s = "Ranges: ", w;
        for (i = 0; i < columns.length; i++) {
            w = columns.eq(i).width() - 10 - (i == 0 ? 1 : 0);
            ranges.push(w);
            total += w;
        }
        for (i = 0; i < columns.length; i++) {
            ranges[i] = 100 * ranges[i] / total;
        }
        if (id === "cnv") {
            s += " <" + Math.round(ranges[0]) + "%";
        } else {
            s += " <" + Math.round(ranges[0]) + "%,";
            s += Math.round(ranges[0]) + "% - " + (Math.round(ranges[1]) + Math.round(ranges[0])) + "%,";
            s += ">" + (Math.round(ranges[1]) + Math.round(ranges[0])) + "%";
        }
        $("#text-" + id).html(s);
        scales[id][0] = Math.round(ranges[0])
        if (id !== "cnv") {

            scales[id][1] = (Math.round(ranges[1]) + Math.round(ranges[0]))
            scales[id][2] = (Math.round(ranges[1]) + Math.round(ranges[0]) + Math.round(ranges[2]))
        }
        update_colors()
        $(".scale-indicator-" + id.substring(0, 3)).eq(1).html(Math.round(ranges[0]) + "%");
        $(".scale-indicator-" + id.substring(0, 3)).eq(2).html(Math.round(ranges[0]) + Math.round(ranges[1]) + "%");
    }

    //colResize the table
    $("#mutated").colResizable({disable: true})
    $("#mutated").colResizable({
        liveDrag: true,
        draggingClass: "rangeDrag",
        gripInnerHtml: "<div class='rangeGrip'></div>",
        onDrag: onSlide,
        minWidth: 8
    });
    $("#altsplice").colResizable({disable: true})
    $("#altsplice").colResizable({
        liveDrag: true,
        draggingClass: "rangeDrag",
        gripInnerHtml: "<div class='rangeGrip'></div>",
        onDrag: onSlide,
        minWidth: 8
    });
    $("#cnv").colResizable({disable: true})
    $("#cnv").colResizable({
        liveDrag: true,
        draggingClass: "rangeDrag",
        gripInnerHtml: "<div class='rangeGrip'></div>",
        onDrag: onSlide,
        minWidth: 8
    });
</script>

<div class="center">
    <br/><br/>
    Mutations
    <div class="slider">
        <table class="range" id="mutated" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td width="<?php echo $_GET['m1']; ?>%"></td>
                <td width="<?php echo $_GET['m2']; ?>%"></td>
                <td width="<?php echo $_GET['m3']; ?>%"></td>
            </tr>
        </table>	
    </div>
    <p class="text" id="text-mutated">Ranges: <<?php echo $_GET['m1']; ?>%,<?php echo $_GET['m1']; ?>% - <?php echo $_GET['m2'] + $_GET['m1']; ?>%,><?php echo $_GET['m2'] + $_GET['m1']; ?>%</p>
</div>	

<div class="center">
    <br/><br/>
    Alt-Splicing
    <div class="slider">
        <table class="range" id="altsplice" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td width="<?php echo $_GET['as1']; ?>%"></td>
                <td width="<?php echo $_GET['as2']; ?>%"></td>
                <td width="<?php echo $_GET['as3']; ?>%"></td>
            </tr>
        </table>	
    </div>
    <p class="text" id="text-altsplice">Ranges: <<?php echo $_GET['as1']; ?>%,<?php echo $_GET['as1']; ?>% - <?php echo $_GET['as2'] + $_GET['as1']; ?>%,><?php echo $_GET['as2'] + $_GET['as1']; ?>%</p>
</div>
<div class="center">
    <br/><br/>
    CNVs
    <div class="slider">
        <table class="range" id="cnv" width="100%" cellspacing="0" cellpadding="0">
            <tr>
                <td width="<?php echo $_GET['cnv1']; ?>%"></td>
                <td width="<?php echo 100 - $_GET['cnv1']; ?>%"></td>
            </tr>
        </table>	
    </div>
    <p class="text" id="text-cnv">Ranges: <<?php echo $_GET['cnv1']; ?>%</p>
</div>