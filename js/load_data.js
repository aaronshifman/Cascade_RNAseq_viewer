/**
 * Read the pathway data from a file
 * @param {type} evt The filebrowser object
 */
function loadPathwayFromFile(evt) {
    fromDb = false; // not from the database
    var file1;
    readFiles(evt.target.files[0], function (e) {
        file1 = (e.target.result); //file lines
        origNodes = file1;
        parsePathway(file1);
    });
}
/**
 * Load the pathway information (same as file format) from the database
 * @param {type} id The Pathway Id
 */
function loadPathwayFromDB(id) {
    fromDb = true; //pathway came from the database
    /**
     * load pathway lines and generate "loading..." message
     */
    $.get('load_pathway.php?path=' + id, function (data) {
        $("#dialog-modal").html("<image src='css/images/loading_gif.gif'> Loading...").dialog({
            modal: true,
            dialogClass: "no-close",
            open: function () {
                controls = null;
            },
            close: function () {
                controls = new THREE.OrbitControls(camera);
            }
        });
        file1 = data;
        origNodes = file1;
        parsePathway(file1);
        loadPatientsFromDB(); //load the patients (gene data) from the database once the pathway is loaded
    })
}

function loadControllFile(evt) {
    var file1;
    readFiles(evt.target.files[0], function (e) {
        file1 = (e.target.result);
        controllFile = file1
        parseControll(file1);
    });
}

function loadControllFromDB(id) {
    var obj = {
        genes: Object.keys(unSplit_nodes),
        newsnp: $("#newsnp_toggle").is(":checked"),
        knownsnp: $("#knownsnp_toggle").is(":checked"),
        newindel: $("#newindel_toggle").is(":checked"),
        source: id
    }
    $.post('load_patient_IRIC.php', obj, function (data) {
        parseControll(data, 1, false)
    });
}

/**
 * Loads patient (gene data) from a file
 * @param {type} evt The filebrowser object 
 */
function loadPatientsFromFile(evt) {
    var file1;
    fromDb = false;
    readFiles(evt.target.files[0], function (e) {
        file1 = (e.target.result);
        controllData = file1
        parsePatients(file1);
    });
}


/**
 * Load the patient information (same as file format) from the database
 */
function loadPatientsFromDB(reload) {
    fromDb = true;
    // various attributes about what data to get from the database
    // types of snps, indels, which genes, and which patients
    var obj = {
        genes: Object.keys(unSplit_nodes),
        newsnp: $("#newsnp_toggle").is(":checked"),
        knownsnp: $("#knownsnp_toggle").is(":checked"),
        newindel: $("#newindel_toggle").is(":checked"),
        source: $("input[name='data_source']:checked").attr("id")
    }
    $.post('load_patient_IRIC.php', obj, function (data) {
        parsePatients(data, 1, reload)
    });
}
var genes = [];
function parseControll(pat_lines, start, reload) {
    if (start === undefined)
        start = 2;
    genes = []
    patsUsed = []
    x = lines = pat_lines.split("\n");
    for (var i = start; i < lines.length; i++) {
        lines[i] = lines[i].replace(/(\r\n|\n|\r)/gm, ""); //remove all extraneous characters
        split = lines[i].split(",") // split the line into its components
        if (split[1] !== undefined) { // if there isn't a blank last line
            if (genes[split[0]] === undefined) {
                genes[split[0]] = []
            }
            genes[split[0]].push({expression: Math.round(split[2] * 10) / 10})
        }
    }
    for (gene in genes) {
        genes[gene] = median(genes[gene])
    }
    initNodes(nodes)
}
/**
 * Convert the pathway file (or db) into an array of node objects
 * and then render these objects onto the canvas
 * @param {type} path_lines The Lines corresponding to the lines of the pathway in the file
 * @returns {undefined}
 */
function parsePathway(path_lines) {
    var lines = path_lines.split("\n")
    if (lines.length === 1)
        lines = path_lines.split("\r") //hack for a windows based file (\r\n)
    nodes = []
    for (var i = 1; i < lines.length; i++) {
        if (lines[i] !== "") { //incase there is a final cariage return at the last line
            lines[i] = lines[i].replace(/(\r\n|\n|\r)/gm, ""); //remove all extraneous characters
            var split = lines[i].split(","); //break the line into its components
            while (true) {
                if (split[split.length - 1] === "") {
                    split = split.splice(0, split.length - 1) //if the file is from excel the smaller lines will have ,,,, on their end
                } else {
                    break;
                }
            }
            if (split[0] !== "") { //another check for blank lines
                if (split[0].split("|").length > 1) {
                    nodes[split[0].split(":")[0]] = {masterGene: split[0].split(":")[0], name: split[0].split(":")[0], linked: [], expression: 0, mutated: 0, altsplice: 0, copyNumber: 0, family: split[0].split(":")[1].split("(")[1].split(")")[0].split("|")}
                    for (var fam in split[0].split(":")[1].split("(")[1].split(")")[0].split("|")) {
                        var nd = split[0].split(":")[1].split("(")[1].split(")")[0].split("|")[fam]
                        nodes[nd] = {masterGene: nd, name: nd, linked: [], expression: 0, mutated: 0, altsplice: 0, copyNumber: 0, familyMember: true}
                    }
                    split[0] = split[0].split(":")[0]
                } else {
                    nodes[split[0]] = {masterGene: split[0], name: split[0], linked: [], expression: 0, mutated: 0, altsplice: 0, copyNumber: 0}
                }
                nodes[split[0]][split[split.length - 1]] = 1 //declare if it's a gene or an endpoint (genes will have endpoint===undefined)
                for (var j = 1; j < split.length - 1; j++) { //add all children into its linked array
                    if (split[j] !== "")
                        nodes[split[0]].linked.push(split[j])
                }
            }
        }
    }
    unSplit_nodes = jQuery.extend(true, {}, nodes); //create a copy of the nodes before they are manipulated
    initNodes(nodes)
}
/**
 * 
 * @param {type} pat_lines lines from the patient file (or database)
 * @param {type} start where the reading should should start (files have a number of patients on the 2nd line)
 * @returns {undefined}
 */
var x;
function parsePatients(pat_lines, start, reload) {
    if (start === undefined)
        start = 2;
    patients = []
    x = lines = pat_lines.split("\n");
    for (var i = start; i < lines.length; i++) {
        lines[i] = lines[i].replace(/(\r\n|\n|\r)/gm, ""); //remove all extraneous characters
        split = lines[i].split(",") // split the line into its components
        if (split[1] !== undefined) { // if there isn't a blank last line
            if (patients[split[1]] === undefined) {
                patients[split[1]] = {} //initialize the patient object if it does not already exist
            }
            patients[split[1]][split[0]] = {expression: 0, mutated: 0, altsplice: 0, copyNumber: 0} //initialize the patient information for gene 'n'
            patients[split[1]][split[0]].expression = Math.round(split[2] * 10) / 10;
            patients[split[1]][split[0]].snp = split[3]
            patients[split[1]][split[0]].snpdmg = split[4]
            patients[split[1]][split[0]].indel = split[5]
            patients[split[1]][split[0]].altsplice = split[6]
            patients[split[1]][split[0]].copyNumber = split[7]
        }
    }
    mutations = []
    expressions = []
    altsplices = []
    cnvs = []
    nodes = jQuery.extend(true, {}, unSplit_nodes); //make a new copy of nodes for parsing from the unsplit nodes
    for (var node in nodes) {
        for (var patient in patients) {
            /**
             * initalize the rnaSEQ information for later storage
             * and display
             */
            if (expressions[node] === undefined)
                expressions[node] = []
            if (mutations[node] === undefined)
                mutations[node] = []
            if (altsplices[node] === undefined)
                altsplices[node] = []
            if (cnvs[node] === undefined)
                cnvs[node] = []
            if (patients[patient][node] !== undefined) {
                if (!fromDb) { //if the mutated value depends on the display settings
                    var numberAdded = 0
                    if ($("#newsnp_toggle").is(":checked")) {
                        nodes[node].mutated += parseInt(patients[patient][node].snp);
                        numberAdded++;
                    }
                    if ($("#newindel_toggle").is(":checked")) {
                        nodes[node].mutated += parseInt(patients[patient][node].indel);
                        numberAdded++;
                    }
                    if ($("#knownsnp_toggle").is(":checked")) {
                        nodes[node].mutated += parseInt(patients[patient][node].snpdmg);
                        numberAdded++;
                    }
                    nodes[node].mutated = nodes[node].mutated / numberAdded;
                } else {
                    nodes[node].mutated += (parseInt(patients[patient][node].snp) || parseInt(patients[patient][node].indel) || parseInt(patients[patient][node].snpdmg)); //mutated value gets the logical or of snps and indels                    
                }
                nodes[node].altsplice += parseInt(patients[patient][node].altsplice);
                nodes[node].copyNumber += parseInt(patients[patient][node].copyNumber);
                expressions[node].push({patient: patient, expression: patients[patient][node].expression})
                mutations[node].push({patient: patient, snp: patients[patient][node].snp, snpdmg: patients[patient][node].snpdmg, indel: patients[patient][node].indel})
                altsplices[node].push({patient: patient, splice: patients[patient][node].altsplice})
                cnvs[node].push({patient: patient, cnv: patients[patient][node].copyNumber})
            } else { //there is no gene information for that patient (probably not a real gene)
                nodes[node].mutated += 0;
                nodes[node].altsplice += 0;
                nodes[node].copyNumber += 0;
                expressions[node].push({patient: patient, expression: 0})
            }
        }
    }
    if (start === undefined) //file
        var numPatients = parseInt(lines[1])
    else //db
        var numPatients = Object.keys(patients).length
    /**
     * calculate percentages for
     * non RPKM atributes
     */
    for (var node in nodes) {
        nodes[node].mutated = 100 * nodes[node].mutated / numPatients
        nodes[node].altsplice = 100 * nodes[node].altsplice / numPatients
        nodes[node].copyNumber = nodes[node].copyNumber / numPatients
    }
    /**
     * Calculate expression attributes
     * mean, stdev, and outliers
     */
    for (node in expressions) {
        var expressionArray = expressions[node]
        var dispExpr = median(expressionArray);
        var meanVal = mean(expressionArray);
        var stdevValue = stdev(expressionArray, meanVal)
        var mean_median_limit = settings[11] * dispExpr
        // use initial mean to remove outliers and recalculate mean
        var filtered_sum = 0;
        var outCounter = 0;
        for (var expr in expressionArray) {
            if (expressionArray[expr].expression < mean_median_limit) {
                filtered_sum += expressionArray[expr].expression
                outCounter += 1;
            }
        }
        var new_meanVal = filtered_sum / outCounter
        var upperBound = new_meanVal + 0.8 * new_meanVal
        var lowerBound = new_meanVal - 0.8 * new_meanVal
        outCounter = 0;
        for (var expr in expressionArray) {
            if ((expressionArray[expr].expression > upperBound) || (expressionArray[expr].expression < lowerBound)) {//if the expression is outside +/- bounds
                if (expressionArray[expr].expression < mean_median_limit) {
                    expressionArray[expr].outlier = true
                    outCounter += 1;
                }
            }
        }
        outlierCutoff = 0.35
        if (outCounter / expressionArray.length >= outlierCutoff) {
            nodes[node].exprOutlier = true
        }
        nodes[node].expression = parseFloat(parseFloat(dispExpr).toFixed(1))
    }
    initNodes(nodes, reload)
    SCROLLING = false;
    $("#dialog-modal").html("").dialog("close")//close the loading dialog
}
/**
 * Read a file
 * @param {type} file File object
 * @param {type} callback Callback function
 * @returns {undefined}
 */
function readFiles(file, callback) {
    var reader = new FileReader();
    reader.onload = callback;
    reader.readAsText(file);
}
/**
 * Load disease-spec targets from a file
 * @param {type} evt Filereader object
 */
function customTargets(evt) {
    $("#show-cust-mut").show()
    var file1;
    readFiles(evt.target.files[0], function (e) {
        file1 = (e.target.result);
        parseCustomMut(file1); //parse the lines
    });
}
/**
 * Parse the mutation targets
 * @param {type} file Lines from the custom targets
 */
function parseCustomMut(file) {
    var lines = file.split("\n");
    customMuts = []
    for (var line in lines) {
        customMuts.push(lines[line].replace(/(\r\n|\n|\r)/gm, ""))
    }
    update_colors(); //update the colors (hide the coloring on the non targeted muts)
}
/**
 * 
 * @param {type} id Disease Id
 * @param {type} view Whether the Mutations should be used or viewed
 * @returns {undefined}
 */
function loadCustomMutsFromDb(id, view) {
    $.get('getTarget_list.php?id=' + id, function (data) {//load genes based on disease id
        if (view === undefined) {
            parseCustomMut(data) //if the genes are to be used, parse them
        } else { //otherwise generate the datatable for them
            var string = "<table id=genes-table><thead><tr><td>Gene</td></tr></thead><tbody>"
            var genes = data.split("\n");
            genes = genes.splice(0, genes.length - 1)
            for (var gene in genes) {
                string += "<tr><td>" + genes[gene] + "</td></tr>"
            }
            string += "</tbody></table>"
            $("#dialog-modal").html(string)
            $("#genes-table").dataTable({
                "bJQueryUI": true,
                "aLengthMenu": [5, 10, 50],
                "fnInitComplete": function () {
                    $("#genes-table").css("width", '')
                    $("#dialog-modal").dialog({
                        open: function () {
                            controls = null
                        },
                        close: function () {
                            controls = new THREE.OrbitControls(camera);
                        },
                        dialogClass: "",
                        modal: true
                    });
                }
            })
        }
    })
}
/**
 * 
 * TODO: merge with loadCustomMutsFromDb
 */
function view_targets() {
    var string = "<table id=genes-table><thead><tr><td>Gene</td></tr></thead><tbody>"
    for (var gene in customMuts) {
        string += "<tr><td>" + customMuts[gene] + "</td></tr>"
    }
    string += "</tbody></table>"
    $("#dialog-modal").html(string)
    $("#genes-table").dataTable({
        "bJQueryUI": true,
        "aLengthMenu": [5, 10, 50],
        "fnInitComplete": function () {
            $("#genes-table").css("width", '')
            $("#dialog-modal").dialog({
                open: function () {
                    controls = null
                },
                close: function () {
                    controls = new THREE.OrbitControls(camera);
                },
                dialogClass: "",
                modal: true
            });
        }
    })

}
/**
 * Remove the mutation targets
 * return coloring to normal
 */
function clear_targets() {
    customMuts = []
    update_colors();
    $(".dataTable_selected").removeClass("dataTable_selected");
}
/**
 * loads the example pathway and patients
 * parsed like they have been loaded by hand
 */
//function load_example() {
//    fromDb = false;
//    var file1;
//    var file2;
//    $.get("js/test_1.csv", function(data) { // get pathway
//        file1 = data;
//        parsePathway(file1)
//        $.get("js/test2_2.csv", function(data2) { //get patients
//            file2 = data2;
//            parsePatients(file2)
//        });
//    })
//}