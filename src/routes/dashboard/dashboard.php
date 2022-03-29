<?php
Route::middleware('auth')->group(function () {

    Route::group(['namespace' => 'Dashboard', 'middleware' => 'schooladmin'], function() {
        // views
        Route::get('/dashboard', 'DashboardController@index')->name('home');
        Route::group(['prefix' => 'api/dashboard'], function() {
            Route::get('/getParentsDriversInfo', 'DashboardController@getParentsDriversInfo');
        });
    });

    Route::group(['namespace' => 'Dashboard', 'middleware' => 'superadmin'], function() {
        // views
        Route::get('/sadmin_dashboard', 'DashboardController@index')->name('sadmin_home');

        // api
        Route::group(['prefix' => 'api/dashboard'], function() {
            Route::post('/loginAsSchoolAdmin', 'DashboardController@loginAsSchoolAdmin');
            Route::get('/getSchoolsInfo', 'DashboardController@getSchoolsInfo');
            Route::get('/getStripeBalance', 'DashboardController@getStripeBalance');
            
        });
    });

});
