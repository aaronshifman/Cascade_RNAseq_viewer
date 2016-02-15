var settings;
function updateSettings(settings){
    this.settings = settings;
}

function removeDuplicates(array) {
    for (var i = 1; i < array.length;) {
        (array[i - 1] == array[i]) ? array.splice(i, 1) : i++;
    }
    return array;
}