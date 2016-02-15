function averageAngle(angles) {
    var x = 0;
    var y = 0;
    for (var i = 0; i < angles.length; i++) {
        x += Math.cos(angles[i]);
        y += Math.sin(angles[i]);
    }
    return Math.atan2(y, x);
}
function median(values) {
    values.sort( function(a,b) {return a - b;} );
    var half = Math.floor(values.length/2);
    if(values.length % 2)
        return values[half];
    else
        return (values[half-1] + values[half]) / 2.0;
};
