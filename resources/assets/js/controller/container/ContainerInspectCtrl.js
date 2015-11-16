(function() {
    angular
        .module('dt')
        .controller('ContainerInspectCtrl', ContainerTopCtrl);

    function ContainerTopCtrl($scope, api, $state) {
        api.container.inspect({id: $state.params.container}, function(result){
            $scope.inspect = angular.toJson(result, true);
        });
    }
})();
