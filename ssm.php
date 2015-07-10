<!--Outer container for the side bar-->
<div class="meny" style="position: fixed; display: block; z-index: 1; -webkit-transform-origin: 100% 50%; -webkit-transition: all 0.5s ease; transition: all 0.5s ease; -webkit-transform: translateX(-100%) translateX(6px) scale(1.01) rotateY(-30deg);">
    <h2>Select data types to display:</h2>
    <h3>Expression</h3>
    <div class="meny_subgroup"> 
        <input type="checkbox" id="medrpkm_toggle" checked><label for='medrpkm_toggle'>Median RPKM</label><br/>
        <span style="display: none" id="dataset_comp_label">Comparing to:</span>
        <span style="display: none" id="dataset_comp"></span>
        <h3>Splicing</h3>
        <input type="checkbox" id="splicing_toggle"><label for='splicing_toggle'>Alternate Splicing</label><br/>
        <h3>SNV/Mutation Data data</h3>          
        <input type="checkbox" id='newsnp_toggle' checked><label for='newsnp_toggle'class="opt_checkbox">Novel SNPs</label><br/>            
        <input type="checkbox" id='knownsnp_toggle'><label for='knownsnp_toggle'class="opt_checkbox">Known INDELs and Damaging SNPs</label><br/>
        <h3>  INDEL data</h3>         
        <input type="checkbox" id='newindel_toggle'><label for='newindel_toggle'class="opt_checkbox">Novel INDELs</label><br/>        
        <h3>  Copy Number data</h3>
        <input type="checkbox" id='cnv_toggle' ><label for='cnv_toggle'class="opt_checkbox">CNVs</label><br/>
    </div>
    <h2>Select defined pathway:</h2>
    <div class="meny_subgroup">
        <table cellpadding="1" cellspacing="0" border="0" class="display" id="example"></table>
        Custom Pathway:<br/><div class="file-upload"><span>Select File <input type="file" id="pathway_file" class="upload" name="data"/></span></div>
        <div style="clear:both;"><input style="clear:both; width:150px;" type="button" value="Save Pathway" onclick="save_pathway()"/></div>
<!--        <input type="button" value="Example" onclick="load_example()"/>-->
    </div>
    <h2>Select data source:</h2>
    <div class="meny_subgroup">
        <input type="radio" name="data_source" id='demo_' enabled checked>   	<label for="demo_">	Demo data</label>		<br/>
        <input type="radio" name="data_source" id='tcga_AML_' enabled>     <label for="tcga_AML_">	TCGA AML</label>		<br/>
        <input type="radio" name="data_source" id='Leu_ALL_' enabled>    <label for="Leu_ALL_">	Leucegene-ALL</label>		<br/>
        <input type="radio" name="data_source" id='tcga_Prostate_' enabled>     <label for="tcga_Prostate_">	TCGA Prostate cancer</label>		<br/>
        <input type="radio" name="data_source" id='nature_2014_' enabled>   <label for="nature_2014_">	Zhengyan 2014</label>		<br/>
        <input type="radio" name="data_source" id='LEU_AML' disabled>  <label for="LEU_AML">	Leucegene</label>		<br/>
        Custom Data:<br/><div class="file-upload"><span>Select File<input type="file" class="upload" id="patient_file" name="data"/></span></div><br/>

        <h2 style="clear:both;">Restrict frequency display:</h2>
        <table cellpadding="0" cellspacing="0" border="0" class="display" id="target-table"></table><br/><div class="file-upload"><span>Select File<input type="file" id="important_gene_file" class='upload' name="data"/></span></div><br/>
        <div style='clear:both;'><input type="button" style="width:150px;"value="Clear Targets" onclick="clear_targets()"/></div><input id="show-cust-mut" type="button" style="width:150px"value="View Targets" onclick="view_targets()" style="display: none;"/>
    </div>
</div>
<div class="meny-arrow"></div>
<script type="text/javascript">
            $(document).ready(function() {
                $("#controll_panel input[type=button]").button()
                $(".meny input[type=button]").button()

                var meny = Meny.create({
                    menuElement: document.querySelector('.meny'),
                    contentsElement: document.querySelector('.contents'),
                    position: Meny.getQuery().p || 'left',
                    height: 200,
                    width: 260,
                    threshold: 40 //mouse distance
                });
                if (Meny.getQuery().u && Meny.getQuery().u.match(/^http/gi)) {
                    var contents = document.querySelector('.contents');
                    contents.style.padding = '0px';
                    contents.innerHTML = '<div class="cover"></div><iframe src="' + Meny.getQuery().u + '" style="width: 100%; height: 100%; border: 0; position: absolute;"></iframe>';
                }
                /**
                 * Color modification and image generation for
                 * jqueryUI checkboxes and radio buttons
                 */
                $("input[type='checkbox'][id!='medrpkm_toggle']").button({icons: {primary: "ui-icon-close"}})
                $("#medrpkm_toggle").button({icons: {primary: "ui-icon-check"}})
                $("#medrpkm_toggle,#newsnp_toggle").button({icons: {primary: "ui-icon-check"}})
                $("label[for!='medrpkm_toggle'][for!='newsnp_toggle']").addClass("ui-state-error")
                $("label[for='medrpkm_toggle']").addClass("ui-state-highlight")
                $("label[for='newsnp_toggle']").addClass("ui-state-highlight")
                $("label[for~='_toggle']").css({width: '200px'})
                $("input[type='checkbox']").change(function() {
                    if ($(this).is(":checked")) {
                        $("label[for='" + $(this).attr("id") + "']").children().eq(0).removeClass("ui-icon-close");
                        $("label[for='" + $(this).attr("id") + "']").children().eq(0).removeClass("ui-icon");
                        $("label[for='" + $(this).attr("id") + "']").children().eq(0).addClass("ui-icon-check");
                        $("label[for='" + $(this).attr("id") + "']").children().eq(0).addClass("ui-icon");
                        $("label[for='" + $(this).attr("id") + "']").removeClass("ui-state-error")
                        $("label[for='" + $(this).attr("id") + "']").addClass("ui-state-highlight")
                    } else {
                        $("label[for='" + $(this).attr("id") + "']").children().eq(0).removeClass("ui-icon-check");
                        $("label[for='" + $(this).attr("id") + "']").children().eq(0).removeClass("ui-icon");
                        $("label[for='" + $(this).attr("id") + "']").children().eq(0).addClass("ui-icon-close");
                        $("label[for='" + $(this).attr("id") + "']").children().eq(0).addClass("ui-icon");
                        $("label[for='" + $(this).attr("id") + "']").addClass("ui-state-error")
                        $("label[for='" + $(this).attr("id") + "']").removeClass("ui-state-highlight")
                    }
                })
                $("input[name='data_source']").eq(0).siblings("label").removeClass("ui-state-error")
                $("input[type='radio']").button({icons: {primary: 'ui-icon-close'}});
                $("input[name='data_source']").eq(0).siblings("label[for='demo_']").addClass("ui-state-highlight")
                $("#demo_").button("option", "icons", {primary: 'ui-icon-check'})
                $("input[name='data_source']").eq(0).siblings("label[for!='demo_']").addClass("ui-state-error")
                $("input[type='radio']").change(function() {
                    $("input[name='data_source']").eq(0).siblings("label").removeClass("ui-state-highlight")
                    $("input[name='data_source']").eq(0).siblings("label").removeClass("ui-state-error")
                    $("input[name='data_source']").button("option", "icons", {primary: 'ui-icon-close'})
                    $("input[name='data_source']").eq(0).siblings("label").addClass("ui-state-error")
                    $("label[for='" + $(this).attr("id") + "']").removeClass("ui-state-error")
                    $("label[for='" + $(this).attr("id") + "']").addClass("ui-state-highlight")
                    $(this).button("option", "icons", {primary: 'ui-icon-check'})
                    try {
                        $("#dialog-modal").html("<image src='css/images/loading_gif.gif'> Loading...").dialog({
                            modal: true,
                            dialogClass: "no-close",
                            open: function() {
                                controls = null
                            },
                            close: function() {
                                controls = new THREE.OrbitControls(camera);
                            }
                        });
                        loadPatientsFromDB(1)
                    } catch (e) {
                        $("#dialog-modal").dialog("close")
                    }
                })
                $(".meny").bind("mousewheel", function(e) {
                    e.stopPropagation();
                })
                $(".meny").bind("mousedown", function(e) {
                    e.stopPropagation();
                })
            })
</script>