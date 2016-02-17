(function () {
    var app = angular.module('cascade', ['ui.grid', 'ui.grid.selection']);

    app.service('viewSettingsService', function () {
        var settings = {
            backgroundColor: "#222222",
            familyNodeColor: "#654321",
            nodeColorLowFrequency: "#FFFFFF",
            nodeColorMedFrequency: "#F778A1",
            nodeColorHighFrequency: "#FF0000",
            nodeLowFrequencyValue: 0.1,
            nodeHighFrequencyValue: 0.5,
            altLowFrequencyValue: 0.1,
            altHighFrequencyValue: 0.5,
            textSize: 10,
            outlierColor: "#FF0000",
            linkColor: "#FF0000",
            nodeNameColor: "#66FFEE",
            levelRingColor: "#FFFFFF",
            medianThresholdParameter: 15,
            nameColor: "#66FF66",
            ringsOn: true
        };

        var getSettings = function () {
            return settings;
        }
        return {getSettings: getSettings};
    })
    app.service('pathwayStateService',function(){
        var pathwaySelected = null;

        var getPathwayState = function(){
            return pathwaySelected;
        };

        var setPathwayState = function(pathway){
            pathwaySelected = pathway;
        }
        return {getPathwayState:getPathwayState, setPathwayState:setPathwayState};
    });
    app.controller('viewSettingController', function ($scope,viewSettingsService) {
        $scope.settings = viewSettingsService.getSettings();
    });

    app.controller("expressionController", function () {
        this.expression_type = 1; //off, median, ratio
    });

    app.controller("splicingController", function () {
        this.splicing = true;
    });
    app.controller("mutationController", function () {
        this.snp = true;
        this.damaging = false;
        this.indel = true;
    });
    app.controller("cnvController", function () {
        this.cnv = 2; //off, absolute, relative
    });
    app.controller('pathwayListController', ['$scope', '$http', 'uiGridConstants','viewSettingsService','pathwayStateService', function ($scope, $http, uiGridConstants,viewSettingsService,pathwayStateService) {
        var settings = viewSettingsService.getSettings();
        $scope.columns = [{field: 'pathway_id', enableHiding: false, name: 'Id', visible: false}, {
            field: 'pathway_name', enableHiding: false, name: 'Name'
        }, {field: 'type', enableHiding: false, name: 'Type'}];
        $scope.gridOptions = {
            enableSorting: true,
            columnDefs: $scope.columns,
            enableFullRowSelection: true,
            multiSelect: false,
            enableRowHeaderSelection: false,
            enableFiltering: true,
            onRegisterApi: function (gridApi) {
                $scope.gridApi = gridApi;
                gridApi.selection.on.rowSelectionChanged($scope, function (row) {
                    pathwayStateService.setPathwayState(row.entity[1]);
                    $http.get("load_pathway.php?path=" + row.entity[0]).then(function (response) {
                        var pathway = parsePathway(response.data);
                        pathway.genes = calculatePathwayPosition(pathway.genes, pathway.structure);

                        updateSettings(settings);
                        var setup = initScene();
                        setup.scene = drawScene(setup.scene,pathway.genes,pathway.structure);
                        prepareAnimation(setup.scene, setup.camera, setup.renderer); //save scene camera and renderer as globals
                        animate();
                        render();
                    });
                });
            }
        };
        $http.get("getPaths.php").then(function (response) {
            $scope.gridOptions.data = response.data;
        });
    }]);
    app.controller('dataListController', ['$scope', '$http', 'uiGridConstants','pathwayStateService', function ($scope, $http, uiGridConstants,pathwayStateService) {
        $scope.columns = [{field: 'data_id', enableHiding: false, name: 'id', visible: false},
            {field: 'name', enableHiding: false, name: 'name', visible: true}];
        $scope.gridOptions = {
            enableSorting: true,
            columnDefs: $scope.columns,
            enableFullRowSelection: true,
            multiSelect: false,
            enableRowHeaderSelection: false,
            enableFiltering: true,
            data:[
                {data_id:"demo_", name:"Demo Data"},
                {data_id:"tcga_AML_", name:"TCGA AML"},
                {data_id:"Leu_ALL_", name:"Leucegene ALL"},
                {data_id:"tcga_Prostate_", name:"TCGA Prostate Cancer"},
                {data_id:"nature_2014", name:"Zhengyan 2014"},
                {data_id:"LEU_AML", name:"Leucegene"}
            ],
            onRegisterApi: function (gridApi) {
                $scope.gridApi = gridApi;
                gridApi.selection.on.rowSelectionChanged($scope, function (row) {
                    var genes = [];
                    for(var i = 0; i<scene.children.length;i++){
                        if(scene.children[i].type === 'node')
                            genes.push(scene.children[i].masterGene);
                    }
                    var data = $.param({
                        json:JSON.stringify({
                            source:row.entity.data_id,
                            genes:removeDuplicates(genes)
                        })
                    });
                    var config = {
                        headers : {
                            'Content-Type': 'application/x-www-form-urlencoded;charset=utf-8;'
                        }
                    };
                    $http.post('load_data.php',data,config).then(function(response){
                        console.log(response.data);
                        drawData(response.data);
                    });
                });
            }
        };
        $scope.show = function(){
            return pathwayStateService.getPathwayState() !== null;
        }
    }]);
})();