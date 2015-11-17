angular.module('dt').filter('level', function() {
    return function(input) {
        var level = input.match(/\[(\w+)]/)[1].toLowerCase();
        return level && level != "" ? level : "info";
    };
});
