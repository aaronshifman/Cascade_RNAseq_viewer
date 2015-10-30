<?php
require_once('config.php')
?>
<html lang="en">
    <head>
        <title>Cascade</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
        <link rel="stylesheet" href="css/jquery-ui_old.css">
        <link rel="stylesheet" type="text/css" href="css/col.css" />  
        <link rel="stylesheet" type="text/css" href="css/main.css" />  
        <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css" /> 
        <link rel="stylesheet" href="css/meny3.css">
        <link rel='stylesheet' type='text/css' href='css.php' />
    </head>
    <body class=" meny-left " style="-webkit-perspective: 800px; -webkit-perspective-origin-x: 0px; -webkit-perspective-origin-y: 50%;">
        <canvas id="filler_canvas" style="position:absolute; left:100px"></canvas>
        <script src="js/jquery.js"></script>
        <script src="js/jquery-ui.js"></script>
        <script src="js/Three.js"></script>
        <script src="js/ThreeJS.Detector.js"></script>
        <script src="js/ThreeJS.OrbitControlls.js"></script>
        <script src="js/ThreeJS.WindowResize.js"></script>
        <script src="js/ssm.js"></script>
        <script src="js/ThreeJS.helvetiker.js"></script>
        <script src="js/colResizable.js"></script>
        <script src="js/dataTables.js"></script>
        <script src="js/load_data.js"></script>
        <script src="js/helpers.php"></script>
        <script src="js/math.js"></script>
        <script src="js/highcharts.js"></script>
        <script src="js/highcharts-more.js"></script>
        <div class="header">
            <div id="header-info"></div>
            <span class="header-left display-span" style="background-color: #<?php echo $node_color_low; ?>">Low</span>
            <span class="header-left display-span" style="background-color: #<?php echo $node_color_med; ?>">Intermediate</span>
            <span class="header-left display-span" style="background-color: #<?php echo $node_color_high; ?>">High</span><br/>
            <div style="display:block;">
                <span class="header-left display-spanr scale-indicator-label">Mutation</span>
                <span class="header-left display-spanr scale-indicator-mut">0%</span>
                <span class="header-left display-spanr scale-indicator-mut">10%</span>
                <span class="header-left display-spanr scale-indicator-mut">50%</span>
                <span class="header-left display-spanr scale-indicator-mut">100%</span><br/>
                <span class="header-left display-spanr scale-indicator-label">Alt-Splicing</span>
                <span class="header-left display-spanr scale-indicator-alt">0%</span>
                <span class="header-left display-spanr scale-indicator-alt">10%</span>
                <span class="header-left display-spanr scale-indicator-alt">50%</span>
                <span class="header-left display-spanr scale-indicator-alt">100%</span>
            </div>
        </div>
        <div id="controll_panel"class="ui-widget-header ui-corner-all" style="display:inline-block; margin-left:50px; padding:4px;">
            <input class="header-right" type="button" value="Modify Ranges" onclick="modify_range();"/>   
            <input class ="header-right" type="button" id="circle_toggle"value="Disable Levels" onclick="toggle_circles();"/>
<!--            <input class ="header-right" type="button" id="sel_sec_expr"value="Select Comparison" onclick="sel_comp();"/>-->
<!--            <input type="button" value ='Generate Image' onclick='print_view();'/>-->
            <input class="header-right" type="button" value="View documentation" onclick="window.open('http://www.bioinfo.iric.ca/~wilhelmb/cas/Cascade intro.pdf')"/><br/>
        </div>
        <div class='header title'>
            <div id='pathway_title'></div>
        </div>
        <?php include_once 'ssm.php'; ?> <!--load the menu -->
        <div class="contents" style="-webkit-transform-origin: 0px 50%; -webkit-transition: all 0.5s ease; transition: all 0.5s ease;"> 
            <script>
                var widthOffset = 0;
                        var defaultPath = <?php echo $_GET['path']; ?>;
                        var defaultData = <?php echo $_GET['data']; ?>;
                        $(document).ready(function () {
                heightOffset = $("canvas[id!=filler_canvas]").position().top
                })
                        $(window).resize(function () {
                heightOffset = $("canvas[id!=filler_canvas]").position().top
                })
                        var openNodes = [];

<?php include "js/init_vars.php"; ?> //include the scripts to initialize the variables
                function initNodes(nodes, reload) {
                cleanScene();
                        /**
                         * find all duplicate children and create seperate nodes for them
                         * eliminates converging pathways
                         */

                        //find all starting toplevels
                        var topsS = [];
                        for (var node in nodes) {
                if (nodes[node].familyMember === undefined) { // if it's a low level node
                var topLevel = true;
                        for (var nodeSearch in nodes) {
                if (jQuery.inArray(node, nodes[nodeSearch].linked) > - 1) { //if the node is another's child
                topLevel = false;
                        break;
                }
                }
                if (topLevel) {
                topsS.push(node);
                }
                }
                }
                var children = [];
                        for (var node in nodes) {
                children = children.concat(nodes[node].linked); //list of ALL children
                }
                children = children.sort();
                        for (var i = 0; i < children.length; i++) {
                if (jQuery.inArray(children[i], Object.keys(nodes)) === - 1) {
                nodes[children[i]] = {masterGene: children[i], name: children[i], linked: [], expression: 0, mutated: 0, altsplice: 0, copyNumber: 0, endpoint: 1};
                        alert("Missing Node Entry For: " + children[i] + " it had been Added as a Terminus");
                }
                }

                var wasChangeMade = false;
                        while (true) {
                var children = [];
                        for (var node in nodes) {
                children = children.concat(nodes[node].linked); //list of ALL children
                }
                children = children.sort();
                        var dups = [];
                        for (var i = 0; i < children.length - 1; i++) {
                if (children[i] === children[i + 1]) //if that node appears as a child more than once
                        dups.push(children[i]);
                }
                dups = removeDuplicates(dups); //get list of duplicated nodes
                        var i = 0;
                        wasChangeMade = false
                        for (var j = 0; j < dups.length; j++) {
                for (var node in nodes) {
                for (var link in nodes[node].linked) {
                if (nodes[node].linked[link] === dups[j]) { //if that link appears in the list of duplicates
                var tmp = jQuery.extend(true, {}, nodes[nodes[node].linked[link]]); //copy the node object for the duplicated node (for those not clear with OOP, obj1 = obj2, if you change obj2 you affect obj1)                               
                        nodes[nodes[node].linked[link] + "-" + i] = jQuery.extend(true, {}, tmp); //copy the refence object into an obj with the same name (+ unique number)
                        nodes[nodes[node].linked[link] + "-" + i].masterGene = tmp.masterGene; // replace the master gene with the real name
                        nodes[nodes[node].linked[link] + "-" + i].name = nodes[node].linked[link] + "-" + i; // give its name the name+number
                        nodes[node].linked[link] = nodes[node].linked[link] + "-" + i; //give the link the corrected name
                        i++;
                        wasChangeMade = true;
                }
                }
                }
                }
                for (var dup in dups) {
                delete nodes[dups[dup]]; // delete the old (no number) node
                }
                if (!wasChangeMade)
                        break;
                }
                /**
                 * find all dulicate members of families (occuring when a family is part of a convergent pathway
                 * duplicates family members as unique nodes
                 */
                var children = [];
                        for (var node in nodes) {
                if (nodes[node].family !== undefined)
                        children = children.concat(nodes[node].family);
                }
                children = children.sort();
                        var dups = [];
                        for (var i = 0; i < children.length - 1; i++) {
                if (children[i] === children[i + 1])
                        dups.push(children[i]);
                }
                for (var node in nodes) {
                if (nodes[node].family !== undefined) {
                for (var child in nodes[node].family) {
                if (jQuery.inArray(nodes[node].family[child], dups) > - 1) {
                var tmp = jQuery.extend(true, {}, nodes[nodes[node].family[child]]);
                        nodes[nodes[node].family[child] + i] = jQuery.extend(true, {}, tmp);
                        nodes[nodes[node].family[child] + i].masterGene = tmp.masterGene;
                        nodes[nodes[node].family[child] + i].name = nodes[node].family[child] + i;
                        nodes[node].family[child] = nodes[node].family[child] + i;
                        i++;
                }
                }
                }
                }
                for (var dup in dups) {
                delete nodes[dups[dup]];
                }

                //find final top level nodes;
                var topsF = [];
                        for (var node in nodes) {
                if (nodes[node].familyMember === undefined) { // if it's a low level node
                var topLevel = true;
                        for (var nodeSearch in nodes) {
                if (jQuery.inArray(node, nodes[nodeSearch].linked) > - 1) { //if the node is another's child
                topLevel = false;
                        break;
                }
                }
                if (topLevel) {
                topsF.push(node);
                }
                }
                }
                var extraNodes = ($(topsF).not(topsS).get())
                        for (var nodeE in extraNodes) {
                delete nodes[extraNodes[nodeE]];
                }

                /**
                 * Finds the toplevel nodes to abstract them from the next stage
                 */
                var tops = [];
                        var numNodes = 0;
                        for (var node in nodes) {
                if (nodes[node].familyMember === undefined) { // if it's a low level node
                numNodes++;
                        var topLevel = true;
                        for (var nodeSearch in nodes) {
                if (jQuery.inArray(node, nodes[nodeSearch].linked) > - 1) { //if the node is another's child
                topLevel = false;
                        break;
                }
                }
                if (topLevel) {
                tops[node] = nodes[node];
                }
                }
                }
                var numNodesInLevels = Object.keys(tops).length; //include the top level nodes
                        var levels = [[]]; // 2d array each level and its nodes                    
                        var i = 0;
//                    for (var top in tops) { //put the top level data into the structure data set
//                        levels[0][top] = tops[top];
//                        i++;
//                    }
                        levels[0] = tops
                        /**
                         * Generate the structure dataset
                         */
                        for (var i = 0; i < levels.length; i++) {
                levels[i + 1] = []; //create child level
                        for (var parent in levels[i]) {
                for (var child in nodes[parent].linked) { // for all of the parent's children
                levels[i + 1][nodes[parent].linked[child]] = nodes[nodes[parent].linked[child]]; // the level object is a pointer to the origional node object                                
                        levels[i + 1][nodes[parent].linked[child]].parent = parent;
                        numNodesInLevels++;
                }
                }
                if (numNodes === numNodesInLevels) //if all nodes have been added
                        break;
                }
                /**
                 * render positions for the graph's top level
                 * Note all angles are in radians
                 */
                a = levels;
                        //                  var radSep = 2 * Math.PI / Object.keys(levels[0]).length; //split the circle into the number of "ligands"
                        //                  var i = 0; // time around


                        /**
                         * Calculate the relative division of the plane based on maximum width with each pathway
                         *
                         * idea is find ring level with maximum nodes for each pathway and create divisions based on relative proportions
                         *
                         */

                        var leafs = getLeafs(levels);
                        var toAdd = []

                        //rendering leaf level
                        nodeObjects = [];
                        cylObjects = [];
                        var i = 0;
                        var sep = 2 * Math.PI / leafs.length;
                        if (Object.keys(levels[0]).length === 1) {
                sep = sep / 2
                }
                for (var node in leafs) {
                var ringLevel = getLevel(levels, leafs[node]);
                        var shapes, geom, mat, mesh;
                        var nodeObj, nodeMat, nodeGeom;
                        var nodeColor = choseColor(levels[ringLevel][leafs[node]], 'mutated');
                        var ringColor = choseColor(levels[ringLevel][leafs[node]], 'altsplice');
                        if (levels[ringLevel][leafs[node]].endpoint)
                        nodeColor = "blue";
                        else if (levels[ringLevel][leafs[node]].ion)
                        nodeColor = 'green'
                        var nodeGeom = new THREE.SphereGeometry(10, 10, 10);
                        var nodeMat = new THREE.MeshLambertMaterial({color: nodeColor});
                        if (levels[ringLevel][leafs[node]].copyNumber === undefined)
                        levels[ringLevel][leafs[node]].copyNumber = 0;
                        levels[ringLevel][leafs[node]].position = {};
                        levels[ringLevel][leafs[node]].position.x = 100 * (ringLevel + 1) * Math.sin(sep * i)
                        levels[ringLevel][leafs[node]].position.y = 100 * (ringLevel + 1) * Math.cos(sep * i)
                        if (levels[ringLevel][leafs[node]].altsplice && $("#splicing_toggle").is(":checked")) {
                drawCyl(levels[ringLevel][leafs[node]], ringColor);
                }
                levels[ringLevel][leafs[node]].angle = i * sep;
                        addNode(levels[ringLevel][leafs[node]], nodeMat, nodeGeom);
                        toAdd.push(levels[ringLevel][leafs[node]].parent);
                        i++;
                }

                toAdd = $.grep(toAdd, function (v, k) {
                return $.inArray(v, toAdd) === k;
                });
                        //toAdd are the parents
                        var added = leafs;
                        // added is whats been added
                        var tempPars = []
                        for (var i = levels.length - 1; i >= 0; i--) {
                for (var node in toAdd) {
                var ringLevel = getLevel(levels, toAdd[node]);
                        if (ringLevel === i) {
                var parent = levels[ringLevel][toAdd[node]];
                        var nodeObj, nodeMat, nodeGeom;
                        var nodeColor = choseColor(parent, 'mutated');
                        var ringColor = choseColor(parent, 'altsplice');
                        if (parent.endpoint)
                        nodeColor = "blue";
                        else if (parent.ion)
                        nodeColor = 'green'
                        var nodeGeom = new THREE.SphereGeometry(10, 10, 10);
                        var nodeMat = new THREE.MeshLambertMaterial({color: nodeColor});
                        if (parent.copyNumber === undefined)
                        parent.copyNumber = 0;
                        parent.position = {};
                        var angle = 0;
                        if (parent.linked.length === 1) {
                angle = levels[ringLevel + 1][parent.linked[0]].angle
                } else {
                var angles = []
                        for (var x in parent.linked) {
                angles.push(levels[ringLevel + 1][parent.linked[x]].angle);
                }
                angle = averageAngle(angles);
                }
                parent.position.x = (ringLevel + 1) * 100 * Math.sin(angle)
                        parent.position.y = (ringLevel + 1) * 100 * Math.cos(angle)
                        if (parent.altsplice && $("#splicing_toggle").is(":checked")) {
                drawCyl(parent, ringColor);
                }
                parent.angle = angle;
                        addNode(parent, nodeMat, nodeGeom);
                        added.push(parent.name);
                        if (parent.parent !== undefined) {
                tempPars = tempPars.concat(parent.parent);
                }
                } else {
                tempPars.push(toAdd[node]);
                }
                }
                toAdd = tempPars
                        toAdd = $.grep(toAdd, function (v, k) {
                return $.inArray(v, toAdd) === k;
                });
                        tempPars = [];
                }
                /**
                 Calculate max expression value to set scaling factor for vertical expression lines
                 **/
                for (var i = 0; i < levels.length; i++) {
                for (var parent in levels[i]) {
                var max_exprLevel;
                        var test_exprLevel;
                        if (EXPR_STATE % 3 === 2) {
                test_exprLevel = Math.round(100 * levels[i][parent].expression / genes[parent])
                        if (genes[parent] === undefined) {
                exprLevel = NaN
                }
                } else {
                test_exprLevel = levels[i][parent].expression
                }
                if (test_exprLevel > max_exprLevel) {
                max_exprLevel = test_exprLevel
                }
                }
                }
                var RPKM_scaling_factor;
                        if (max_exprLevel < 10) {
                RPKM_scaling_factor = 40
                }
                else if (max_exprLevel < 40) {
                RPKM_scaling_factor = 20
                }
                else if (max_exprLevel < 80) {
                RPKM_scaling_factor = 10
                }
                else {
                RPKM_scaling_factor = 3
                }
                /**
                 * Drawing the expression lines, cnvs and links
                 */
                for (var i = 0; i < levels.length; i++) {
                for (var parent in levels[i]) {
                if (levels[i][parent].family === undefined) { //don't draw CNVs or expression lines if the node is a family node (JAK made up of JAK1,JAK2...)
                var displayCNV = levels[i][parent].copyNumber;
                        if (isNaN(displayCNV)) { // if cnv does not come in data (error in data)
                displayCNV = 0;
                }
                var exprLevel;
                        if (EXPR_STATE % 3 === 2) {
                exprLevel = Math.round(100 * levels[i][parent].expression / genes[parent])
                        if (genes[parent] === undefined) {
                exprLevel = NaN
                }
                } else {
                exprLevel = levels[i][parent].expression
                }
                if (!$("#cnv_toggle").is(":checked"))
                        displayCNV = 0; //if CNVs are not to be shown, treat the CNV like its 0                            
                        if (EXPR_STATE % 3 > 0) { //only draw the RPKM lines is the RPKM toggle is on
                if (levels[i][parent].endpoint === undefined && levels[i][parent].ion === undefined) { //only draw if the node is not an endpoint
                var material = new THREE.LineBasicMaterial({color: 0x66ff66, linewidth: 5});
                        var geometry = new THREE.Geometry();
                        var sign = displayCNV < 0 ? - 1 : 1; //heavyside function of the copyNumber
                        if (CNV_STATE % 3 < 2) {
                geometry.vertices.push(new THREE.Vector3(levels[i][parent].position.x, 10 * sign + displayCNV * 20, levels[i][parent].position.y)); //bast
                        geometry.vertices.push(new THREE.Vector3(levels[i][parent].position.x, 10 * sign + displayCNV * 20 + (exprLevel * sign * RPKM_scaling_factor), levels[i][parent].position.y)); //top
                } else {
                var numCNV = 0;
                        try {
                for (var pat in patients) {
                if (patients[pat][levels[i + 1][childName].masterGene].copyNumber != 0)
                        numCNV += 1;
                }
                } catch (e) {
                numCNV = 0;
                }
                var freqCNV = 100 * numCNV / Object.keys(patients).length;
                        mult = 0;
                        if (freqCNV >= scales['cnv'][0]) {
                mult = 1;
                }
                if (levels[i][parent].copyNumber !== 0) {
                geometry.vertices.push(new THREE.Vector3(levels[i][parent].position.x, 10 * sign + sign * 20 * mult, levels[i][parent].position.y)); //bast
                        geometry.vertices.push(new THREE.Vector3(levels[i][parent].position.x, 10 * sign + sign * 20 * mult + (exprLevel * sign * RPKM_scaling_factor), levels[i][parent].position.y)); //top
                } else {
                geometry.vertices.push(new THREE.Vector3(levels[i][parent].position.x, 10 * sign + displayCNV * 20 * mult, levels[i][parent].position.y)); //bast
                        geometry.vertices.push(new THREE.Vector3(levels[i][parent].position.x, 10 * sign + displayCNV * 20 * mult + (exprLevel * sign * RPKM_scaling_factor), levels[i][parent].position.y)); //top
                }
                }
                var line = new THREE.Line(geometry, material); //expression line
                        var shapes, geom, mat, mesh;
                        shapes = THREE.FontUtils.generateShapes(exprLevel, {
                font: "helvetiker",
                        size: <?php echo $text_size; ?>
                });
                        var geom = new THREE.ShapeGeometry(shapes);
                        if (levels[i][parent].exprOutlier) { //if there are outliers in the expression, draw the number red
                mat = new THREE.MeshBasicMaterial({color: "#<?php echo $out_color; ?>"});
                        if (EXPR_STATE % 3 == 2) {
                mat = new THREE.MeshBasicMaterial({color: 0x<?php echo $reg_color; ?>});
                }
                } else {
                mat = new THREE.MeshBasicMaterial({color: 0x<?php echo $reg_color; ?>});
                }
                mesh = new THREE.Mesh(geom, mat);
                        if (CNV_STATE % 3 < 2) {
                mesh.position.set(levels[i][parent].position.x - 10, displayCNV * 20 + ((exprLevel * RPKM_scaling_factor) + 20) * sign, levels[i][parent].position.y);
                } else {
                if (levels[i][parent].copyNumber !== 0) {
                mesh.position.set(levels[i][parent].position.x - 10, sign * 20 + ((exprLevel * RPKM_scaling_factor) + 20) * sign, levels[i][parent].position.y);
                } else {
                mesh.position.set(levels[i][parent].position.x - 10, displayCNV * 20 + ((exprLevel * RPKM_scaling_factor) + 20) * sign, levels[i][parent].position.y);
                }

                }
                textLabels[textLabels.length] = mesh;
                        scene.add(mesh);
                        scene.add(line);
                        exprLines.push(line);
                }
                }
                if (displayCNV < 0) {
                drawCone(levels[i][parent], "#00FF00");
                } else if (displayCNV > 0) {
                drawCone(levels[i][parent], "#FF0000");
                }
                }
                //draw links
                for (var child in levels[i][parent].linked) {
                var childName = (levels[i][parent].linked[child]);
                        var material = new THREE.LineBasicMaterial({color: 0x<?php echo $link_color;?>});
                        var geometry = new THREE.Geometry();
                        if (levels[i][parent].family !== undefined) {
                displayCNV = 0;
                }
                if (CNV_STATE % 3 === 0) //dont draw the elevated line unless CNVs are on and the node is not a family node
                        geometry.vertices.push(new THREE.Vector3(levels[i + 1][childName].position.x, 0, levels[i + 1][childName].position.y));
                        else if (CNV_STATE % 3 === 1) {
                geometry.vertices.push(new THREE.Vector3(levels[i + 1][childName].position.x, levels[i + 1][childName].copyNumber * 20, levels[i + 1][childName].position.y));
                } else {
                var childSign = levels[i + 1][childName].copyNumber;
                        if (childSign < 0)
                        childSign = - 1;
                        else if (childSign > 0)
                        childSign = 1;
                        else
                        childSign = 0;
                        var numCNV = 0;
                        try {
                for (var pat in patients) {
                if (patients[pat][levels[i + 1][childName].masterGene].copyNumber != 0)
                        numCNV += 1;
                }
                } catch (e) {
                numCNV = 0;
                }
                var freqCNV = 100 * numCNV / Object.keys(patients).length;
                        if (freqCNV < scales['cnv'][0]) {
                childSign = 0
                }
                geometry.vertices.push(new THREE.Vector3(levels[i + 1][childName].position.x, childSign * 20, levels[i + 1][childName].position.y));
                }
                if (CNV_STATE % 3 === 2) {
                if (levels[i][parent].copyNumber === 0)
                        geometry.vertices.push(new THREE.Vector3(levels[i][parent].position.x, 0, levels[i][parent].position.y));
                        else {
                geometry.vertices.push(new THREE.Vector3(levels[i][parent].position.x, sign * 20, levels[i][parent].position.y));
                }
                } else {
                geometry.vertices.push(new THREE.Vector3(levels[i][parent].position.x, displayCNV * 20, levels[i][parent].position.y));
                }
                var line = new THREE.Line(geometry, material);
                        line.parentNode = parent
                        scene.add(line);
                        pathLines.push(line);
                }
                }
                }
                // draw plane circles
                for (var z = 1; z <= levels.length; z++) {
                var resolution = 100 * z; // number of segments
                        var amplitude = 100 * z; //radius
                        var size = 360 / resolution; //segment length

                        var geometry = new THREE.Geometry();
                        var material = new THREE.LineBasicMaterial({transparent: true, color: "#<?php echo $ring_color?>", opacity: (1.1 - (z / (2 * levels.length)))});
                        for (var i = 0; i <= resolution; i++) {
                var segment = (i * size) * Math.PI / 180;
                        geometry.vertices.push(new THREE.Vertex(new THREE.Vector3(Math.cos(segment) * amplitude, 0, Math.sin(segment) * amplitude)));
                }
                var line = new THREE.Line(geometry, material);
                        if (CIRCLES_ENABLED) {
                scene.add(line);
                }
                circLines.push(line);
                }

                if (reload !== undefined) {
                for (var obj in Object.keys(openNodes)) {
                for (var o in nodeObjects) {
                if (nodeObjects[o].name === Object.keys(openNodes)[obj]) {
                nodeObjects[o].open = true
                        nodes[nodeObjects[o].name].open = true
                        addFamNode(nodeObjects[o]);
                        break
                }
                }
                }
                }
                }
                /**
                 * Function that responds to a mouse move
                 * @param {type} event DOM event
                 */
                function onDocumentMouseMove(event) {
                if (!SCROLLING) {
                if (mouseDown[2]) {
                if ((event.clientX - widthOffset) - xPosMouse < - 200) {
                if (parseInt(currentPath) > 0) {
                currentPath = parseInt(currentPath) - 1;
                        loadPathwayFromDB(pathIds[currentPath])
                        if (currentPath === 0)
                        $("#pathway_title").html(pathNames[parseInt(currentPath)] + "<input type='button' class='pathway_scroll' onclick='load_right()' value='&#8594;'/>")
                        else if (currentPath === pathIds.length - 1)
                        $("#pathway_title").html("<input type='button' class='pathway_scroll' onclick='load_left()' value='&#8592;'/>" + pathNames[parseInt(currentPath)])
                        else
                        $("#pathway_title").html("<input type='button' class='pathway_scroll' onclick='load_left()' value='&#8592;'/>" + pathNames[parseInt(currentPath)] + "<input type='button' class='pathway_scroll' onclick='load_right()' value='&#8594;'/>")

                        xPosMouse = event.clientX
                        makeScrollButton();
                        SCROLLING = true;
                }
                }

                else if ((event.clientX - widthOffset) - xPosMouse > 200) {
                if (parseInt(currentPath) < pathIds.length - 1) {
                currentPath = parseInt(currentPath) + 1;
                        loadPathwayFromDB(pathIds[currentPath])
                        if (currentPath === 0)
                        $("#pathway_title").html(pathNames[parseInt(currentPath)] + "<input type='button' class='pathway_scroll' onclick='load_right()' value='&#8594;'/>")
                        else if (currentPath === pathIds.length - 1)
                        $("#pathway_title").html("<input type='button' class='pathway_scroll' onclick='load_left()' value='&#8592;'/>" + pathNames[parseInt(currentPath)])
                        else
                        $("#pathway_title").html("<input type='button' class='pathway_scroll' onclick='load_left()' value='&#8592;'/>" + pathNames[parseInt(currentPath)] + "<input type='button' class='pathway_scroll' onclick='load_right()' value='&#8594;'/>")
                        xPosMouse = event.clientX
                        makeScrollButton();
                        SCROLLING = true;
                }
                }
                }



                // update sprite position
                sprite1.position.set(event.clientX, event.clientY, 0);
                        // update the mouse variable
                        mouse.x = ((event.clientX - widthOffset) / window.innerWidth) * 2 - 1;
                        mouse.y = - ((event.clientY - heightOffset) / (window.innerHeight)) * 2 + 1; //corrected mouse position for the shifted canvas (header height)
                }
                }
                /**
                 * Function that reponds to a dom click
                 * used to bring up the about page for nodes
                 * and to open/close the family nodes
                 * @param {type} event DOM event
                 */
                function onClick(event) {
                var vector = new THREE.Vector3(((event.clientX - widthOffset) / window.innerWidth) * 2 - 1, - ((event.clientY - heightOffset) / window.innerHeight) * 2 + 1, 0.5); //get a unit vector between the camera and the mouse
                        projector.unprojectVector(vector, camera);
                        var raycaster = new THREE.Raycaster(camera.position, vector.sub(camera.position).normalize());
                        tmpNodes = [];
                        for (var nd in famObjects)
                        tmpNodes.push(famObjects[nd]); //fake the famObjects assoc. array into being numerically indexed
                        var intersects1 = raycaster.intersectObjects(nodeObjects);
                        var intersects2 = raycaster.intersectObjects(tmpNodes);
                        if (intersects1.length > 0 || intersects2.length > 0) { //if the intersection if with either a node or a family object

                //pick the apropriate intersection object
                if (intersects1.length > 0)
                        intersects = intersects1; //node
                        else
                        intersects = intersects2; //family
                        if (clicked !== intersects[0].object.name) { //if you're clicking a new object
                if (intersects[0].object.endpoint === undefined && intersects[0].object.ion === undefined && intersects[0].object.family === undefined) { //if the object is not an endpoint and not a family node
                clicked = intersects[0].object.name;
                        oldHex = intersects[0].object.currentHex;
                        intersects[0].object.material.color.setHex(0x123456);
                        var clickedObj = "";
                        //get the array index of the clicked object
                        for (var node in nodeObjects) {
                if (nodeObjects[node].name === clicked)
                        clickedObj = nodeObjects[node];
                }
                for (var node in famObjects) {
                if (famObjects[node].name === clicked)
                        clickedObj = famObjects[node];
                }
                /**
                 * Generate the DOM string for the about dialog
                 * uses the JQueryUI scroller to collapse each div
                 */
                var string = "<div id='accordion_info'>";
                        string += "<h3>About</h3><div>";
                        string += "<span id='loading_card'><img src='css/images/loading_gif.gif'/></span></div>";
                        $.get("loadGenecards.php?gene=" + nodes[clicked].masterGene, function (data) {
                var string = "";
                        F = data;
//                                    $("a[name='summaries']", data).parent().eq(1).children().each(function() {
//                                        if ($(this).prop("tagname") === "a") {
//                                            $(this).removeAttr("target").removeAttr("onclick");
//                                        }
//                                        if ($(this).prop("tagname") !== "img")
//                                            string += $(this).prop("outerHTML");
//                                    });
//                                    $("a[name='aliases_descriptions']", data).parent().eq(1).find('td[nowrap]').each(function() {
//                                        if (this.attributes.length === 1)
//                                            string += $(this).html() + "<br/>";
//                                    });
                        $("#loading_card").replaceWith("<td>" + $(($("#summaryDl", F).find("dd")[($("#summaryDl", F).find("dd").length) - 2])).html() + "</td>");
                });
                        string += "<h3>Expression</h3><div>";
                        var rpkms_bypat = []
                        var rpkms_bypat_mod = []
                        var pat = []
                        string += "<input type='button' value = 'Change Graph' id='graph_change' onclick='toggle_graph()'/>";
                        string += "<div id='expression_subscroll'><h3>Patients</h3><div>";
                        for (expression in expressions[clickedObj.masterGene]) {
                if (expressions[clickedObj.masterGene][expression].outlier)
                        string += "Patient: " + expressions[clickedObj.masterGene][expression].patient + " RPKM: <span style='color:red;'>" + expressions[clickedObj.masterGene][expression].expression + "</span></br>";
                        else {
                string += "Patient: " + expressions[clickedObj.masterGene][expression].patient + " RPKM: " + expressions[clickedObj.masterGene][expression].expression + "</br>";
                }
                pat.push(expressions[clickedObj.masterGene][expression].patient)
                        var found = false
                        for (var mut in mutations[clickedObj.masterGene]) {
                if (mutations[clickedObj.masterGene][mut].patient === pat[pat.length - 1]) {
                if (parseInt(mutations[clickedObj.masterGene][mut].snp) || parseInt(mutations[clickedObj.masterGene][mut].snpdmg) || parseInt(mutations[clickedObj.masterGene][mut].indel)) {
                found = true;
                        rpkms_bypat.push(
                {
                y: parseFloat(expressions[clickedObj.masterGene][expression].expression),
                        color: "red"
                })
                        break;
                }
                }
                }
                if (!found) {
                rpkms_bypat.push(parseFloat(expressions[clickedObj.masterGene][expression].expression))
                }
                rpkms_bypat_mod.push(parseFloat(expressions[clickedObj.masterGene][expression].expression))
                }

                MAST = nodes[clicked].masterGene;
                        BYPAT = rpkms_bypat;
                        BYPAT_mod = rpkms_bypat_mod;
                        PAT = pat;
                        string += "</div></div>"
                        string += "Median RPKM: " + clickedObj.expression;
                        string += "<div id='expression_graph'>";
                        string += "</div>"
                        string += "</div><h3>Mutations</h3><div><div id='mutation_subscroll'><h3>Patients</h3><div>";
                        string += "<table id='mutation_table'><thead><tr>";
                        string += "<th>Patient</th><th>New SNVs</th><th>Damaging</th><th>New INDELs</th></tr></thead><tbody>";
                        for (var mutated in mutations[clickedObj.masterGene]) {
                string += "<tr><td>" + mutations[clickedObj.masterGene][mutated].patient + "</td>";
                        if (mutations[clickedObj.masterGene][mutated].snp === "1")
                        string += "<td><span style='color:red;'>*</span></td>";
                        else
                        string += "<td>Ref</td>";
                        if (mutations[clickedObj.masterGene][mutated].snpdmg === "1")
                        string += "<td><span style='color:red;'>*</span></td>";
                        else
                        string += "<td>Ref</td>";
                        if (mutations[clickedObj.masterGene][mutated].indel === "1")
                        string += "<td><span style='color:red;'>*</span></td>";
                        else
                        string += "<td>Ref</td>";
                        string += "</tr>";
                }
                string += "</tbody></table></div></div>";
                        string += Math.round(parseFloat(clickedObj.mutated)) + "% Mutated <br/>" + "</div><h3>Alt Splicing</h3><div>";
                        string += "<div id='splice_subscroll'><h3>Patients</h3><div>";
                        string += "<table id='splice_table'><thead><tr>";
                        string += "<th>Patient</th><th>Spliced</th></thead><tbody>";
                        for (var splice in altsplices[clickedObj.masterGene]) {
                string += "<tr><td>" + altsplices[clickedObj.masterGene][splice].patient + "</td>";
                        if (altsplices[clickedObj.masterGene][splice].splice === "1")
                        string += "<td><span style='color:red;'>*</span></td>";
                        else
                        string += "<td>Ref</td>";
                        string += "</tr>";
                }
                string += "</tbody></table></div></div>";
                        string += Math.round(parseFloat(clickedObj.altsplice)) + "% Alternatively Spliced";
                        string += "</div><h3>Copy Number Variations</h3><div>";
                        string += "<div id='cnv_subscroll'><h3>Patients</h3><div>";
                        string += "<table id='cnv_table'><thead><tr>";
                        string += "<th>Patient</th><th>CNV</th></thead><tbody>";
                        for (var cnv in cnvs[clickedObj.masterGene]) {
                string += "<tr><td>" + cnvs[clickedObj.masterGene][cnv].patient + "</td>";
                        string += "<td>" + cnvs[clickedObj.masterGene][cnv].cnv + "</td>";
                        string += "</tr>";
                }
                string += "</tbody></table></div></div>";
                        string += "Average CNV: " + clickedObj.copyNumber + "</div>";
                        string += "</div>";
                        $("#header-info").html(string).dialog({
                width: 600,
                        height: window.innerHeight * 0.9,
                        modal: true,
                        title: nodes[clicked].masterGene,
                        dialogClass: "",
                        close: function () {
                for (node in nodeObjects) {
                if (nodeObjects[node].name === clicked)
                        nodeObjects[node].material.color.setHex(oldHex);
                }
                for (node in famObjects) {
                if (famObjects[node].name === clicked)
                        famObjects[node].material.color.setHex(oldHex);
                }
                clicked = "";
                        controls = new THREE.OrbitControls(camera); //turn on the mouse functions on the canvas
                },
                        open: function () {
                $("#accordion_info").accordion({heightStyle: "fill",
                        activate: function () {
                barchart()
                }});
                        $("div[id*='subscroll']").accordion({
                collapsible: true,
                        active: false,
                        heightStyle: "content"
                });
                        $("#expression_subscroll").accordion({
                collapsible: true,
                        active: false,
                        heightStyle: "content"
                })
                        $("table[id*='_table']").dataTable({
                "bJQueryUI": true,
                        "aLengthMenu": [[25, 50, 100, 200, - 1],
                        [25, 50, 100, 200, "All"]],
                        "iDisplayLength": - 1
                });
                        controls = null; //turn off the mouse functions on the canvas
                }
                });
                }
                else { //if the object is a family object
                if (!intersects[0].object.open) { //if it is closed open it
                intersects[0].object.open = true;
                        nodes[intersects[0].object.name].open = true;
                        addFamNode(intersects[0].object);
                } else { //if its open, remove its children
                for (var fam in intersects[0].object.family) {
                scene.remove(familyLines[intersects[0].object.family[fam]]);
                        delete familyLines[intersects[0].object.family[fam]];
                        scene.remove(famLabels[intersects[0].object.family[fam]]);
                        delete famLabels[intersects[0].object.family[fam]];
                        scene.remove(famObjects[intersects[0].object.family[fam]]);
                        delete famObjects[intersects[0].object.family[fam]];
                        scene.remove(famExprLable[intersects[0].object.family[fam]]);
                        delete famExprLable[intersects[0].object.family[fam]];
                        scene.remove(famExpr[intersects[0].object.family[fam]]);
                        delete famExpr[intersects[0].object.family[fam]];
                        scene.remove(famSplice[intersects[0].object.family[fam]]);
                        delete famSplice[intersects[0].object.family[fam]];
                        scene.remove(famCNV[intersects[0].object.family[fam]]);
                        delete famCNV[intersects[0].object.family[fam]];
                        intersects[0].object.open = false;
                        nodes[intersects[0].object.name].open = false;
                        delete openNodes[intersects[0].object.name];
                }
                }
                }
                }
                }
                }
                function animate() {
                requestAnimationFrame(animate);
                        render();
                        update();
                }
                function update() {
                // find intersections
                try {
                // create a Ray with origin at the mouse position
                //   and direction into the scene (camera direction)
                var vector = new THREE.Vector3(mouse.x, mouse.y, 1);
                        projector.unprojectVector(vector, camera);
                        var ray = new THREE.Raycaster(camera.position, vector.sub(camera.position).normalize());
                        // create an array containing all objects in the scene with which the ray intersects
                        var intersects = ray.intersectObjects(scene.children);
                        // INTERSECTED = the object in the scene currently closest to the camera 
                        //		and intersected by the Ray projected from the mouse position 	

                        // if there is one (or more) intersections
                        if (intersects.length > 0)
                {
                // if the closest object intersected is not the currently stored intersection object
                if (intersects[ 0 ].object !== INTERSECTED)
                {
                // restore previous intersection object (if it exists) to its original color
                if (INTERSECTED) {
                INTERSECTED.material.color.setHex(INTERSECTED.currentHex);
                }
                // store reference to closest object as current intersection object
                INTERSECTED = intersects[ 0 ].object;
                        // store color of closest object (for later restoration)
                        INTERSECTED.currentHex = INTERSECTED.material.color.getHex();
                        // update text, if it has a "name" field.

                        if (intersects[0].object.type === "node")
                {

                // set a new color for closest object
                if (intersects[0].object.masterGene !== undefined) {
                for (node in nodeObjects) {
                if (nodeObjects[node].masterGene === intersects[0].object.masterGene)
                        nodeObjects[node].material.color.setHex(0xffff00);
                }
                for (path in pathLines) {
                if (pathLines[path].parentNode === intersects[0].object.name) {
                pathLines[path].material.color.setHex(0xff0000);
                }
                }
                }
                INTERSECTED.material.color.setHex(0xffff00);
                }
                else
                {
                context1.clearRect(0, 0, 300, 300);
                        texture1.needsUpdate = true;
                }
                }
                }
                else // there are no intersections
                {
                // restore previous intersection object (if it exists) to its original color
                if (INTERSECTED) {
                if (INTERSECTED.name !== clicked)
                        INTERSECTED.material.color.setHex(INTERSECTED.currentHex);
                        if (INTERSECTED.masterGene !== undefined) {
                for (node in nodeObjects) {
                if (nodeObjects[node].name !== clicked) {
                if (nodeObjects[node].masterGene === INTERSECTED.masterGene)
                        nodeObjects[node].material.color.setHex(INTERSECTED.currentHex);
                }

                }
                for (path in pathLines) {
                pathLines[path].material.color.setHex(0xffff00);
                }
                }
                }
                // remove previous intersection object reference
                //     by setting current intersection object to "nothing"
                INTERSECTED = null;
                        context1.clearRect(0, 0, 300, 300);
                        texture1.needsUpdate = true;
                }
                try {
                controls.update();
                } catch (e) {
                }
                } catch (e) {
                }
                }
                function render() {
                //make all labels look at the camera (keep text facing the user)
                for (mesh in textLabels) {
                textLabels[mesh].lookAt(camera.position);
                }
                for (mesh in famExprLable) {
                famExprLable[mesh].lookAt(camera.position);
                }
                for (mesh in famLabels) {
                famLabels[mesh].lookAt(camera.position);
                }
                renderer.render(scene, camera);
                }
                var F;
            </script>
        </div>
        <div id="dialog-modal"></div>
    </body>
</html>