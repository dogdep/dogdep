(function() {
    angular
        .module('dt')
        .controller("RepoCommandsCtrl", ctrl);

    function ctrl($scope, project, api, toaster) {

        $scope.commands = project.commands;
        $scope.command = newCommand();
        $scope.sortableOptions = {
            orderChanged: function() {
                for (var i = 0; i< $scope.commands.length; i++) {
                    if ($scope.commands[i].order != i) {
                        $scope.commands[i].order = i;
                        $scope.commands[i].$save();
                    }
                }
            }
        };

        $scope.saveCommand = function() {
            $scope.command.$save(function(cmd) {
                $scope.commands.push(cmd);
                $scope.command = newCommand();
                toaster.pop("success", "Command saved");
            });
        };

        $scope.remove = function(command) {
            api.commands.delete(command, function() {
                $scope.commands.splice($scope.commands.map(function(x) { return x.id }).indexOf(command.id), 1);
                toaster.pop("success", "Command deleted");
            });
        };

        function newCommand() {
            return new api.commands({
                repo_id: project.id,
                type: 'post-release',
                order: $scope.commands.length
            });
        }
    }
})();

