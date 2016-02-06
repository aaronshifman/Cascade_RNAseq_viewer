(function () {
    var app = angular.module('cascade', ['ui.grid', 'ui.grid.selection']);
    var loadedPathways = false;

    app.controller('viewSettingController', function () {
        this.backgroundColor = "#222222";
        this.familyNodeColor = "#654321";
        this.nodeColorLowFrequency = "#FFFFFF";
        this.nodeColorMedFrequency = "#F778A1";
        this.nodeColorHighFrequency = "#FF0000";
        this.textSize = 10;
        this.outlierColor = "#FF0000";
        this.linkColor = "#FF0000";
        this.nodeNameColor = "#66FFEE";
        this.levelRingColor = "#FFFFFF";
        this.medianThresholdParameter = 15;
        this.nameColor = "#66FF66";
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
    app.controller('pathwayListController', ['$scope', '$http', 'uiGridConstants', function ($scope, $http, uiGridConstants) {
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
                        calculatePathwayPosition(pathway.genes, pathway.structure);
                    });
                });
            }
        };
        $http.get("getPaths.php").then(function (response) {
            $scope.gridOptions.data = response.data;
        });
    }]);
})();
