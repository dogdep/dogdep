(function() {
    angular
        .module('dt')
        .config(config);

    function config($locationProvider, $stateProvider, $urlRouterProvider) {

        $stateProvider
            .state('anon', {
                abstract: true,
                template: "<ui-view/>"
            })
            .state('anon.check', {
                url: '/',
                controller: function($state, AuthFactory) {
                    if (AuthFactory.isLoggedIn()) {
                        $state.go('user.index');
                    } else {
                        $state.go('anon.login');
                    }
                }
            })
            .state('anon.login', {
                url: '/login?error',
                templateUrl: '/templates/auth/login.html',
                controller: function($rootScope, $scope, providers, $stateParams, toaster) {
                    $rootScope.bodyClass = 'login';
                    $scope.providers = providers;
                    if ($stateParams.error) {
                        toaster.pop("error", $stateParams.error);
                    }
                },
                resolve: {
                    providers: function($http) {
                        return $http({url: '/internal/auth/providers', skipAuthorization: true, method: 'GET'})
                            .then(function(res) {
                                return res.data;
                            });
                    }
                }
            })
            .state('anon.login_handle', {
                url: '/login/handle/:token',
                controller: function(AuthFactory, $state, $stateParams) {
                    AuthFactory.login($stateParams.token);
                    $state.go('user.index');
                }
            })
            .state('user', {
                abstract: true,
                controller: "UserCtrl",
                templateUrl: "/templates/layout.html",
                resolve: {
                    projects: function(api) {
                        return api.repo.query().$promise;
                    }
                }
            })
            .state('user.index', {
                url: "/repo",
                controller: "RepoIndexCtrl",
                templateUrl: "/templates/repo/add.html",
                resolve: {
                    config: function(api) {
                        return api.config.get().$promise;
                    }
                }
            })
            .state('user.repo', {
                url: "/view/:id",
                templateUrl: "/templates/repo/view.html",
                controller: "RepoDetailsCtrl",
                resolve: {
                    project: function($stateParams, api) {
                        return api.repo.get({id: $stateParams.id}).$promise;
                    }
                }
            })
            .state('user.repo.commits', {
                url: "/commits/:page/:branch",
                templateUrl: "/templates/repo/commits.html",
                controller: "RepoCommitsCtrl",
                params: {
                    page: "1",
                    branch: "0"
                },
                resolve:  {
                    commits: function($stateParams, api, project) {
                        return api.commits.query({repo_id: project.id, page: $stateParams.page, branch: $stateParams.branch}).$promise;
                    }
                }
            })
            .state('user.repo.commands', {
                url: "/commands",
                templateUrl: "/templates/repo/commands.html",
                controller: "RepoCommandsCtrl"
            })
            .state('user.repo.checks', {
                url: "/checks",
                templateUrl: "/templates/repo/checks.html",
                controller: "RepoChecksCtrl"
            })
            .state('user.repo.volumes', {
                url: "/volumes",
                templateUrl: "/templates/repo/volumes.html",
                controller: "RepoVolumesCtrl"
            })
            .state('user.repo.settings', {
                url: "/settings",
                templateUrl: "/templates/repo/settings.html",
                controller: "RepoSettingsCtrl"
            })
            .state('user.repo.releases', {
                url: "/releases",
                templateUrl: "/templates/repo/releases.html",
                controller: "RepoReleasesCtrl",
                resolve: {
                    releases: function (project, api) {
                        return api.release.query({repo_id: project.id}).$promise;
                    }
                }
            })
            .state('user.repo.releases.details', {
                url: "/:release",
                abstract: true,
                parent: 'user.repo.releases',
                onEnter: function($modal, $state, release, project) {
                    $modal.open({
                        size: 'xl',
                        templateUrl: "/templates/repo/release/modal.html",
                        controller: function ($state, $scope) {
                            $scope.$state = $state;
                            $scope.release = release;
                            $scope.project = project;
                        }
                    }).result.finally(function() {
                        $state.go('user.repo.releases');
                    });
                },
                resolve: {
                    release: function ($stateParams, project, api) {
                        return api.release.get({repo_id: project.id, id: $stateParams.release}).$promise;
                    }
                }
            })
            .state('user.repo.releases.details.log', {
                url: "/log",
                views: {
                    'modal@': {
                        templateUrl: '/templates/repo/release/log.html',
                        controller: 'ReleaseLogCtrl'
                    }
                }
            })
            .state('user.repo.releases.details.config', {
                url: "/config",
                views: {
                    'modal@': {
                        templateUrl: '/templates/repo/release/config.html',
                        controller: 'ReleaseConfigCtrl',
                        resolve: {
                            config: function(api, project, release) {
                                return api.release.config({repo: project.id, release: release.id}).$promise;
                            }
                        }
                    }
                }
            })
            .state('user.repo.commits.deploy', {
                url: "/deploy/:commit",
                parent: 'user.repo.commits',
                onEnter: function($modal, $state, commit) {
                    $modal.open({
                        size: 'xl',
                        templateUrl: "/templates/repo/commits/modal.html",
                        controller: 'CommitDeployCtrl',
                        resolve: {
                            commit: function ($stateParams, commits) {
                                for(var i=0; i<commits.length; i++) {
                                    if (commits[i].hash == $stateParams.commit) {
                                        return commits[i];
                                    }
                                }
                            }
                        }
                    }).result.finally(function() {
                        $state.go('^');
                    });
                }
            })
            .state('user.repo.releases.container', {
                url: "/container/:container",
                abstract: true,
                parent: 'user.repo.releases',
                onEnter: function($modal, $state, container) {
                    $modal.open({
                        size: 'xl',
                        templateUrl: "/templates/repo/container/modal.html",
                        controller: function ($state, $scope) {
                            $scope.$state = $state;
                            $scope.container = container;
                        }
                    }).result.finally(function() {
                        $state.go('user.repo.releases');
                    });
                },
                resolve: {
                    container: function ($stateParams, api) {
                        return api.container.get({id: $stateParams.container}).$promise;
                    }
                }
            })
            .state('user.repo.releases.container.inspect', {
                url: "/inspect",
                views: {
                    'modal@': {
                        templateUrl: '/templates/repo/container/inspect.html',
                        controller: 'ContainerInspectCtrl'
                    }
                }
            })
            .state('user.repo.releases.container.log', {
                url: "/log",
                views: {
                    'modal@': {
                        templateUrl: '/templates/repo/container/log.html',
                        controller: 'ContainerLogCtrl'
                    }
                }
            })
            .state('user.repo.releases.container.top', {
                url: "/top",
                views: {
                    'modal@': {
                        templateUrl: '/templates/repo/container/top.html',
                        controller: 'ContainerTopCtrl'
                    }
                }
            })
            .state('user.repo.releases.container.terminal', {
                url: "/terminal",
                views: {
                    'modal@': {
                        templateUrl: '/templates/repo/container/terminal.html',
                        controller: 'ContainerTerminalCtrl'
                    }
                }
            })
        ;

        $locationProvider.html5Mode(true);
        $urlRouterProvider.otherwise("/");

    }
})();

