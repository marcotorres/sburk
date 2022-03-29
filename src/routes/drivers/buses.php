<?php
Route::middleware('auth')->group(function () {
    Route::group(['namespace' => 'Drivers', 'middleware' => 'schooladmin'], function() {
        // views
        Route::group(['prefix' => 'buses'], function() {
                Route::view('/', 'buses.index');
                // Route::view('/create', 'buses.create');
                // Route::view('/{bus}/edit', 'buses.edit');
        });

        // api
        Route::group(['prefix' => 'api/buses'], function() {
            Route::get('/all', 'BusController@all');
            Route::post('/store', 'BusController@store');

            Route::post('/filter', 'BusController@filter');

            Route::delete('/{bus}', 'BusController@destroy');
            
        });
    });
});

