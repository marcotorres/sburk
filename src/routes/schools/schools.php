<?php
Route::middleware('auth')->group(function () {
    Route::group(['namespace' => 'Schools', 'middleware' => 'superadmin'], function() {
        // views
        Route::group(['prefix' => 'schools'], function() {
            Route::get('/', 'SchoolsController@showIndex');
            Route::view('/create', 'schools.create');
        });

        // api
        Route::group(['prefix' => 'api/schools'], function() {
            Route::post('/filter', 'SchoolsController@filter');
            Route::delete('/{school}', 'SchoolsController@destroy');
            Route::post('/store', 'SchoolsController@store');
        });
    });
});
