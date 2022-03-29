<?php
Route::middleware('auth')->group(function () {
    Route::group(['namespace' => 'Parents', 'middleware' => 'schooladmin'], function() {
        // views
        Route::group(['prefix' => 'parents'], function() {
            Route::view('/', 'parents.index');
            Route::view('/create', 'parents.create');
            Route::view('/{parent}/edit', 'parents.edit');
            Route::get('/{parent}/map', 'ParentController@showmap');
        });

        // api
        Route::group(['prefix' => 'api/parents'], function() {
            Route::get('/all', 'ParentController@all');

            Route::post('/assign', 'ParentController@assignDrivers');

            Route::post('/upload', 'ParentController@upload');

            Route::get('/getParent/{parent}', function (App\Parent_ $parent) {
                return App::call('App\Http\Controllers\Parents\ParentController@getParent', ['parent' => $parent]);
            })->middleware('can:view,parent');

            Route::post('/filter', 'ParentController@filter');

            Route::get('/{parent}', function (App\Parent_ $parent) {
                return App::call('App\Http\Controllers\Parents\ParentController@show', ['parent' => $parent]);
            })->middleware('can:view,parent');

            Route::post('/store', 'ParentController@store');

            Route::get('/child/{parent}', 'ParentController@getChildren');

            Route::put('/update/{parent}', 'ParentController@update');

            Route::delete('/deleteMany', 'ParentController@deleteMany');

            Route::delete('/{parent}', function (App\Parent_ $parent) {
                App::call('App\Http\Controllers\Parents\ParentController@destroy', ['parent' => $parent]);
            })->middleware('can:delete,parent');
        });
    });
});

