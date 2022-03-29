<?php
Route::middleware('auth')->group(function () {
    Route::group(['namespace' => 'School', 'middleware' => 'schooladmin'], function() {
        
        Route::get('/school', 'SchoolController@index');
        
        // api
        Route::group(['prefix' => 'api/school'], function() {
            Route::get('/getSchool', 'SchoolController@getSchool');
            Route::put('/updateSchool', 'SchoolController@updateSchool');
        });
    });
});
