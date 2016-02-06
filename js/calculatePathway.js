function calculatePathwayPosition(genes, structure) {
    var leafs = getLeafs(genes);
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

    var added = leafs;
    var tempPars = []
    for (var i = structure.length - 1; i >= 0; i--) {
        for (var j = 0; j < toAdd.length; j++) {
            var ringLevel = getLevel(structure, toAdd[j]);
            if (ringLevel === i) {
                var angle = 0;
                if ((genes[toAdd[j]].children.length === 1) && (genes[toAdd[j]].children[0] !== "")) {
                    angle = genes[genes[toAdd[j]].children[0]].angle
                } else {
                    var angles = [];
                    for (var k = 0; k < genes[toAdd[j]].children.length; k++) {
                        angles.push(genes[genes[toAdd[j]].children[k]].angle);
                    }
                    angle = averageAngle(angles);
                }
                genes[toAdd[j]].x = (ringLevel + 1) * 100 * Math.sin(angle);
                genes[toAdd[j]].y = (ringLevel + 1) * 100 * Math.cos(angle);
                genes[toAdd[j]].z = 0;
                genes[toAdd[j]].angle = angle;

                added.push(genes[toAdd[j]].name);
                if (genes[toAdd[j]].parent !== undefined) {
                    tempPars = tempPars.concat(genes[toAdd[j]].parent);
                }
            } else {
                tempPars.push(toAdd[j]);
            }
        }
        toAdd = tempPars;
        toAdd = $.grep(toAdd, function (v, k) {
            return $.inArray(v, toAdd) === k;
        });
        tempPars = [];
    }
    return genes;
}

function getLeafs(genes) {
    var leafs = [];
    for (var gene in genes) {
        if (genes[gene].children.length === 1 && genes[gene].children[0] === "") {
            leafs.push(gene);
        }
    }
    return leafs
}
function getLevel(structure, node) {
    for (var i = 0; i < structure.length; i++) {
        if (jQuery.inArray(node, structure[i]) > -1) {
            return i;
        }
    }
}