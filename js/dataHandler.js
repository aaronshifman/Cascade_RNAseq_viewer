function drawData(data) {
    for (var i = 0; i < scene.children.length; i++) {
        if (scene.children[i].type === 'node') {
            var expression = [];
            for (var patient in data) {
                try {
                    expression.push(data[patient][scene.children[i].masterGene].expression)
                } catch (err) {
                    expression.push(0);
                }
            }
            var position = scene.children[i].position;
            drawExpression(position.x, position.y, position.z, median(expression));
            drawExpressionValue(position.x, position.y, position.z, median(expression));
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