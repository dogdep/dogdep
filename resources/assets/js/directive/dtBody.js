(function() {
    angular
        .module('dt')
        .directive('dtBody', dir);

    function dir() {
        return {
            restrict: 'AE',
            link: function($rootScope, element) {
                $rootScope.$watch('bodyClass', function(value) {
                    element.attr('class', value);
                });
            }
        };
    }
})();
