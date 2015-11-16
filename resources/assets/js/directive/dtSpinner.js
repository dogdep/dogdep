(function() {
    angular
        .module('dt')
        .directive('dtSpinner', dir);

    function dir() {
        return {
            restrict: 'EA',
            template: '<div class="text-center dt-spinner"><i class="fa fa-2x fa-circle-o-notch fa-spin"></i></div>'
        };
    }
})();
