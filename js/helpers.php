<?php
header("Content-type: text/javascript; charset: UTF-8");
include('../config.php');
?>

    function update_colors() {
        for (node in nodeObjects) {
            if (nodeObjects[node].endpoint === undefined && nodeObjects[node].ion === undefined) {
                var nodeColor;
                if (customMuts.length > 0 && (jQuery.inArray(nodeObjects[node].masterGene, customMuts) === -1)) {
                    nodeColor = colors_freindly[0];
                } else {
                    if (nodeObjects[node].mutated <= scales['mutated'][0] || nodeObjects[node].mutated === undefined) {
                        nodeColor = colors_freindly[0];
                    } else if (nodeObjects[node].mutated > scales['mutated'][0] && nodeObjects[node].mutated <= scales['mutated'][1]) {
                        nodeColor = colors_freindly[1];
                    }
                    else {
                        nodeColor = colors_freindly[2];
                    }
                }
                if (nodeObjects[node].family === undefined)
                    nodeObjects[node].material.color.setHex(nodeColor);
            }
        }
        for (node in famObjects) {
            if (famObjects[node].endpoint === undefined && nodeObjects[node].ion === undefined) {
                var nodeColor;
                if (customMuts.length > 0 && (jQuery.inArray(famObjects[node].masterGene, customMuts) === -1)) {
                    nodeColor = colors_freindly[0];
                } else {
                    if (famObjects[node].mutated <= scales['mutated'][0] || famObjects[node].mutated === undefined) {
                        nodeColor = colors_freindly[0];
                    } else if (famObjects[node].mutated > scales['mutated'][0] && famObjects[node].mutated <= scales['mutated'][1]) {
                        nodeColor = colors_freindly[1];
                    }
                    else {
                        nodeColor = colors_freindly[2];
                    }
                }
                famObjects[node].material.color.setHex(nodeColor);
            }
        }
        for (node in cylObjects) {

            var ringColor;
            if (cylObjects[node].altsplice <= scales['altsplice'][0] || cylObjects[node].altsplice === undefined) {
                ringColor = colors_freindly[0];
            } else if (cylObjects[node].altsplice > scales['altsplice'][0] && cylObjects[node].altsplice <= scales['altsplice'][1]) {
                ringColor = colors_freindly[1];
            }
            else {
                ringColor = colors_freindly[2];
            }
            cylObjects[node].material.color.setHex(ringColor);
        }
    }
    /**
     * Delete all objects from the scene
     * Allows for the creation of the next
     * pathway after selecting another
     */
    function cleanScene() {
        //delete nodes
        for (node in nodeObjects) {
            scene.remove(nodeObjects[node]);
            delete nodeObjects[node];
        }
        //delete altsplice cylingders
        for (obj in cylObjects) {
            scene.remove(cylObjects[obj]);
            delete cylObjects[obj];
        }
        //delete node names
        for (obj in textLabels) {
            scene.remove(textLabels[obj]);
            delete textLabels[obj];
        }
        //delete expression lines
        for (obj in exprLines) {
            scene.remove(exprLines[obj]);
            delete exprLines[obj];
        }
        //delete path links
        for (obj in pathLines) {
            scene.remove(pathLines[obj]);
            delete pathLines[obj];
        }
        //delete CNV cones
        for (obj in cpCyl) {
            scene.remove(cpCyl[obj]);
            delete cpCyl[obj];
        }
        for (obj in familyLines) {
            scene.remove(familyLines[obj]);
            delete familyLines[obj];
        }
        for (obj in famObjects) {
            scene.remove(famObjects[obj]);
            delete famObjects[obj];
        }
        for (obj in famLabels) {
            scene.remove(famLabels[obj]);
            delete famLabels[obj];
        }
        for (obj in famExpr) {
            scene.remove(famExpr[obj]);
            delete famExpr[obj];
        }
        for (obj in famExprLable) {
            scene.remove(famExprLable[obj]);
            delete famExprLable[obj];
        }
        for (obj in famSplice) {
            scene.remove(famSplice[obj]);
            delete famSplice[obj];
        }
        for (obj in famCNV) {
            scene.remove(famCNV[obj]);
            delete famCNV[obj];
        }
        for (obj in circLines) {
            scene.remove(circLines[obj]);
            delete circLines[obj];
        }
    }
    /**
     * Adds a gene or endpoint to the scene
     * @param {type} levelObj object properties of the gene
     * @param {type} mat ThreeJS material object, contains color and texture information
     * @param {type} geom ThreeJS geometry element
     * @param {type} z Gene Name
     */
    function addNode(levelObj, mat, geom, z) {
        if (levelObj.familyMember && z === undefined)
            return;
        if (levelObj.family)
        mat.color.setHex('0x<?php echo $fam_node_color; ?>');
        var nodeObj = new THREE.Mesh(geom, mat);
        var displayCNV = levelObj.copyNumber;
        if (isNaN(displayCNV)) { // if cnv does not come in data (error in data)
            displayCNV = 0;
        }
        if (CNV_STATE % 3 === 0) {
            displayCNV = 0; //multiplication by 0 keeps everything flat
            if (z !== undefined)
                nodeObj.position.set(levelObj.position.x, z, levelObj.position.y);
            else
                nodeObj.position.set(levelObj.position.x, displayCNV * 20, levelObj.position.y);
        } else if (CNV_STATE % 3 === 1) {
            if (z !== undefined)
                nodeObj.position.set(levelObj.position.x, z, levelObj.position.y);
            else
                nodeObj.position.set(levelObj.position.x, displayCNV * 20, levelObj.position.y);
        } else if (CNV_STATE % 3 === 2) {
            var numCNV = 0;
            try {
                for (var pat in patients) {
                    if (patients[pat][levelObj.masterGene].copyNumber != 0)
                        numCNV += 1;
                }
            } catch (e) {
                numCNV = 0;
            }
            var freqCNV = 100 * numCNV / Object.keys(patients).length;
            if (freqCNV >= scales['cnv'][0]) {
                if (z !== undefined)
                    nodeObj.position.set(levelObj.position.x, z, levelObj.position.y);
                else
                    nodeObj.position.set(levelObj.position.x, sign(levelObj.copyNumber) * 20, levelObj.position.y);
            } else {
                if (z !== undefined)
                    nodeObj.position.set(levelObj.position.x, z, levelObj.position.y);
                else
                    nodeObj.position.set(levelObj.position.x, 0, levelObj.position.y);
            }
        }
        nodeObj.type = "node";
        nodeObj.masterGene = levelObj.masterGene;
        nodeObj.mutated = levelObj.mutated;
        nodeObj.altsplice = levelObj.altsplice;
        nodeObj.copyNumber = levelObj.copyNumber;
        nodeObj.expression = levelObj.expression;
        nodeObj.endpoint = levelObj.endpoint;
        nodeObj.ion = levelObj.ion;
        nodeObj.family = levelObj.family;
        nodeObj.name = levelObj.name;
        nodeObj.dispCNV = displayCNV;
        scene.add(nodeObj);
        if (z === undefined) {
            nodeObjects[nodeObjects.length] = nodeObj;
        }
        else {
            try {
                scene.remove(famObjects[levelObj.name])
            }
            catch (err) {
            }
            famObjects[levelObj.name] = nodeObj;
        }
        var text;
        if (nodeObj.masterGene !== undefined) {
            text = THREE.FontUtils.generateShapes(nodeObj.masterGene, {
                font: "helvetiker",
                size: 10
            });
        } else {
            text = THREE.FontUtils.generateShapes(levelObj.name, {
                font: "helvetiker",
                size: 10
            });
        }
        // objects to add the gene name to the scene
        var geom = new THREE.ShapeGeometry(text);
        var mat = new THREE.MeshBasicMaterial({
            color: "#<?php echo $node_name_color;?>"
        });
        var mesh = new THREE.Mesh(geom, mat);
        if (z !== undefined)
            mesh.position.set(levelObj.position.x + 10, z + 10, levelObj.position.y + 10);
        else
            mesh.position.set(levelObj.position.x + 10, displayCNV * 20 + 10, levelObj.position.y + 10);
        if (z === undefined)
            textLabels[textLabels.length] = mesh;
        else {
            famLabels[levelObj.name] = mesh;
        }
        scene.add(mesh);
        if (levelObj.open) {
            addFamNode(nodeObjects[nodeObjects.length - 1], true);
            nodeObjects[nodeObjects.length - 1].open = true;
        }
    }
    /**
     * Gives the appropriate color for the node or splice ring based on the percent cut off
     * @param {type} node The node object containing the mutation or splicing data
     * @param {type} colorType The color type to use (different percentage cutoffs for each type)
     * @returns {unresolved} The color of the node or ring
     */
    function choseColor(node, colorType) {
        if (colorType === 'mutated' && customMuts.length > 0 && (jQuery.inArray(node.masterGene, customMuts) === -1))
            return colors[0];
        if (node[colorType] <= scales[colorType][0] || node[colorType] === undefined) {
            return colors[0];
        } else if (node[colorType] > scales[colorType][0] && node[colorType] <= scales[colorType][1]) {
            return colors[1];
        }
        else {
            return colors[2];
        }
    }
    /**
     * Adds a CNV cone above or below each node
     * @param {type} node Node object containing the CNV information
     * @param {type} color The hex-color of the cone
     */
    function drawCone(node, color, z, name) {
        var sign = node.copyNumber < 0 ? -1 : 1;
        var coneMat = new THREE.MeshBasicMaterial({
            opacity: 0.5,
            transparent: true,
            color: color
        });
        if (CNV_STATE % 3 === 1) {
            if (node.copyNumber > 0)
                var coneGeom = new THREE.CylinderGeometry(0, 10, Math.abs(node.copyNumber * 20) - 5, 50, 50, false);
            else
                var coneGeom = new THREE.CylinderGeometry(10, 0, Math.abs(node.copyNumber * 20) - 5, 50, 50, false);
            var cylinder = new THREE.Mesh(coneGeom, coneMat);

            if (node.familyMember === undefined) {
                if (z === undefined)
                    cylinder.position.set(node.position.x, node.copyNumber * 10 - sign * 2.5, node.position.y);
                else
                    cylinder.position.set(node.position.x, z - sign * 20, node.position.y);
            } else {
                if (z === undefined)
                    cylinder.position.set(node.position.x, node.copyNumber * 10 - sign * 2.5, node.position.y);
                else
                    cylinder.position.set(node.position.x, z - sign * 10 - sign * Math.abs(node.copyNumber * 10), node.position.y);
            }
            scene.add(cylinder);
            if (z === undefined)
                cpCyl.push(cylinder);
            else
                famCNV[name] = cylinder;
        } else if (CNV_STATE % 3 === 2) {
            var numCNV = 0;
            try {
                for (var pat in patients) {
                    if (patients[pat][node.masterGene].copyNumber != 0)
                        numCNV += 1;
                }
            } catch (e) {
                numCNV = 0;
            }
            var freqCNV = 100 * numCNV / Object.keys(patients).length;
            if (freqCNV >= scales['cnv'][0]) {
                if (node.copyNumber > 0)
                    var coneGeom = new THREE.CylinderGeometry(0, 10, 20 - 5, 50, 50, false);
                else
                    var coneGeom = new THREE.CylinderGeometry(10, 0, 20 - 5, 50, 50, false);
                var cylinder = new THREE.Mesh(coneGeom, coneMat);
                if (node.familyMember === undefined) {
                    if (z === undefined)
                        cylinder.position.set(node.position.x, sign * (10 - 2.5), node.position.y);
                    else
                        cylinder.position.set(node.position.x, z - sign * (10 - 2.5), node.position.y);
                } else {
                    if (z === undefined)
                        cylinder.position.set(node.position.x, sign * (10 - 2.5), node.position.y);
                    else
                        cylinder.position.set(node.position.x, z - sign * (10 - 2.5), node.position.y);
                }
                scene.add(cylinder);
                if (z === undefined)
                    cpCyl.push(cylinder);
                else
                    famCNV[name] = cylinder;
            }
        }
    }
    /**
     * Adds a altsplice ring around a node
     * @param {type} node Node object containing altsplice information
     * @param {type} color The Color of the ring
     * @param {type} z (optional) The Z coord of the familymember none
     * @param {type} name (optional) The name of the familymember node
     */
    function drawCyl(node, color, z, name) {
        if (CNV_STATE % 3 === 2) {
            var numCNV = 0;
            try {
                for (var pat in patients) {
                    if (patients[pat][node.masterGene].copyNumber != 0)
                        numCNV += 1;
                }
            } catch (e) {
                numCNV = 0;
            }
            var freqCNV = 100 * numCNV / Object.keys(patients).length;
            if (freqCNV >= scales['cnv'][0]) {
                var displayCNV = node.copyNumber;
                var cylGeom = new THREE.CylinderGeometry(15, 15, 5, 50, 50, false)
                var cylMat = new THREE.MeshBasicMaterial({
                    color: color
                })
                var cylinder = new THREE.Mesh(cylGeom, cylMat);
                if (z === undefined)
                    cylinder.position.set(node.position.x, 20 * sign(displayCNV), node.position.y)
                else
                    cylinder.position.set(node.position.x, z, node.position.y)

                cylinder.altsplice = node.altsplice
                scene.add(cylinder);
                if (z === undefined)
                    cylObjects[cylObjects.length] = cylinder;
                else
                    famSplice[name] = cylinder;
            }else{
                var displayCNV = node.copyNumber;
                var cylGeom = new THREE.CylinderGeometry(15, 15, 5, 50, 50, false)
                var cylMat = new THREE.MeshBasicMaterial({
                    color: color
                })
                var cylinder = new THREE.Mesh(cylGeom, cylMat);
                if (z === undefined)
                    cylinder.position.set(node.position.x, 0, node.position.y)
                else
                    cylinder.position.set(node.position.x, z, node.position.y)

                cylinder.altsplice = node.altsplice
                scene.add(cylinder);
                if (z === undefined)
                    cylObjects[cylObjects.length] = cylinder;
                else
                    famSplice[name] = cylinder;
            }
        } else {
            var displayCNV = node.copyNumber;
            if (!$("#cnv_toggle").is(":checked"))
                displayCNV = 0
            var cylGeom = new THREE.CylinderGeometry(15, 15, 5, 50, 50, false)
            var cylMat = new THREE.MeshBasicMaterial({
                color: color
            })
            var cylinder = new THREE.Mesh(cylGeom, cylMat);
            if (z === undefined)
                cylinder.position.set(node.position.x, displayCNV * 20, node.position.y)
            else
                cylinder.position.set(node.position.x, z, node.position.y)

            cylinder.altsplice = node.altsplice
            scene.add(cylinder);
            if (z === undefined)
                cylObjects[cylObjects.length] = cylinder;
            else
                famSplice[name] = cylinder;
        }
    }
    /**
     * Generates a JPEG of the current view of the canvas
     * and opens a new tab displaying it for saving or printing
     */
    function print_view() {
        canvas = document.getElementsByTagName("canvas");
        canvas = canvas[1];
        var str = canvas.toDataURL("image/JPEG");
        popup = window.open();
        popup.document.write("<img src='" + str + "'</img>")
    }
    /**
     * pass a "GET" string to the modify ranges dialog to
     * pass the current scale settings
     */
    function modify_range() {
        var string = "m1=" + scales['mutated'][0];
        string += "&m2=" + (scales['mutated'][1] - scales['mutated'][0])
        string += "&m3=" + (100 - scales['mutated'][1])
        string += "&as1=" + scales['altsplice'][0];
        string += "&as2=" + (scales['altsplice'][1] - scales['altsplice'][0])
        string += "&as3=" + (100 - scales['altsplice'][1])
        string += "&cnv1=" + scales['cnv'][0];
        $("#dialog-modal").load("scaling_controlls.php?" + string).dialog({
            width: 600,
            modal: true,
            dialogClass: "",
            open: function () {
                controls = null
            },
            close: function () {
                controls = new THREE.OrbitControls(camera);
            }
        })
    }
    /**
     * 
     * @param {type} array An array to search
     * @returns {removeDuplicates.array} THe filtered array
     */
    function removeDuplicates(array) {
        for (var i = 1; i < array.length; ) {
            (array[i - 1] == array[i]) ? array.splice(i, 1) : i++;
        }
        return array;
    }
    /**
     * Adds members of a family of genes 175px above the
     * parent node
     * @param {type} parent Parent node of the family
     */
    function addFamNode(parent) {
        openNodes[parent.name] = 1;
        var i = 1;
        var x1 = nodes[parent.name].position.x;
        if (nodes[nodes[parent.name].parent] === undefined)
            var x2 = 0
        else
            var x2 = 0//nodes[nodes[parent.name].parent].position.x

        var y1 = nodes[parent.name].position.y
        if (nodes[nodes[parent.name].parent] === undefined)
            var y2 = 0
        else
            var y2 = 0//nodes[nodes[parent.name].parent].position.y
        var dy = y2 - y1;
        var dx = x2 - x1;

        var slopeP = -1 * dx / dy
        var plus = false;
        var material = new THREE.LineBasicMaterial({
            color: 0xFF0000,
            linewidth: 5
        });
        var geometry = new THREE.Geometry();
        var line = new THREE.Line(geometry, material); //expression liune
        scene.add(line)
        var distance = 60;
        for (var fam in parent.family) {
            var famNode = nodes[parent.family[fam]]
            var mult = i % 2 == 0 ? (i / -2) : ((i + 1) / 2);
            famNode.position = {};
            if (Math.abs(slopeP) < 100) {
                famNode.position.x = x1 + mult * distance / Math.sqrt(1 + (slopeP * slopeP));
                famNode.position.y = famNode.position.x * slopeP + y1 - slopeP * x1
            } else {
                famNode.position.x = x1
                famNode.position.y = y1 + distance * mult
            }
            var displayCNV = famNode.copyNumber;
            if (!$("#cnv_toggle").is(":checked"))
                displayCNV = 0 //multiplication by 0 keeps everything flat
            famNode.position.z = (parent.dispCNV * 20) + 175 + (displayCNV * 10)
            famNode.name = parent.family[fam];
            addNode(famNode, new THREE.MeshBasicMaterial({
                color: choseColor(famNode, 'mutated')
            }), new THREE.SphereGeometry(10, 10, 10), famNode.position.z)
            i++;
            var material = new THREE.LineBasicMaterial({
                color: 0xFF0000,
                linewidth: 5
            });
            var geometry = new THREE.Geometry();
            geometry.vertices.push(new THREE.Vector3(parent.position.x, parent.dispCNV * 20, parent.position.z));
            geometry.vertices.push(new THREE.Vector3(famNode.position.x, famNode.position.z, famNode.position.y));
            var line = new THREE.Line(geometry, material);
            scene.add(line)
            familyLines[parent.family[fam]] = line
            var material = new THREE.LineBasicMaterial({color: 0x66ff66, linewidth: 5});
            var geometry = new THREE.Geometry();
            geometry.vertices.push(new THREE.Vector3(famNode.position.x, famNode.position.z + 10, famNode.position.y));
            var exprVal;

            if (EXPR_STATE % 3 == 2) {
                exprVal = Math.round(100 * famNode.expression / genes[famNode.masterGene])
                if (genes[famNode.masterGene] === undefined) {
                    exprVal = undefined
                }
            }
            else {
                exprVal = famNode.expression
            }
            geometry.vertices.push(new THREE.Vector3(famNode.position.x, famNode.position.z + 10 + (exprVal === undefined ? 0 : exprVal), famNode.position.y));

            var line = new THREE.Line(geometry, material); //expression line
            var shapes, geom, mat, mesh;

            shapes = THREE.FontUtils.generateShapes(exprVal, {
                font: "helvetiker",
                size: <?php echo $text_size;?>
            });// add expression (RPKM) value above vertical line
            var geom = new THREE.ShapeGeometry(shapes);
            if (famNode.exprOutlier) {
                mat = new THREE.MeshBasicMaterial({color: "#<?php echo $out_color;?>"});
                if (EXPR_STATE % 3 == 2) {
                    mat = new THREE.MeshBasicMaterial({color: 0x<?php echo $reg_color;?>});
                }
            } else {
                mat = new THREE.MeshBasicMaterial({color: 0x<?php echo $reg_color;?>});
            }
            mesh = new THREE.Mesh(geom, mat);
            mesh.position.set(famNode.position.x, famNode.position.z + (exprVal === undefined ? 0 : exprVal) + 20, famNode.position.y);
            famExprLable[parent.family[fam]] = mesh;
            scene.add(mesh);
            scene.add(line);
            famExpr[parent.family[fam]] = line
            if (famNode.altsplice && $("#splicing_toggle").is(":checked"))
                drawCyl(famNode, choseColor(famNode, 'altsplice'), famNode.position.z, intersects[0].object.family[fam])
            if ($("#cnv_toggle").is(":checked")) {
                if (famNode.copyNumber > 0)
                    drawCone(famNode, "red", famNode.position.z, parent.family[fam])
                else if (famNode.copyNumber < 0)
                    drawCone(famNode, 'green', famNode.position.z, parent.family[fam])
            }
        }
    }
    function getMaxWidth(startingNode, lev) {
        maxWidth = 1;
        nextNodes = [];
        tempWidth = 0;
        var k = 0;
        currNodes = [startingNode];
        while (true) {
            for (var trav in currNodes) {
                tempWidth += lev[k][currNodes[trav]].linked.length;
                nextNodes = nextNodes.concat(lev[k][currNodes[trav]].linked);
            }
            if (tempWidth > maxWidth)
                maxWidth = tempWidth;
            if (nextNodes.length === 0)
                return maxWidth;
            else {
                currNodes = nextNodes;
                nextNodes = [];
                tempWidth = 0;
                k++;
            }
        }
    }
    function assocWithStart(startingNode, lev) {
        nodes = []
        nextNodes = [];
        var k = 0;
        currNodes = [startingNode];
        while (true) {
            for (var trav in currNodes) {
                nextNodes = nextNodes.concat(lev[k][currNodes[trav]].linked);
                nodes.push(currNodes[trav])
            }
            if (nextNodes.length === 0)
                return nodes;
            else {
                currNodes = nextNodes;
                nextNodes = [];
                tempWidth = 0;
                k++;
            }
        }
    }
    function getParent(child, lev) {
        for (var i in lev) {
            for (var node in lev[i]) {
                if (jQuery.inArray(child, lev[i][node].linked) > -1) {
                    return node;
                }
            }
        }
    }
    function relAtLevel(i, parent, lev, max) {
        var start = assocWithStart(max, lev)
        var number = 0;
        for (var node in lev[i]) {
            if (jQuery.inArray(node, start) > -1)
                number++;
        }
        return number;
    }
    function getLeafs(lev) {
        var allLeafs = []
        for (var topNode in lev[0]) {
            var x = traverse(subtree(lev, topNode));
            if (x instanceof Array)
                x = x.reverse();
            allLeafs = allLeafs.concat(x)
        }
        return allLeafs
    }
    function subtree(lev, root) {
        var levStart = 0;
        //get starting position
        for (var pos = 0; pos < lev.length - 1; pos++) {
            if (jQuery.inArray(root, Object.keys(lev[pos])) > -1) {
                levStart = pos;
                break;
            }
        }
        var levPrime = [];
        levPrime[0] = [];
        levPrime[0][root] = (lev[levStart][root]);
        var n = 1;
        levPrime[1] = []
        var children = lev[levStart][root].linked;
        while (true) {
            var tempChildren = [];
            var addedChild = false
            for (var child in children) {
                addedChild = true
                levPrime[n][children[child]] = (lev[levStart + n][children[child]])
                tempChildren = tempChildren.concat(lev[levStart + n][children[child]].linked)
            }
            children = tempChildren;
            if (!addedChild)
                break;
            n++;
            levPrime[n] = []
        }
        return levPrime;
    }
    function traverse(tree, array) {
        var array = array || [];
        /// 2 level         ///one node                                  /// no children                                        //empty level 2                                      
        if (tree.length === 2 && Object.keys(tree[0]).length === 1 && tree[0][Object.keys(tree[0])[0]].linked.length === 0 && tree[1].length === 0) {
            return (Object.keys(tree[0])[0]);
        } else {
            for (var vertex in tree[1]) {
                array = array.concat(traverse(subtree(tree, tree[1][vertex].name)));
            }
        }
        return array;

    }
    function getLevel(lev, node) {
        for (var pos = 0; pos < lev.length; pos++) {
            if (jQuery.inArray(node, Object.keys(lev[pos])) > -1) {
                return pos;
            }
        }
    }
    function averageAngle(thetas) {
        var x = 0;
        var y = 0;
        for (var theta in thetas) {
            x += Math.cos(thetas[theta])
            y += Math.sin(thetas[theta])
        }

        return Math.atan2(y, x);
    }

    function viewDocumentation() {
        window.open('http://www.bioinfo.iric.ca/~wilhelmb/cas/Cascade intro.pdf');
    }
    function barchart() {
        var chart = new Highcharts.Chart({
            chart: {
                type: 'column',
                renderTo: "expression_graph"
            },
            title: {
                text: 'Expression of Patients'},
            subtitle: {
                text: 'For Gene ' + MAST
            },
            xAxis: {
                categories: PAT,
                labels: {
                    enabled: false
                }
            },
            yAxis: {
                title: {
                    text: 'Log Expression'
                },
                min: 0
            },
            tooltip: {
                crosshairs: true,
                shared: true
            },
            plotOptions: {
                spline: {
                    marker: {
                        radius: 4,
                        lineColor: '#666666',
                        lineWidth: 1
                    }
                }
            },
            series: [{
                    name: 'RPKM',
                    marker: {
                        symbol: 'diamond'
                    },
                    data: log(BYPAT)
                }]
        });
    }
    function boxplot() {
        console.log(BYPAT_mod)
        var chart = new Highcharts.Chart({
            chart: {
                type: 'boxplot',
                renderTo: "expression_graph"
            },
            title: {
                text: 'Expression of Patients'},
            subtitle: {
                text: 'For Gene ' + MAST
            },
            xAxis: {
                categories: ["Expression"],
                labels: {
                    enabled: false
                }

            },
            yAxis: {
                title: {
                    text: 'Expression'
                },
                min: 0
            },
            tooltip: {
                crosshairs: true,
                shared: true
            },
            plotOptions: {
                spline: {
                    marker: {
                        radius: 4,
                        lineColor: '#666666',
                        lineWidth: 1
                    }
                }
            },
            series: [{
                    name: 'RPKM',
                    data: [[BYPAT_mod[0], quantile(BYPAT_mod, .25), simpleMedian(BYPAT_mod), quantile(BYPAT_mod, .75), BYPAT_mod[BYPAT_mod.length - 1]]]
                }]
        });
    }
    function toggle_graph() {
        if (BARCHART)
            boxplot()
        else
            barchart()

        BARCHART = !BARCHART;
    }
    function load_left() {
        if (parseInt(currentPath) > 0) {
            currentPath = parseInt(currentPath) - 1;
            loadPathwayFromDB(pathIds[currentPath])
            if (currentPath === 0)
                $("#pathway_title").html(pathNames[parseInt(currentPath)] + "<input type='button' class='pathway_scroll' onclick='load_right()' value='&#8594;'/>")
            else if (currentPath === pathIds.length - 1)
                $("#pathway_title").html("<input type='button' class='pathway_scroll' onclick='load_left()' value='&#8592;'/>" + pathNames[parseInt(currentPath)])
            else
                $("#pathway_title").html("<input type='button' class='pathway_scroll' onclick='load_left()' value='&#8592;'/>" + pathNames[parseInt(currentPath)] + "<input type='button' class='pathway_scroll' onclick='load_right()' value='&#8594;'/>")

            makeScrollButton();
        }

    }
    function load_right() {
        if (parseInt(currentPath) < pathIds.length) {
            currentPath = parseInt(currentPath) + 1;
            loadPathwayFromDB(pathIds[currentPath])
            if (currentPath === 0)
                $("#pathway_title").html(pathNames[parseInt(currentPath)] + "<input type='button' class='pathway_scroll' onclick='load_right()' value='&#8594;'/>")
            else if (currentPath === pathIds.length - 1)
                $("#pathway_title").html("<input type='button' class='pathway_scroll' onclick='load_left()' value='&#8592;'/>" + pathNames[parseInt(currentPath)])
            else
                $("#pathway_title").html("<input type='button' class='pathway_scroll' onclick='load_left()' value='&#8592;'/>" + pathNames[parseInt(currentPath)] + "<input type='button' class='pathway_scroll' onclick='load_right()' value='&#8594;'/>")

            makeScrollButton();
        }
    }

    function makeScrollButton() {
        $("#pathway_title input").button()
    }

    function toggle_circles() {
        CIRCLES_ENABLED = !CIRCLES_ENABLED;
        if (CIRCLES_ENABLED) {
            $("#circle_toggle").attr("value", 'Disable Levels')
            for (obj in circLines) {
                scene.add(circLines[obj]);
            }
        } else {
            $("#circle_toggle").attr("value", 'Enable Levels')
            for (obj in circLines) {
                scene.remove(circLines[obj]);
            }
        }
    }
    function sel_comp() {
        if (ANYTHING_LOADED) {
            $("#dialog-modal").load("comparison_table.php").dialog({
                width: 600,
                modal: true,
                dialogClass: "",
                open: function () {
                    controls = null;
                },
                close: function () {
                    controls = new THREE.OrbitControls(camera);
                }
            })
        } else {
            alert("Select Pathway")
        }
    }
    function sign(x) {
        if (x > 0)
            return 1;
        if (x < 0)
            return -1;
        if (x === 0)
            return 0;
    }
    var CIRCLES_ENABLED = true;
    var MAST = "";
    var BYPAT = [];
    var BYPAT_mod = [];
    var PAT = [];
    var BARCHART = true;
    var SCROLLING = false;
    var CNV_STATE = 0;
    var EXPR_STATE = 1;
