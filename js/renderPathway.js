function renderPathway(genes, structure) {
    var leafs = getLeafs(genes);
    console.log(leafs);
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