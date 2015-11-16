(function() {
    angular
        .module('dt')
        .controller("RepoChecksCtrl", ctrl);

    function ctrl($scope, project, api, toaster) {
        $scope.checks = project.checks;
        $scope.check = newCheck();

        $scope.remove = function(check) {
            api.checks.delete({id: check.id}, function() {
                $scope.checks.splice($scope.checks.map(function(x) { return x.id }).indexOf(check.id), 1);
                toaster.pop("success", "Check deleted");
            });
        };

        $scope.save = function() {
            $scope.check.$save(function(cmd) {
                $scope.checks.push(cmd);
                $scope.check = newCheck();
                toaster.pop("success", "Command saved");
            });
        };

        function newCheck() {
            return new api.checks({repo_id: project.id});
        }
    }
})();

