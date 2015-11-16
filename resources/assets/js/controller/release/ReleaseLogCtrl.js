(function() {
    angular
        .module('dt')
        .controller("ReleaseLogCtrl", ctrl);

    function ctrl($scope, $sce, LogService, release) {
        $scope.refresh = function() {
            return LogService.releaseLogs(release.repo.id, release.id).then(function(newLogs) {
                $scope.log = $sce.trustAsHtml(newLogs.log.replace(/\n/g, '<br/>'));
            });
        };
    }
})();
