function drawData(data) {
    for (var i = 0; i < scene.children.length; i++) {
        if ((scene.children[i].type === 'node') && (!scene.children[i].familyNode)) {
            var expression = [];
            var mutation = 0;
            var count = 0;
            var patients = []
            var altSplice = 0
            for (var patient in data) {
                try {
                    expression.push(data[patient][scene.children[i].masterGene].expression);
                    mutation = mutation + parseInt(data[patient][scene.children[i].masterGene].newSNP); //TODO: Only handles newSNPs
                    altSplice = altSplice + parseInt(data[patient][scene.children[i].masterGene].altsplice);
                    count = count+1;
                    patients[patient] = data[patient][scene.children[i].masterGene]
                } catch (err) {
                    expression.push(0);
                }
            }
            var position = scene.children[i].position;
            drawExpression(position.x, position.y, position.z, median(expression));
            drawExpressionValue(position.x, position.y, position.z, median(expression));
            percentMut = mutation/count;
            setMutations(i,isNaN(percentMut)?0:percentMut); //0 if undefined
            scene.children[i].patients = patients;
            percentSplice = altSplice/count;
            drawaltSpliceCyl(i,isNaN(percentSplice)?0:percentSplice);
        }
    }
}
function drawExpression(x, y, z, median) {
    var expressionHeight = 0.1*median;
    var material = new THREE.LineBasicMaterial({
        transparent: true,
        color: settings.linkColor,
    });
    var geometry = new THREE.Geometry();
    geometry.vertices.push(new THREE.Vector3(x, y, z));
    geometry.vertices.push(new THREE.Vector3(x, y+ expressionHeight, z));
    var line = new THREE.Line(geometry, material);
    line.type = "expression"
    scene.add(line);
    return scene;
}
function drawExpressionValue(x, y, z, median) {
    var expressionHeight = 0.1*median;
    var text = THREE.FontUtils.generateShapes(parseInt(median), {
        font: "helvetiker",
        size: settings.textSize
    });
    var geom = new THREE.ShapeGeometry(text);
    var mat = new THREE.MeshBasicMaterial({
        color: settings.nameColor
    });
    var name = new THREE.Mesh(geom, mat);
    name.position.set(x + 10, y + expressionHeight, z + 10);
    name.type = "expression_value"
    scene.add(name);
}
function setMutations(node_id,value){
    var color;
    if (value <= settings.nodeLowFrequencyValue){
        color = settings.nodeColorLowFrequency
    }else if ((value > settings.nodeLowFrequencyValue) && (value <= settings.nodeHighFrequencyValue)){
        color = settings.nodeColorMedFrequency
    }else{
        color = settings.nodeColorHighFrequency
    }
    scene.children[node_id].material.color.setHex(hexStringToHex(color));
}
function hexStringToHex(hex){
    var color = /^\#([0-9a-f]{6})$/i.exec(hex);
    return parseInt(color[1],16)
}
function drawaltSpliceCyl(node_id,value){
    var color;
    if (value <= settings.altLowFrequencyValue){
        color = settings.nodeColorLowFrequency
    }else if ((value > settings.altLowFrequencyValue) && (value <= settings.altHighFrequencyValue)){
        color = settings.nodeColorMedFrequency
    }else{
        color = settings.nodeColorHighFrequency
    }
    var cylGeom = new THREE.CylinderGeometry(15, 15, 5, 50, 50, false)
    var cylMat = new THREE.MeshBasicMaterial({color: color})
    var cylinder = new THREE.Mesh(cylGeom, cylMat);
    cylinder.position = scene.children[node_id].position;
    cylinder.position.y = cylinder.position.y+5
    scene.add(cylinder); //TODO: These are rotating to the camera
}