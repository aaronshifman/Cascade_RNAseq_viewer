function median(values) {
    values.sort(function(a, b) {
        return a.expression - b.expression;
    });
    var half = Math.floor(values.length / 2);
    if (values.length % 2) //odd
        return values[half].expression;
    else //even
        return (parseInt(values[half - 1].expression) + parseInt(values[half].expression)) / 2.0;
}
function mean(values) {
    var sum = 0
    for (var value in values) {
        sum += parseInt(values[value].expression)
    }
    return sum / values.length
}
function stdev(values, mean) {
    var sum = 0
    for (var value in values) {
        sum += Math.pow((values[value].expression - mean), 2)
    }
    return Math.sqrt(sum / (values.length - 1))
}
function toRad(deg) {
    return (deg / 360) * 2 * Math.PI;
}
function quantile(sample, p) {
    sample.sort(function(a, b) {
        return a - b;
    });
    var idx = (sample.length) * p;
    if (p < 0 || p > 1) {
        return null;
    } else if (p === 1) {
        return sample[sample.length - 1];
    } else if (p === 0) {
        return sample[0];
    } else if (idx % 1 !== 0) {
        return sample[Math.ceil(idx) - 1];
    } else if (sample.length % 2 === 0) {
        return (sample[idx - 1] + sample[idx]) / 2;
    } else {
        return sample[idx];
    }
}
function simpleMedian(values) {
    values.sort(function(a, b) {
        return a - b;
    });
    var half = Math.floor(values.length / 2);
    if (values.length % 2) //odd
        return values[half];
    else //even
        return (parseInt(values[half - 1]) + parseInt(values[half])) / 2.0;
}
function log(array) {
    var logs = [];
    for (i in array) {
        if (typeof array[i] === "object") {
            x = jQuery.extend(true, {}, array[i])
            x.y = Math.log(array[i].y) / Math.LN10
            logs.push(x)
        } else {
            logs.push(Math.log(array[i]) / Math.LN10);
        }
    }
    return logs;
}