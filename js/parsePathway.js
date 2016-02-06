var xyz;
function parsePathway(data) {
    data = data.split("\n");
    data = data.slice(1);

    if (data.slice(data.length - 1)[0].replace(/ /g, '') === '') {
        data = data.slice(0, data.length - 1);
    }
    var genes = [];
    for (var i = 0; i < data.length; i++) {
        var gene = data[i];
        gene = gene.split(",");
        genes[gene[0].split(":")[0]] = {};
        genes[gene[0].split(":")[0]].children = gene.slice(1, gene.length - 1);
        genes[gene[0].split(":")[0]].type = gene.slice(gene.length - 1);
        genes[gene[0].split(":")[0]].masterGene = gene[0].split(":")[0];
        genes[gene[0].split(":")[0]].name = gene[0];
    }
    xyz = genes;
    genes = findFamilyMembers(genes);
    genes = splitOutDuplicates(genes);
    var structure = structureGenes(genes);

    return {genes: genes, structure: structure};
}
function splitOutDuplicates(genes) {
    var parents = [];
    for (var gene in genes) {
        parents.push(gene.split(":")[0]);
    }

    var children = getAllChildren(genes);
    //find missing entries
    for (i = 0; i < children.length; i++) {
        if (jQuery.inArray(children[i], parents) === -1) {
            genes[children[i]] = {type: "gene", children: []};
            alert("Missing Node Entry For: " + children[i] + " Its Has Been Added As a Top Level Gene");
        }
    }

    xyz = genes;
    var offsetId = 0;
    while (true) {
        var wasChangeMade = false;

        children = getAllChildren(genes);
        children = children.sort();

        var duplicates = [];
        for (var i = 0; i < children.length - 1; i++) {
            if (children[i] === children[i + 1]) //if that node appears as a child more than once
                duplicates.push(children[i]);
        }
        duplicates = removeDuplicates(duplicates);
        for (var i = 0; i < duplicates.length; i++) {
            for (gene in genes) {
                for (var k = 0; k < genes[gene].children.length; k++) {
                    if (genes[gene].children[k] === duplicates[i]) {
                        var tmp = jQuery.extend(true, {}, genes[genes[gene].children[k]]);
                        genes[genes[gene].children[k] + "-" + offsetId] = jQuery.extend(true, {}, tmp); //copy the refence object into an obj with the same name (+ unique number)
                        genes[gene].children[k] = genes[gene].children[k] + "-" + offsetId; //give the link the corrected name
                        offsetId = offsetId + 1;
                        wasChangeMade = true;
                    }
                }
            }
        }
        for (var duplicate in duplicates) {
            delete genes[duplicates[duplicate]]; // delete the old (no number) node

        }
        if (!wasChangeMade)
            break;
    }
    return genes;

}

function findFamilyMembers(genes) {
    var famMembers = [];
    var famOwners = [];
    for (var gene in genes) {
        if (genes[gene].name.split(":").length > 1) {
            famOwners.push(genes[gene].name.split(":")[0])
            var family = genes[gene].name.split(":")[1]
            family = family.slice(2, family.length - 1).split("|");
            for (var i = 0; i < family.length; i++) {
                famMembers.push(family[i]);
            }
        }
    }
    for (var gene in genes) {
        if (jQuery.inArray(genes[gene].name, famMembers) > -1) {
            genes[gene].familyMember = true;
        } else {
            genes[gene].familyMember = false;
        }
        if (jQuery.inArray(genes[gene].name.split(":")[0], famOwners) > -1) {
            genes[gene].familyNode = true;
        } else {
            genes[gene].familyNode = false;
        }
    }
    return genes;
}
function getAllChildren(genes) {
    var children = [];
    for (var gene in genes) {
        if (genes[gene].children[0].length > 0) {
            children = children.concat(genes[gene].children); // list all children
        }
    }
    return children
}

function removeDuplicates(array) {
    for (var i = 1; i < array.length;) {
        (array[i - 1] == array[i]) ? array.splice(i, 1) : i++;
    }
    return array;
}

function structureGenes(genes) {
    var topGenes = [];
    for (var gene in genes) {
        var topLevel = true;
        for (var nodeSearch in genes) {
            if (jQuery.inArray(gene.split(':')[0], genes[nodeSearch].children) > -1) { //if the node is another's child
                topLevel = false;
                break;
            }
        }
        if (topLevel)
            topGenes.push(gene);
    }
    var level = 0;
    var levels = [[]];
    levels[level] = topGenes;
    while (true) {
        var addedNode = false;
        levels.push([]); //create child level
        for (var i = 0; i < levels[level].length; i++) {
            for (var j = 0; j < genes[levels[level][i]].children.length; j++) { // for all of the parent's children
                if (genes[levels[level][i]].children[j] !== "") {
                    genes[genes[levels[level][i]].children[j]].parent = parent;
                    levels[level + 1].push(genes[levels[level][i]].children[j]); // the level object is a pointer to the origional node object
                    addedNode = true;
                }
            }
        }
        level = level + 1;
        if (!addedNode)
            break;
    }
    return levels.slice(0, levels.length - 1)
}