(function () {
    var app = angular.module('cascade', ['ui.grid', 'ui.grid.selection']);

    app.service('viewSettingsService', function () {
        var settings = {
            backgroundColor: "#222222",
            familyNodeColor: "#654321",
            nodeColorLowFrequency: "#FFFFFF",
            nodeColorMedFrequency: "#F778A1",
            nodeColorHighFrequency: "#FF0000",
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
    app.controller('pathwayListController', ['$scope', '$http', 'uiGridConstants','viewSettingsService', function ($scope, $http, uiGridConstants,viewSettingsService) {
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
                    $http.get("load_pathway.php?path=" + row.entity[0]).then(function (response) {
                        var pathway = parsePathway(response.data);
                        pathway.genes = calculatePathwayPosition(pathway.genes, pathway.structure);
                        var setup = initScene();
                        setup.scene = drawScene(setup.scene,pathway.genes,pathway.structure,settings);
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
})();