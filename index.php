<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Welcome to Cascade</title>
    </head>
    <body>
        <script src="js/jquery.js"></script>
        <script src="js/jquery-ui.js"></script>
        <script src="js/dataTables.js"></script>
        <link rel="stylesheet" href="css/jquery-ui.css">
        <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css" /> 

        <style>
            #welcome{
                text-align: center;
            }
            TD{
                font-size:1.1em !important;
            }
            #start{
                display: block;
                float:left;
                width:100%;
                text-align: center;
                margin-top:10px;
            }
            select{
                display:none;
            }
            .wrapper{
                text-align:center;
                margin-bottom:50px;
            }
            .msg{
                display:inline;
                vertical-align: middle;
                margin-right:50px;
            }
            table{
                margin-left:auto; 
                margin-right:auto;
                text-align:center;
            }
            td{
                width:50%;
            }
            body{
                background-color: #222;
                color:white;
            }
            h1{
                color:red;
            }

        </style>
        <div id="welcome">
            <h1>
                Welcome to the Cascade
            </h1>
        </div>
        <h2>
            Cascade is a tool which allows you to visualize your RNA-seq and DNA-seq data by overlaying the results onto known pathways to explore your data for potential
            combinatorial effects (e.g. mutation, expression, CNVs). Genes are represented by spheres (coloured based on mutation frequency, if available) and edges represent interactions between genes.
			Controls for data/pathway selection are on a fold-away menu on the left side of the screen.
        </h2>
        <h3>
                    To get started:
        </h3>

        <div class="wrapper">
            <table>
                <tr>
                    <td>
                        <h3 class="msg">Step 1 - Select a starting pathway: </h3>
                    </td>
                    <td>
                        <select name="pathway" id="pathway" >
                            <option id ="1">KEGG AML Signaling</option>
                            <option id="2">KEGG Prostate</option>
                            <option id="3">KEGG ALL Signaling</option>
                            <option id="4">KEGG RAS Signaling</option>
                            <option id="5">KEGG WNT Signaling</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>
                        <h3 class="msg">Step 2 - Select a data source: </h3>
                    </td>
                    <td>
                        <select name="data" id="data">
                            <option id="demo_">Demo</option>
                            <option id="tcga_AML_">TCGA AML</option>
                            <option id="Leu_ALL_">Leucegene-ALL</option>
                            <option id="tcga_Prostate_">TCGA Prostate</option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        <div id="start">
            <h3 class="msg">Step 3 - </h3>
            <span id="start_btn">Launch Cascade now!</span>
            <h3>
                N.B. Move your mouse over to the left of the screen to show fold-away menu to change data shown or pathway, or restrict colouring to specific gene lists.
            </h3>
        </div>
        <script type="text/javascript">
            function start() {
                var selId = parseInt($("#pathway option:selected").attr("id"))
                var datId = $("#data option:selected").attr("id")
                var site = "index_run?path=" + selId + "&data='" + datId + "'"
                window.location.replace(site);
            }
            $(document).ready(function() {

                $("#start_btn").button();
                $("#pathway").selectmenu({width: '350px'});
                $("#data").selectmenu({width: '350px'});
            })

            $("#start_btn").click(function() {
                start();
            })
        </script>
    </body>
</html>
