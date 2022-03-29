<?php
Route::middleware('auth')->group(function () {
    Route::group(['namespace' => 'Settings', 'middleware' => 'superadmin'], function() {
        
        Route::view('/settings', 'settings.settings');
        
        // api
        Route::group(['prefix' => 'api/settings'], function() {
            Route::get('/getSettings', 'SettingsController@getSettings');
            Route::put('/updateSettings', 'SettingsController@updateSettings');
        });
    });
});
