<?php
header("Content-type: text/javascript; charset: UTF-8");
require_once('config.php');
?>

var oTable;
var heightOffset = 100;
var pathIds = [];
var currentPath;
var pathNames = []
var swap = false
$.get("getPaths.php", function(data) {
    var tableRows = []

    for (var row in JSON.parse(data)) {
        pathIds.push(parseInt(JSON.parse(data)[row].pathway_id))
        pathNames.push(JSON.parse(data)[row].pathway_name)
        tableRows.push([JSON.parse(data)[row].pathway_id, JSON.parse(data)[row].pathway_name, JSON.parse(data)[row].type, JSON.parse(data)[row].genes])
    }
    oTable = $("#example").dataTable({
        "aaData": tableRows,
        "aoColumns": [
            {"bVisible": false},
            {"sTitle": "Name"},
            {"sTitle": "Function"},
            {"bVisible": false},
        ],
        "bJQueryUI": true,
        iDisplayLength: 5,
        "bLengthChange": false,
        "fnInitComplete": function() {
            $("#example").css("width", '')
        }
    })
})
var oTable2;
$.get("getTargets.php", function(data) {
    var tableRows = []
    for (var row in JSON.parse(data))
        tableRows.push([JSON.parse(data)[row].disease_id, JSON.parse(data)[row].disease_name, "<input type='button' value='View' class='view_genes'/>", JSON.parse(data)[row].genes])
    oTable2 = $("#target-table").dataTable({
        "aaData": tableRows,
        "aoColumns": [
            {"bVisible": false},
            {"sTitle": "Disease"},
            {"sTitle": "View"},
            {"bVisible": false}
        ],
        "bJQueryUI": true,
        iDisplayLength: 5,
        "bLengthChange": false,
        "fnInitComplete": function() {
            $("#target-table").css("width", '')
        }
    })
})
$(".view_genes").live("click", function(e) {
    loadCustomMutsFromDb(oTable2.fnGetData(oTable2.fnGetPosition($(this).parent().parent()[0]))[0], 1)
    e.stopPropagation();
})
$("#example tbody tr").live("click", function() {
    ANYTHING_LOADED = true;
    openNodes = [];
    currentPath = jQuery.inArray(parseInt(oTable.fnGetData(oTable.fnGetPosition(this))[0]), pathIds);

    loadPathwayFromDB(oTable.fnGetData(oTable.fnGetPosition(this))[0]) // load the pathway of the selected ID
    if (currentPath === 0)
        $("#pathway_title").html(oTable.fnGetData(oTable.fnGetPosition(this))[1] + "<input type='button' class='pathway_scroll' onclick ='load_right()' value='&#8594;'/>");
    else if (currentPath === pathIds.length - 1)
        $("#pathway_title").html("<input type='button' class='pathway_scroll' onclick='load_left()' value='&#8592;'/>" + oTable.fnGetData(oTable.fnGetPosition(this))[1]);
    else
        $("#pathway_title").html("<input type='button' class='pathway_scroll' onclick='load_left()' value='&#8592;'/>" + oTable.fnGetData(oTable.fnGetPosition(this))[1] + "<input type='button' class='pathway_scroll' onclick ='load_right()' value='&#8594;'/>");
    makeScrollButton();
    setCheck_as_db();
})
$("#target-table tbody tr").live("click", function() {
    loadCustomMutsFromDb(oTable2.fnGetData(oTable2.fnGetPosition(this))[0])
    $(".dataTable_selected").removeClass("dataTable_selected");
    $(this).addClass("dataTable_selected")
})
function save_pathway() {
    $("#dialog-modal").load("save_pathway_dialog.php").dialog({
        open: function() {
            controls = null
        },
        close: function() {
            controls = new THREE.OrbitControls(camera);
        },
        dialogClass: ""
    })
}
document.getElementById('pathway_file').addEventListener("change", loadPathwayFromFile, false);
document.getElementById('patient_file').addEventListener("change", loadPatientsFromFile, false);
document.getElementById('important_gene_file').addEventListener("change", customTargets, false);



//////////
// MAIN //
//////////
// standard global variables
var container, scene, camera, renderer, controls, stats;
var clock = new THREE.Clock();
var projector, mouse = {x: 0, y: 0}, INTERSECTED;
var sprite1;
var canvas1, context1, texture1;
var textLabels = [];
var nodeObjects = [];
var cylObjects = [];
var exprLines = [];
var pathLines = [];
var familyLines = [];
var famObjects = [];
var famLabels = [];
var famExpr = [];
var famExprLable = [];
var famSplice = [];
var famCNV = [];
var cpCyl = [];
var circLines = [];
var scales;
var colors_freindly;
var nodes;
var unSplit_nodes;
var patients;
var expressions;
var mutations;
var altsplices;
var cnvs;
var customMuts = [];
var fromDb;
var clicked = ""; //last clicked node
var oldHex = 0; //old hex color of the node clicked node
init()
animate()
var origNodes = ""; // line by line of the text file for later saving
var controllFile = ""
var ANYTHING_LOADED = false;
var compData = ""

/** return #FAAFBE; */

function init() {
    scales = [[]];
    scales['mutated'] = [10, 50]
    scales['altsplice'] = [10, 50]
    scales['cnv'] = [10];
    textLabels = []; //array of all labels so that they always face the user
    colors = ["#<?php echo $node_color_low;?>", "#<?php echo $node_color_med;?>", "#<?php echo $node_color_high;?>"] //color scale colors                    
    colors_freindly = ["0x<?php echo $node_color_low;?>", "0x<?php echo $node_color_med;?>", "0x<?php echo $node_color_high;?>"] //color scale colors for three                    
    scene = new THREE.Scene();
    var SCREEN_WIDTH = window.innerWidth, SCREEN_HEIGHT = window.innerHeight;
    var VIEW_ANGLE = 45, ASPECT = SCREEN_WIDTH / (SCREEN_HEIGHT), NEAR = 0.1, FAR = 20000;
    camera = new THREE.PerspectiveCamera(VIEW_ANGLE, ASPECT, NEAR, FAR);
    scene.add(camera);
    camera.position.set(0, 300, 1600);
    if (Detector.webgl)
        renderer = new THREE.WebGLRenderer({antialias: true, preserveDrawingBuffer: true});
    else
        renderer = new THREE.CanvasRenderer();
    renderer.setSize(SCREEN_WIDTH, SCREEN_HEIGHT);
    renderer.setClearColorHex('0x<?php echo $bg_color;?>', 1);
    // create a div element to contain the renderer
    container = document.createElement('div');
    document.body.appendChild(container);
    // attach renderer to the container div
    container.appendChild(renderer.domElement);
    THREEx.WindowResize(renderer, camera);
    controls = new THREE.OrbitControls(camera);

    var directionalLight = new THREE.DirectionalLight(0xffffff);
    directionalLight.position.set(1, -1, 1).normalize();
    scene.add(directionalLight);
    var directionalLight2 = new THREE.DirectionalLight(0xffffff);
    directionalLight.position.set(-1, 1, 1).normalize();
    scene.add(directionalLight2);
    var directionalLight3 = new THREE.DirectionalLight(0xffffff);
    directionalLight.position.set(1, 1, -1).normalize();
    // scene.add(directionalLight3);      
    var directionalLight4 = new THREE.DirectionalLight(0xffffff);
    directionalLight.position.set(1, -1, 1).normalize();
    //scene.add(directionalLight4); 

    var light = new THREE.PointLight(0xffffff);
    light.position.set(0, 1000, 0);
    // scene.add(light);
    var light2 = new THREE.PointLight(0xffffff);
    light2.position.set(0, -1000, 0);
    //scene.add(light2);
    var light3 = new THREE.PointLight(0xffffff);
    light3.position.set(0, 0, 1000);
    //scene.add(light3);
    var light4 = new THREE.PointLight(0xffffff);
    light4.position.set(0, 0, -1000);
    //scene.add(light4);
    var light5 = new THREE.PointLight(0xffffff);
    light5.position.set(1000, 0, 0);
    //scene.add(light5);
    var light6 = new THREE.PointLight(0xffffff);
    light6.position.set(-1000, 0, 0);
    scene.add(light6);


    var ambientLight = new THREE.AmbientLight(0x111111);
    scene.add(ambientLight);
    // initialize object to perform world/screen calculations
    projector = new THREE.Projector();
    // when the mouse moves, call the given function

    /////// Gene Mouseovers /////////
    // create a canvas element
    canvas1 = document.createElement('canvas');
    context1 = canvas1.getContext('2d');

    context1.font = "Bold 20px Arial";
    // canvas contents will be used for a texture
    texture1 = new THREE.Texture(canvas1);
    texture1.needsUpdate = true;
    ///////////////////////////////////////

    var spriteMaterial = new THREE.SpriteMaterial({map: texture1, useScreenCoordinates: true, alignment: THREE.SpriteAlignment.topLeft});
    sprite1 = new THREE.Sprite(spriteMaterial);
    sprite1.scale.set(200, 100, 1.0);
    sprite1.position.set(50, 50, 0);
    scene.add(sprite1);
}
$("canvas").click(function(e) {
    onClick(e)
})
$("canvas").mousemove(function(e) {
    onDocumentMouseMove(e)
})

var mouseDown = [0, 0, 0, 0, 0, 0, 0, 0, 0]
var xPosMouse;
document.body.onmousedown = function(evt) {
    mouseDown[evt.button] = 1;
    xPosMouse = evt.clientX
}
document.body.onmouseup = function(evt) {
    mouseDown[evt.button] = 0;
}
/**
 * response to when the rpkm,cnv or splicing
 * toggles are fired
 */
$("#medrpkm_toggle,#splicing_toggle").change(function(e) {
    try {
        initNodes(nodes)
    } catch (e) {
    }
})
$("#cnv_toggle").change(function(e) {
    CNV_STATE += 1;
    if (CNV_STATE % 3 > 0) {
        $("#cnv_toggle").prop("checked", true);
        if (CNV_STATE % 3 === 1) {
            $("label[for='cnv_toggle'").children().eq(1).text("CNV - absolute freq");
        } else if (CNV_STATE % 3 === 2) {
            $("label[for='cnv_toggle'").children().eq(1).text("CNV -relative freq");
        }
    } else {
        $("label[for='cnv_toggle'").children().eq(1).text("CNVs");
    }
    try {
        initNodes(nodes)
    } catch (e) {
    }


})



$("#medrpkm_toggle").change(function(e) {
    EXPR_STATE += 1;
    if (!ANYTHING_LOADED && EXPR_STATE % 3 == 2) {
        EXPR_STATE += 1;
        $("label[for='medrpkm_toggle'").children().eq(1).text("Expression");
    }
    $("#dataset_comp").hide()
    $("#dataset_comp_label").hide()
    if (EXPR_STATE % 3 > 0) {
        $("#medrpkm_toggle").prop("checked", true);
        if (EXPR_STATE % 3 === 1) {
            $("label[for='medrpkm_toggle'").children().eq(1).text("Median RPKM");
            initNodes(nodes)
        } else if (EXPR_STATE % 3 === 2) {
            $("label[for='medrpkm_toggle'").children().eq(1).text("RPKM Ratio");
            if (Object.keys(genes).length === 0) {
                sel_comp();
            } else {
                initNodes(nodes)
                $("#dataset_comp").html(compData)
                $("#dataset_comp").show()
                $("#dataset_comp_label").show()
            }
        }
    } else {
        $("label[for='medrpkm_toggle'").children().eq(1).text("Expression");
        initNodes(nodes)
    }
})



/**
 * auto response to when any of the mutation toggles are fired
 */
$("#knownsnp_toggle,#newsnp_toggle,#newindel_toggle").change(function(e) {
    if (fromDb) {
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
            loadPatientsFromDB()
        } catch (e) {
            $("#dialog-modal").dialog("close")
        }
    } else {
        if (($("#newsnp_toggle").is(":checked") || $("#newindel_toggle").is(":checked"))) {
            for (var node in nodeObjects) {
                nodeObjects[node].mutated = 0;
                var node_real;
                for (var node2 in nodes) {
                    if (nodes[node2].masterGene === nodeObjects[node].masterGene) {
                        node_real = node2;
                    }
                }
                nodes[node_real].mutated = 0
                var i = 0;
                for (var patient in patients) {
                    i++;
                    if ($("#newsnp_toggle").is(":checked") && $("#newindel_toggle").is(":checked")) {
                        nodeObjects[node].mutated += (parseInt(patients[patient][nodeObjects[node].masterGene].snp) || parseInt(patients[patient][nodeObjects[node].masterGene].indel) || parseInt(patients[patient][nodeObjects[node].masterGene].snpdmg));
                    } else if ($("#newindel_toggle").is(":checked")) {
                        nodeObjects[node].mutated += (parseInt(patients[patient][nodeObjects[node].masterGene].indel))
                    } else if ($("#newsnp_toggle").is(":checked")) {
                        nodeObjects[node].mutated += (parseInt(patients[patient][nodeObjects[node].masterGene].snp) || parseInt(patients[patient][nodeObjects[node].masterGene].snpdmg))
                    }
                }
                nodeObjects[node].mutated = 100 * nodeObjects[node].mutated / i;
                nodes[node_real].mutated = nodeObjects[node].mutated;
            }
        } else {
            for (var node in nodeObjects) {
                nodeObjects[node].mutated = 0
            }
            for (var node in nodes) {
                nodes[node].mutated = 0
            }
        }
        update_colors();
    }
})