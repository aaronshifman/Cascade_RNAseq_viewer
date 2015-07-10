<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title></title>
    </head>
    <body>
        <script type="text/javascript">
            var controllTable;
            $(document).ready(function () {
                controllTable = $('#comp_table').dataTable({
                    "bJQueryUI": true
                })
            });
            //document.getElementById('controll_file').addEventListener("change", loadControllFile, false); //loadPatientsFromFile
            $('.cont_row').live('click', function (e) {
                xyz = $(this)
                loadControllFromDB($(this).attr('id'))
                $("#dialog-modal").html('')
                $("#dialog-modal").dialog('close')
                compData = $(this).children().eq(0).html()
                $("#dataset_comp").html(compData)
                $("#dataset_comp").show()
                $("#dataset_comp_label").show()
            })
        </script>
        <table cellpadding="1" cellspacing="0" border="0" class="display" id="comp_table">
            <thead>
                <tr><th>Dataset</th></tr>
            </thead>
            <tbody>
                <tr class="cont_row" id="demo_"><td>Demo Data</td></tr>
                <tr class="cont_row" id='tcga_AML_'><td>TCGA AML</td></tr>
                <tr class="cont_row" id='Leu_ALL_' ><td>Leucegene-ALL</td></tr>
                <tr class="cont_row" id='tcga_Prostate_' ><td>TCGA Prostate cancer</td></tr>
                <tr class="cont_row" id='nature_2014_' ><td>Zhengyan 2014</td></tr>
            </tbody>
        </table>
        Custom Pathway:<br/><div class="file-upload"><span>Select File <input type="file" id="controll_file" class="upload" name="data"/></span></div>
    </body>
</html>
