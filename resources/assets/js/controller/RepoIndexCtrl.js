(function() {
    angular
        .module('dt')
        .controller("RepoIndexCtrl", ctrl);

    function ctrl($scope, projects, api, toaster, $state, config) {
        $scope.projects = projects;
        $scope.project = new api.repo;
        $scope.publicKey = config.public_key;

        $scope.addProject = function() {
            $scope.project.$save(function(project) {
                projects.push(project);
                $scope.project = new api.repo;
                toaster.pop('success', 'Project has been added.');
                $state.go('user.repo.releases', {id: project.id});
            });
        };
    }
})();
