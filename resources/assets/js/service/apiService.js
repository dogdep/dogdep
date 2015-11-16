(function() {
    angular
        .module('dt')
        .service('api', service);

    var plainResponse = function(data){
        return {data: data}
    };

    function service($resource) {
        return {
            repo: $resource("/api/repo/:id", {id: '@id'}, {
                deploy: { method: "POST", url: "/api/repo/:id/release/:commit", params: {id: '@id', commit: '@commit'}},
                pull: { method: "GET", url: "/api/repo/:id/pull", params: {id: '@id'}}
            }),
            config: $resource("/api/config"),
            commits: $resource("/api/repo/:repo_id/commits", {repo_id: '@repo_id'}),
            checks: $resource("/api/check/:id", {id: '@id'}),
            keys: $resource("/api/keys/:id", {id: '@id'}),
            volumes: $resource("/api/volume/:id", {id: '@id'}),
            commands: $resource("/api/command/:id", {id: '@id'}),
            release: $resource("/api/repo/:repo_id/release/:id", {repo_id: '@repo_id', release: '@id'}, {
                log: { method: "GET", url: "/api/repo/:repo/release/:release/log", params: {repo: '@repo.id', release: '@id'} },
                config: { method: "GET", url: "/api/repo/:repo/release/:release/config", params: {repo: '@repo.id', release: '@id'}, transformResponse:plainResponse},
                start: { method: "POST", url: "/api/repo/:repo/release/:release/start", params: {repo: '@repo.id', release: '@id'} },
                stop: { method: "POST", url: "/api/repo/:repo/release/:release/stop", params: {repo: '@repo.id', release: '@id'}},
                destroy: { method: "POST", url: "/api/repo/:repo/release/:release/destroy", params: {repo: '@repo.id', release: '@id'} },
                run: { method: "POST", url: "/api/repo/:repo/release/:release/run/:command", params: {repo: '@repo.id', release: '@id'} },
                runChecks: { method: "POST", url: "/api/repo/:repo/release/:release/check", params: {repo: '@repo.id', release: '@id'} }
            }),
            container: $resource("/api/container/:id", {id: '@id'}, {
                log: {method: "GET", url: '/api/container/:id/log', params: {id: '@id'}},
                top: {method: "GET", url: '/api/container/:id/top', params: {id: '@id'}, isArray: true},
                inspect: {method: "GET", url: '/api/container/:id/inspect', params: {id: '@id'}},
                stop: {method: "GET", url: "/api/container/:id/stop", params: {id: '@id'}},
                restart: {method: "GET", url: "/api/container/:id/restart", params: {id: '@id'}},
                remove: {method: "GET", url: "/api/container/:id/remove", params: {id: '@id'}}
            })
        }
    }
})();
