(function() {
    angular
        .module('dt')
        .controller("RepoReleasesCtrl", ctrl);

    function ctrl($scope, toaster, api, project, releases, Pusher) {
        $scope.releases = releases;

        //$scope.refreshReleases = ;
        function refreshReleases() {
            return api.release.query({repo_id: project.id}, function(releases) {
                $scope.releases = releases;
                $scope.$emit('project-releases', {projectId: project.id, releases: releases});
            }).$promise;
        }

        $scope.manager = {
            stopContainer: function(container) {
                api.container.stop({id: container.id}).$promise.then(notifySuccess("Container will be stopped"), handleError);
            },
            restartContainer: function(container) {
                api.container.restart({id: container.id}).$promise.then(notifySuccess("Container will be restarted"), handleError);
            },
            removeContainer: function(container) {
                api.container.remove({id: container.id}).$promise.then(notifySuccess("Container will be removed"), handleError);
            },
            stop: function(release) {
                release.$stop(notifySuccess("Release will be stopped"), handleError);
            },
            start: function(release) {
                release.$start(notifySuccess("Release will be started"), handleError);
            },
            destroy: function(release) {
                release.$destroy(notifySuccess("Release will be destroyed"), handleError);
            },
            runCommand: function(release, command) {
                release.$run({command: command.id}, notifySuccess("Command queued"), handleError);
            },
            runChecks: function(release) {
                release.$runChecks(notifySuccess("Checks queued"), handleError);
            }
        };

        function handleError() {
            toaster.pop("error", "error", "error");
        }

        function notifySuccess(message) {
            return function() {
                toaster.pop("success", "Success", message);
            };
        }

        Pusher.releases().bind("repo-" + $scope.project.id, refreshReleases);
        $scope.$on('$destroy', function(){
            Pusher.releases().bind("repo-" + $scope.project.id, refreshReleases);
        })
    }
})();

