(function() {
    angular
        .module('dt')
        .controller("RepoDetailsCtrl", ctrl);

    function ctrl($scope, project, $state) {
        $scope.project = project;
        $scope.$state = $state;
    }
})();
