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
        // API
        Route::group(['prefix' => 'auth'], function () {
            Route::get('token', 'AuthController@tokenList')->name('auth.token')->middleware('permission:basic.auth.token');
            Route::post('token', 'AuthController@makeToken')->name('auth.token.make')->middleware('permission:basic.auth.token.make');
            Route::delete('token/{id}', 'AuthController@destroy')->name('auth.token.destroy')->middleware('permission:basic.auth.token.destroy');
        });
        // 渠道
        Route::group(['prefix' => 'channel'], function () {
            Route::get('', 'ChannelController@list')->name('channel.list')->middleware('permission:basic.channel');
            Route::post('{id?}', 'ChannelController@save')->name('channel.save')->middleware('permission:basic.channel.edit');
        });

        // 应用管理
//        Route::group(['prefix'=>'app', 'middleware' => 'permission:app'], function () {
//            Route::get('data', 'AppController@data')->name('app.data');
//            Route::get('list', 'AppController@index')->name('app');
//            //编辑
//            Route::get('{id?}', 'AppController@edit')->name('app.edit')->middleware('permission:app.edit');
//            Route::post('{id?}', 'AppController@save')->name('app.save')->middleware('permission:app.edit');
//            Route::post('{id}/enable', 'AppController@enable')->name('app.enable')->middleware('permission:app.edit');
//            Route::post('{id}/disable', 'AppController@disable')->name('app.disable')->middleware('permission:app.edit');
//
//            //删除
////        Route::delete('destroy', 'AppController@destroy')->name('app.destroy')->middleware('permission:app.destroy');
//        });

        // 活动管理
        Route::group(['prefix'=>'campaign'], function () {
            Route::get('', 'CampaignController@list')->name('advertise.campaign');
            //编辑
            Route::post('{id?}', 'CampaignController@save')->name('campaign.save')->middleware('permission:campaign.edit');
            Route::post('{id}/enable', 'CampaignController@enable')->name('campaign.enable')->middleware('permission:campaign.edit');
            Route::post('{id}/disable', 'CampaignController@disable')->name('campaign.disable')->middleware('permission:campaign.edit');
            //删除
//        Route::delete('destroy', 'CampaignController@destroy')->name('campaign.destroy')->middleware('permission:campaign.destroy');

            // 广告
//            Route::group(['prefix'=>'{campaign_id}/ad', 'middleware' => 'permission:campaign.ad'], function () {
//                Route::get('data', 'AdController@data')->name('campaign.ad.data');
//                Route::get('list', 'AdController@list')->name('campaign.ad');
//                //编辑
//                Route::get('{id?}', 'AdController@edit')->name('campaign.ad.edit')->middleware('permission:campaign.ad.edit');
//                Route::post('{id?}', 'AdController@save')->name('campaign.ad.save')->middleware('permission:campaign.ad.edit');
//                Route::post('{id}/enable', 'AdController@enable')->name('ad.enable')->middleware('permission:campaign.ad.edit');
//                Route::post('{id}/disable', 'AdController@disable')->name('ad.disable')->middleware('permission:campaign.ad.edit');
//                //删除
////            Route::delete('destroy', 'AdController@destroy')->name('campaign.ad.destroy')->middleware('permission:campaign.ad.destroy');
//            });
        });

        // 区域
//        Route::group(['prefix'=>'{campaign_id}/region', 'middleware' => 'permission:campaign'], function () {
//            Route::get('data', 'RegionController@data')->name('campaign.region.data');
//            Route::get('list', 'RegionController@list')->name('campaign.region');
//        });

        //文件
        Route::post('Asset', 'AssetController@processMediaFiles')->name('asset.process');
    });

});
