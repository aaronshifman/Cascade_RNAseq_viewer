(function () {
    var app = angular.module('cascade', []);
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
})();
