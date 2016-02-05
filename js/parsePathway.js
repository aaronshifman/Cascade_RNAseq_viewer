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
        genes[gene[0]] = {};
        genes[gene[0]].children = gene.slice(1, gene.length - 1);
        genes[gene[0]].type = gene.slice(gene.length - 1)
        genes[gene[0]].masterGene = gene[0].split(":")[0];
    }
    xyz = genes;
    var genes = splitOutDuplicates(genes);
    return structureGenes(genes);
}
function splitOutDuplicates(genes) {
    var topGenes = [];
    var parents = [];
    for (var gene in genes) {
        parents.push(gene.split(":")[0]);
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

    var children = getAllChildren(genes);
    //find missing entries
    for (var i = 0; i < children.length; i++) {
        if (jQuery.inArray(children[i], parents) === -1) {
            genes[children[i]] = {type: "gene", children: []};
            alert("Missing Node Entry For: " + children[i] + " Its Has Been Added As a Top Level Gene");
        }
    }

    xyz = genes;
    var offsetId = 0;
    var timesThrough = 0
    console.log(jQuery.extend(true, {}, genes));
    while (true) {
        if (timesThrough > 0)
            break;
        timesThrough = timesThrough + 1;

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
                        offsetId = offsetId+1;
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

function structureGenes(genes){

}