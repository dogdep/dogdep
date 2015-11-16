(function() {
    angular
        .module('dt')
        .controller("ReleaseConfigCtrl", ctrl);

    function ctrl($scope, config) {
        $scope.config = config;
    }
})();
