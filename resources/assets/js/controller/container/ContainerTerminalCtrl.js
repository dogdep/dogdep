(function() {
    angular
        .module('dt')
        .controller("ContainerTerminalCtrl", ctrl);

    function ctrl($scope, container) {
        $scope.container = container;
    }
})();
