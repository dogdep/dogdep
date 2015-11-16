(function() {
    angular
        .module('dt')
        .controller("RepoVolumesCtrl", ctrl);

    function ctrl($scope, project, api, toaster) {
        $scope.volumes = project.volumes;
        $scope.volume = newVolume();

        $scope.save = function() {
            $scope.volume.$save(function(cmd) {
                $scope.volumes.push(cmd);
                $scope.volume = newVolume();
                toaster.pop("success", "Volume saved.");
            });
        };

        $scope.remove = function(volume) {
            api.volumes.delete(volume, function() {
                $scope.volumes.splice($scope.volumes.map(function(x) { return x.id }).indexOf(volume.id), 1);
                toaster.pop("success", "Volume deleted");
            });
        };

        function newVolume() {
            return new api.volumes({
                repo_id: project.id
            });
        }
    }
})();

