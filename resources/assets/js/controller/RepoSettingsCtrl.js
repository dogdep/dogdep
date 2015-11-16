(function() {
    angular
        .module('dt')
        .controller("RepoSettingsCtrl", ctrl);

    function ctrl($scope, project, api, toaster, projects, $state) {
        $scope.project = project;

        $scope.removeEnv = function(k) {
            delete project.env[k];
        };

        $scope.addEnv = function() {
            var name = prompt("Enter variable name");
            if (name) {
                project.env[name.toUpperCase()] = "";
            }
        };

        $scope.save = function() {
            $scope.project.$save(function() {
                toaster.pop("success", "Project updated");
            });
        };

        $scope.deleteProject = function() {
            if (!confirm('Are you sure?')) {
                return;
            }

            api.repo.delete(project, function() {
                projects.splice(projects.map(function(x) { return x.id }).indexOf($scope.project.id), 1);
                toaster.pop('success', "Project deleted.");
                $state.go("user.index");
            });
        };
    }
})();

