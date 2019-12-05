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
Route::group(['middleware' => 'api'], function () {
    Route::post('auth/login', 'AuthController@login');
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('auth/user', 'AuthController@user');
        Route::post('auth/user', 'AuthController@update');
        Route::post('auth/logout', 'AuthController@logout');
    });
    Route::apiResource('users', 'UserController')->middleware('permission:system.user');
    Route::get('users/{user}/permissions', 'UserController@permissions')->middleware('permission:system.user');
    Route::put('users/{user}/permissions', 'UserController@updatePermissions')->middleware('permission:system.user.permission');
//    Route::apiResource('roles', 'RoleController')->middleware('permission:system.permission');
    Route::get('roles/{role}/permissions', 'RoleController@permissions')->middleware('system.role.permission');
    Route::apiResource('permissions', 'PermissionController')->middleware('permission:system.permission');

    Route::group(['namespace' => 'Api'], function () {
        Route::group(['prefix' => 'channel'], function () {
            Route::get('', 'ChannelController@list')->name('channel.list')->middleware('permission:basic.channel');
            Route::post('{id?}', 'ChannelController@save')->name('channel.save')->middleware('permission:basic.channel.edit');
        });
    });

});
