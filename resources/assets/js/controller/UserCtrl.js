(function() {
    angular
        .module('dt')
        .controller('UserCtrl', ctrl);

    function ctrl($scope, projects, $state) {
        $scope.projects = projects;

        $scope.isActive = function(project) {
            return $state.includes('user.repo') && $state.params.id == project.id;
        };

        $scope.$on('project-releases', function(e, item) {
            $scope.projects[$scope.projects.map(function(x) { return x.id; }).indexOf(item.projectId)].releases = item.releases;
        });
    }
})();
