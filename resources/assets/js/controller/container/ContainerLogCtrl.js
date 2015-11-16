(function() {
    angular
        .module('dt')
        .controller("ContainerLogCtrl", ctrl);

    function ctrl($scope, $sce, LogService, $state) {
        $scope.refresh = function() {
            return LogService.containerLogs($state.params.container).then(function (newLogs) {
                $scope.log = $sce.trustAsHtml(newLogs.log.replace(/\n/g, '<br/>'));
            });
        };
    }
})();
