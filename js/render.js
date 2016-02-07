function renderPathway() {

}

var controls;
var projector;
function initScene() {
    var scene = new THREE.Scene();
    var SCREEN_WIDTH = window.innerWidth * 0.95, SCREEN_HEIGHT = window.innerHeight * 0.95;
    var VIEW_ANGLE = 45, ASPECT = SCREEN_WIDTH / (SCREEN_HEIGHT), NEAR = 0.1, FAR = 20000;
    var camera = new THREE.PerspectiveCamera(VIEW_ANGLE, ASPECT, NEAR, FAR);
    scene.add(camera);
    camera.position.set(0, 300, 1600);

    if (Detector.webgl)
        var renderer = new THREE.WebGLRenderer({antialias: true, preserveDrawingBuffer: true});
    else
        var renderer = new THREE.CanvasRenderer();

    renderer.setSize(SCREEN_WIDTH, SCREEN_HEIGHT);
    //renderer.setClearColorHex(0x222222, 1);
    // create a div element to contain the renderer
    var container = document.createElement('div');
    document.body.appendChild(container);
    // attach renderer to the container div
    container.appendChild(renderer.domElement);
    //THREEx.WindowResize(renderer, camera);
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

    projector = new THREE.Projector();
    // initialize object to perform world/screen calculations
    document.addEventListener('mousemove', onDocumentMouseMove, false);
    // when the mouse moves, call the given function
    /////// Gene Mouseovers /////////
    return {renderer: renderer, scene: scene, camera: camera};
}

var mouse = new THREE.Vector2();
function onDocumentMouseMove(evtent) {
    mouse.x = ( event.clientX / (window.innerWidth * .95) ) * 2 - 1;
    mouse.y = -( event.clientY / (window.innerHeight * .95) ) * 2 + 1;
}

function drawLevels(scene, numLevels, settings) {
    var segmentSize = 360 / 100; //degree for segment
    for (var i = 0; i <= numLevels; i++) {
        var resolution = 100 * i;  //100 segments in circle
        var radius = 100 * i;
        var geometry = new THREE.Geometry();
        var material = new THREE.LineBasicMaterial({
            transparent: true,
            color: settings.levelRingColor,
            opacity: (1.1 - (i / (2 * numLevels)))
        });
        for (var j = 0; j <= resolution; j++) {
            var segment = (j * segmentSize) * Math.PI / 180;
            geometry.vertices.push(new THREE.Vector3(Math.cos(segment) * radius, 0, Math.sin(segment) * radius));
        }
        var line = new THREE.Line(geometry, material);
        scene.add(line);
    }
    return scene;
}


function animate() {
    requestAnimationFrame(animate);
    render();
    update();
}
var INTERSECTED;
function update() {
    var vector = new THREE.Vector3(mouse.x, mouse.y, 1);
    projector.unprojectVector(vector, camera);
    var ray = new THREE.Raycaster(camera.position, vector.sub(camera.position).normalize());
    var intersects = ray.intersectObjects(scene.children);

    if (intersects.length > 0) {
        if ((intersects[0].object != INTERSECTED) && (intersects[0].object.type === "node")) {
            if (INTERSECTED)
                INTERSECTED.material.color.setHex(INTERSECTED.currentHex);
            INTERSECTED = intersects[0].object;
            INTERSECTED.currentHex = INTERSECTED.material.color.getHex();
            INTERSECTED.material.color.setHex(0xffff00);
        }
    } else {
        if (INTERSECTED)
            INTERSECTED.material.color.setHex(INTERSECTED.currentHex);
        INTERSECTED = null;
    }
    controls.update();
}
function render() {
    renderer.render(scene, camera);
    for (var i = 0; i < scene.children.length; i++) {
        if (scene.children[i] instanceof THREE.Mesh) {
            scene.children[i].lookAt(camera.position);
        }
    }
}
function drawNodes(scene, genes, settings) {
    for (var gene in genes) {
        var geometry = new THREE.SphereGeometry(10, 10, 10);
        if (genes[gene].familyNode)
            var material = new THREE.MeshBasicMaterial({color: settings.familyNodeColor});
        else
            var material = new THREE.MeshBasicMaterial({color: settings.nodeColorLowFrequency});
        var node = new THREE.Mesh(geometry, material);
        console.log([gene, genes[gene].x, genes[gene].y, genes[gene].z])
        node.position.z = genes[gene].x;
        node.position.x = genes[gene].y;
        node.position.y = genes[gene].z;
        node.type = "node"
        node.masterGene = genes[gene].masterGene
        scene.add(node);
    }
    return scene;
}

function drawNames(scene, genes, settings) {
    for (var gene in genes) {
        var text = THREE.FontUtils.generateShapes(genes[gene].masterGene, {
            font: "helvetiker",
            size: settings.textSize
        });
        var geom = new THREE.ShapeGeometry(text);
        var mat = new THREE.MeshBasicMaterial({
            color: settings.nameColor
        });
        var name = new THREE.Mesh(geom, mat);
        name.position.set(genes[gene].y + 10, genes[gene].z + 10, genes[gene].x + 10);
        scene.add(name);
    }
    return scene;
}

function drawLinks(scene, genes, settings) {
    var material = new THREE.LineBasicMaterial({
        transparent: true,
        color: settings.linkColor,
    });
    for (var gene in genes) {
        for (var i = 0; i < genes[gene].children.length; i++) {
            if (genes[gene].children[i] !== "") {
                var geometry = new THREE.Geometry();
                var z = genes[gene].x
                var x = genes[gene].y
                var y = genes[gene].z
                geometry.vertices.push(new THREE.Vector3(x, y, z));
                z = genes[genes[gene].children[i]].x;
                x = genes[genes[gene].children[i]].y;
                y = genes[genes[gene].children[i]].z;
                geometry.vertices.push(new THREE.Vector3(x, y, z));
                var line = new THREE.Line(geometry, material);
                scene.add(line);
            }
        }
    }
    return scene;
}
var scene;
var camera;
var renderer;
function prepareAnimation(scene_val, camera_val, renderer_val) {
    scene = scene_val;
    camera = camera_val;
    renderer = renderer_val;
}
function drawScene(scene, genes, structure, settings) {
    if (settings.ringsOn)
        scene = drawLevels(scene, structure.length, settings);
    scene = drawNodes(scene, genes, settings);
    scene = drawNames(scene, genes, settings);
    scene = drawLinks(scene, genes, settings);
    return scene
}