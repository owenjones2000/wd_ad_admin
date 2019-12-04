<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => 'api'], function () {
    Route::post('auth/login', 'AuthController@login');
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('auth/user', 'AuthController@user');
        Route::post('auth/logout', 'AuthController@logout');
    });
    Route::apiResource('users', 'UserController')->middleware('permission:system.permission');
    Route::get('users/{user}/permissions', 'UserController@permissions')->middleware('permission:system.permission');
    Route::put('users/{user}/permissions', 'UserController@updatePermissions')->middleware('permission:system.permission');
    Route::apiResource('roles', 'RoleController')->middleware('permission:system.permission');
    Route::get('roles/{role}/permissions', 'RoleController@permissions')->middleware('permission:system.permission');
    Route::apiResource('permissions', 'PermissionController')->middleware('permission:system.permission');

    Route::group(['namespace' => 'Api'], function () {
        Route::group(['prefix' => 'channel'], function () {
            Route::get('', 'ChannelController@list')->name('channel.list');//->middleware('permission:basic.channel');
            Route::post('{id?}', 'ChannelController@save')->name('channel.save');//->middleware('permission:basic.channel');
        });
    });

});
