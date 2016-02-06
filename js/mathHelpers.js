function averageAngle(angles) {
    var x = 0;
    var y = 0;
    for (var i = 0; i < angles.length; i++) {
        x += Math.cos(angles[i]);
        y += Math.sin(angles[i]);
    }
    return Math.atan2(y, x);
}