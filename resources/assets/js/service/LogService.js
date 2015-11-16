(function() {
    angular
        .module('dt')
        .service('LogService', service);

    function service(api) {
        return {
            containerLogs: function(containerId) {
                return api.container.log({id: containerId}).$promise;
            },
            releaseLogs: function(repoId, releaseId) {
                return api.release.log({repo: repoId, release: releaseId}).$promise;
            }
        }
    }
})();
