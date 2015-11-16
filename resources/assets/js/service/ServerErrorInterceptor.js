(function() {
    angular
        .module('dt')
        .factory('ServerErrorInterceptor', interceptor);

    function interceptor($q, toaster) {
        return {
            response: response,
            responseError: error
        };

        function response(response) {
            return response || $q.when(response);
        }

        function error(rejection) {

            if (rejection.status >= 500 || [401, 402, 405].indexOf(rejection.status) != -1) {
                toaster.pop('error', "Server error", rejection.data.message);
            }

            return $q.reject(rejection);
        }
    }

})();
