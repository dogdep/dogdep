(function() {
    angular
        .module('dt')
        .directive('dtLogout', dir);

    function dir() {
        return {
            restrict: 'A',
            template:
                '<ul class="nav navbar-nav navbar-right">' +
                    '<li ng-if="user.image"><img ng-src="{{ user.image }}" class="img-rounded user-image"/></li>' +
                    '<li><a href="#" ng-click="logout()">Logout ({{ user.name }})</a></li>' +
                '</ul>',
            controller: function($scope, $state, AuthFactory) {
                $scope.user = AuthFactory.getUser();
                $scope.logout = function() {
                    AuthFactory.logout();
                    $state.go('anon.login');
                }
            }
        };
    }
})();
