function renderPathway() {

}

var controls;
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


    var ambientLight = new THREE.AmbientLight(0x111111);
    scene.add(ambientLight);
    // initialize object to perform world/screen calculations
    var projector = new THREE.Projector();
    // when the mouse moves, call the given function

    /////// Gene Mouseovers /////////
    // create a canvas element
    var canvas1 = document.createElement('canvas');
    var context1 = canvas1.getContext('2d');

    context1.font = "Bold 20px Arial";
    // canvas contents will be used for a texture
    var texture1 = new THREE.Texture(canvas1);
    texture1.needsUpdate = true;
    ///////////////////////////////////////

    var spriteMaterial = new THREE.SpriteMaterial({
        map: texture1,
        useScreenCoordinates: true,
        alignment: THREE.SpriteAlignment.topLeft
    });
    var sprite1 = new THREE.Sprite(spriteMaterial);
    sprite1.scale.set(200, 100, 1.0);
    sprite1.position.set(50, 50, 0);
    scene.add(sprite1);
    return {renderer: renderer, scene: scene, camera: camera};
}

function drawLevels(scene, numLevels,settings) {
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
            geometry.vertices.push(new THREE.Vertex(new THREE.Vector3(Math.cos(segment) * radius, 0, Math.sin(segment) * radius)));
        }
        var line = new THREE.Line(geometry, material);
        scene.add(line);
    }
    return scene;
}


function animate() {
    requestAnimationFrame(animate);
    render();
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
        var material = new THREE.MeshBasicMaterial({color: settings.nodeColorLowFrequency});
        var node = new THREE.Mesh(geometry, material);
        console.log([gene, genes[gene].x, genes[gene].y, genes[gene].z])
        node.position.z = genes[gene].x;
        node.position.x = genes[gene].y;
        node.position.y = genes[gene].z;
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

var scene;
var camera;
var renderer;
function prepareAnimation(scene_val, camera_val, renderer_val) {
    scene = scene_val;
    camera = camera_val;
    renderer = renderer_val;
}
