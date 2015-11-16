(function () {
    angular
        .module('dt')
        .controller("RepoCommitsCtrl", ctrl);

    function ctrl($scope, project, $state, commits, toaster, api, Pusher) {
        $scope.commits = commits;
        $scope.project = project;
        $scope.branch = project.branches.indexOf($state.params.branch) > -1 ? $state.params.branch : "all";

        $scope.hookPopover = function () {
            return 'Add GitLab webhook for push commits ' + location.protocol + "//" + location.host + '/internal/hook/gitlab';
        };

        $scope.deploy = function (commit, customName) {
            var name = null;
            if (customName) {
                name = prompt('Release name');
                if (!name) {
                    return;
                }
            }

            $scope.project.$deploy({id: $scope.project.id, commit: commit.hash, release_id: name}).then(function () {
                toaster.pop('success', 'Deployment scheduled.');
                $state.go('user.repo.releases', {id: $state.params.id});
            });
        };

        $scope.refreshCommits = function () {
            api.repo.pull({id: $scope.project.id}, function () {
                toaster.pop('info', 'Repository pull scheduled.');
                Pusher.pulls().bind("repo-" + $scope.project.id, reload);

                function reload() {
                    Pusher.pulls().unbind("repo-" + $scope.project.id, reload);
                    $state.reload();
                }
            });
        };

        $scope.prevPage = function () {
            return parseInt($state.params.page) - 1 || false;
        };

        $scope.nextPage = function () {
            return parseInt($state.params.page) + 1 || 2;
        };
    }
})();
