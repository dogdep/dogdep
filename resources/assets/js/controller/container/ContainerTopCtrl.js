(function() {
    angular
        .module('dt')
        .controller('ContainerTopCtrl', ContainerTopCtrl);

    function ContainerTopCtrl($scope, api, $state) {
        $scope.refresh = function() {
            return api.container.top({id: $state.params.container}).$promise.then(function(result) {
                $scope.top = result;
            });
        };
    }
})();
