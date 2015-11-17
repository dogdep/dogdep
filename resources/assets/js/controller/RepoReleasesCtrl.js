(function () {
    angular
        .module('dt')
        .controller("RepoReleasesCtrl", ctrl);

    function ctrl($scope, toaster, api, project, releases, Pusher) {
        $scope.releases = releases;
        project.log = [];

        function refreshReleases(dat) {
            var release = getRelease(dat.id);

            if (!release) {
                return $scope.refresh();
            }

            if (dat.status) {
                release.status = dat.status;

                if (dat.status == 'DESTROYED' || dat.status == 'STARTED') {
                    $scope.refresh();
                }
            } else if (dat.message) {
                release.log.push(dat.message);
            } else {
                $scope.refresh();
            }
        }

        $scope.statusClass = function (status) {
            return {
                "UNKNOWN": "default",
                "INITIATING": "info",
                "STARTING_QUEUED": "default",
                "STARTING": "info",
                "STARTED": "success",
                "STOPPING": "warning",
                "STOPPING_QUEUED": "default",
                "STOPPED": "warning",
                "DESTROYED": "danger",
                "DESTROYING": "danger",
                "DESTROYING_QUEUED": "default",
                "ERROR": "danger"
            }[status];
        };

        $scope.containerStatusClass = function (status) {
            if (status.toLowerCase() == "up") {
                return "info";
            } else if (status.toLowerCase() == "down") {
                return "danger";
            } else {
                return "warning";
            }
        };

        $scope.refresh = function () {
            if ($scope.isRefreshing) {
                return;
            }
            $scope.isRefreshing = true;
            return api.release.query({repo_id: project.id}, function (releases) {
                $scope.isRefreshing = false;
                $scope.releases = releases;
                $scope.$emit('project-releases', {projectId: project.id, releases: releases});
            }).$promise;
        };

        $scope.manager = {
            stopContainer: function (container) {
                api.container.stop({id: container.id}).$promise.then(notifySuccess("Container will be stopped"), handleError);
            },
            restartContainer: function (container) {
                api.container.restart({id: container.id}).$promise.then(notifySuccess("Container will be restarted"), handleError);
            },
            removeContainer: function (container) {
                api.container.remove({id: container.id}).$promise.then(notifySuccess("Container will be removed"), handleError);
            },
            stop: function (release) {
                release.$stop(notifySuccess("Release will be stopped"), handleError);
            },
            start: function (release) {
                release.$start(notifySuccess("Release will be started"), handleError);
            },
            destroy: function (release) {
                release.$destroy(notifySuccess("Release will be destroyed"), handleError);
            },
            runCommand: function (release, command) {
                release.$run({command: command.id}, notifySuccess("Command queued"), handleError);
            },
            runChecks: function (release) {
                release.$runChecks(notifySuccess("Checks queued"), handleError);
            }
        };

        function handleError() {
            toaster.pop("error", "error", "error");
        }

        function notifySuccess(message) {
            return function () {
                toaster.pop("success", "Success", message);
            };
        }

        function getRelease(id) {
            for (var i = 0; i < $scope.releases.length; i++) {
                if ($scope.releases[i].id == id) {
                    return $scope.releases[i];
                }
            }
        }

        Pusher.releases().bind("repo-" + $scope.project.id, refreshReleases);
        $scope.$on('$destroy', function () {
            Pusher.releases().unbind("repo-" + $scope.project.id, refreshReleases);
        })
    }
})();

