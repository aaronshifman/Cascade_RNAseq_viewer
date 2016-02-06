var g;
var s;
function calculatePathwayPosition(genes, structure) {
    g = genes;
    s = structure;
    var leafs = getLeafs(genes, structure);
    var toAdd = [];
    var sep = 2 * Math.PI / leafs.length;

    if (structure[0].length === 1) {
        sep = sep / 2
    }

    for (var i = 0; i < leafs.length; i++) {
        var ringLevel = getLevel(structure, leafs[i]);
        genes[leafs[i]].x = 100 * (ringLevel + 1) * Math.sin(sep * i);
        genes[leafs[i]].y = 100 * (ringLevel + 1) * Math.cos(sep * i);
        genes[leafs[i]].z = 0;
        genes[leafs[i]].angle = sep * i;
        toAdd.push(genes[leafs[i]].parent);
    }
    toAdd = $.grep(toAdd, function (v, k) {
        return $.inArray(v, toAdd) === k;
    });


    for (var i = structure.length - 2; i >= 0; i--) {
        var indexAdded = [];
        var tempToAdd = [];
        for (var j = 0; j <= toAdd.length; j++) {
            var ringLevel = getLevel(structure, toAdd[j]);
            if (ringLevel === i) {
                var angles = [];
                for (var k = 0; k < genes[toAdd[j]].children.length; k++) {
                    angles.push(genes[genes[toAdd[j]].children[k]].angle);
                }
                var angle = averageAngle(angles);
                genes[toAdd[j]].x = (ringLevel + 1) * 100 * Math.sin(angle);
                genes[toAdd[j]].y = (ringLevel + 1) * 100 * Math.cos(angle);
                genes[toAdd[j]].z = 0;
                genes[toAdd[j]].angle = angle;
                tempToAdd.push(genes[toAdd[j]].parent)
                indexAdded.push(j)
            }
        }
        for (var j = 0; j < indexAdded.length; j++) {
            delete toAdd[indexAdded[j]];
        }
        toAdd.filter(function (n) {
            return n != undefined
        });
        toAdd = toAdd.concat(tempToAdd);
    }
    return genes;
}

function getLeafs(genes, structure) {
    var jQueryStructure = createjQueryStructure(genes, structure);
    var leafs = [];
    $(":not(:has(*))", jQueryStructure).each(function () {
        leafs.push(this.id);
    });
    return leafs;
}
function createjQueryStructure(genes, structure) {
    var string = "<tree></tree>"
    var i = 0;
    for (var j = 0; j < structure[i].length; j++) {
        string = $(string).append("<" + structure[i][j] + " id='" + structure[i][j] + "'></" + structure[i][j] + ">");
    }
    for (var i = 1; i < structure.length; i++) {
        for (var j = 0; j < structure[i].length; j++) {
            var parent = "#" + genes[structure[i][j]].parent;
            $(parent, string).append("<" + structure[i][j] + " id='" + structure[i][j] + "'></" + structure[i][j] + ">");
        }
    }
    return string;
}
function getLevel(structure, node) {
    for (var i = 0; i < structure.length; i++) {
        if (jQuery.inArray(node, structure[i]) > -1) {
            return i;
        }
    }
}