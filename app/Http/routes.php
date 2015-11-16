<?php

Route::group(['prefix' => 'internal'], function() {
    Route::controller('auth', 'AuthController');

    Route::group(['prefix' => 'hook'], function() {
        Route::post('gitlab', 'HookController@gitlab');
    });
});


Route::group(['prefix'=>'api', 'middleware' => ['jwt.auth']], function() {
    Route::get('config', 'ConfigController@get');

    Route::group(['prefix'=>'repo'], function() {
        Route::get('/', 'RepoController@repoList');
        Route::post('/', 'RepoController@create');
        Route::get('{repo}', 'RepoController@repo');
        Route::post('{repo}', 'RepoController@update');
        Route::delete('{repo}', 'RepoController@delete');
        Route::get('{repo}/release', 'RepoController@repoReleases');
        Route::get('{repo}/commits', 'RepoController@commits');
        Route::get('{repo}/pull', 'RepoController@pull');

        Route::group(['prefix'=>'{repo}/release'], function() {
            Route::post('{commit}', 'ReleaseController@create');

            Route::get('{release}', 'ReleaseController@release');
            Route::post('{release}/stop', 'ReleaseController@stop');
            Route::post('{release}/start', 'ReleaseController@start');
            Route::post('{release}/destroy', 'ReleaseController@destroy');
            Route::get('{release}/log', 'ReleaseController@log');
            Route::get('{release}/config', 'ReleaseController@config');
            Route::post('{release}/run/{command}', 'ReleaseController@run');
            Route::post('{release}/check', 'ReleaseController@check');
        });
    });

    Route::group(['prefix'=>'volume'], function() {
        Route::get('/', 'VolumeController@index');
        Route::post('/', 'VolumeController@create');
        Route::delete('{volume}', 'VolumeController@delete');
    });

    Route::group(['prefix'=>'command'], function() {
        Route::get('/', 'CommandController@index');
        Route::post('/', 'CommandController@create');
        Route::post('{command}', 'CommandController@update');
        Route::delete('{command}', 'CommandController@delete');
    });

    Route::group(['prefix'=>'check'], function() {
        Route::get('/', 'CheckController@index');
        Route::post('/', 'CheckController@create');
        Route::delete('{check}', 'CheckController@delete');
    });

    Route::group(['prefix'=>'container'], function() {
        Route::get('{container}', 'DockerController@get');
        Route::get('{container}/log', 'DockerController@log');
        Route::get('{container}/inspect', 'DockerController@inspect');
        Route::get('{container}/start', 'DockerController@start');
        Route::get('{container}/stop', 'DockerController@stop');
        Route::get('{container}/restart', 'DockerController@restart');
        Route::get('{container}/remove', 'DockerController@remove');
        Route::get('{container}/top', 'DockerController@top');
    });
    Route::group(['prefix'=>'keys'], function() {
        Route::get('', 'KeysController@index');
        Route::post('{host}', 'KeysController@create');
        Route::delete('{key}', 'KeysController@delete');
    });
});
