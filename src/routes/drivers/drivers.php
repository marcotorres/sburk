<?php
Route::middleware('auth')->group(function () {
    Route::group(['namespace' => 'Drivers', 'middleware' => 'schooladmin'], function() {
        // views
        Route::group(['prefix' => 'drivers'], function() {
            Route::view('/', 'drivers.index');
            Route::view('/create', 'drivers.create');
            Route::view('/{driver}/edit', 'drivers.edit');
            Route::get('/{driver}/map', 'DriverController@showmap');
            Route::get('/map', 'DriverController@showAllMap');
            Route::view('/{driver}/history', 'drivers.history');
        });

        // api
        Route::group(['prefix' => 'api/drivers'], function() {
            Route::get('/all', 'DriverController@all');
            
            Route::get('/getDriver/{driver}', function (App\Driver $driver) {
                return App::call('App\Http\Controllers\Drivers\DriverController@getDriver', ['driver' => $driver]);
            })->middleware('can:view,driver');

            Route::get('/getLog/{driver}', function (App\Driver $driver) {
                return App::call('App\Http\Controllers\Drivers\DriverController@getLog', ['driver' => $driver]);
            })->middleware('can:view,driver');

            Route::post('/filter', 'DriverController@filter');
            Route::post('/sendMessage', 'DriverController@sendMessage');

            Route::get('/{driver}', function (App\Driver $driver) {
                return App::call('App\Http\Controllers\Drivers\DriverController@show', ['driver' => $driver]);
            })->middleware('can:view,driver');

            Route::post('/store', 'DriverController@store')->middleware('can:create,App\Driver');
            
            Route::put('/update/{driver}', 'DriverController@update');

            Route::delete('/{driver}', function (App\Driver $driver) {
                App::call('App\Http\Controllers\Drivers\DriverController@destroy', ['driver' => $driver]);
            })->middleware('can:delete,driver');
        });
    });
});



