(function() {
    angular
        .module('dt')
        .directive('dtHideOnLoad', dir);

    function dir($rootScope) {
        return {
            restrict: 'A',
            link: function(scope, element) {
                $rootScope.$on('$stateChangeSuccess', function() {
                    element.addClass('fade');
                    setTimeout(function() {
                        element.remove();
                    }, 400)
                });
            }
        };
    }
})();
