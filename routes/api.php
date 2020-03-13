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
        // Account
        Route::group(['prefix' => 'account'],function (){
            Route::get('','AccountController@list')->name('account')->middleware('permission:advertise.account');
            // 子账号
            //Route::get('{main_user_id?}/subs','AccountController@list')->name('account')->middleware('permission:advertise.account');
            //编辑
            Route::post('{id?}','AccountController@save')->name('account.save')->middleware('permission:advertise.account.edit');
            Route::post('{id}/enable', 'AccountController@enable')->name('account.enable')->middleware('permission:advertise.account.edit');
            Route::post('{id}/disable', 'AccountController@disable')->name('account.disable')->middleware('permission:advertise.account.edit');
            //删除
//            Route::delete('account/destroy','AccountController@destroy')->name('account.destroy')->middleware('permission:advertise.account.destroy');
        });

        // Bill
        Route::group(['prefix' => 'bill'],function (){
            Route::get('','BillController@list')->name('bill')->middleware('permission:advertise.bill');
            // 确认已支付
            Route::post('{id}/pay', 'BillController@pay')->name('bill.pay')->middleware('permission:advertise.bill.pay');
        });
        
        // API
        Route::group(['prefix' => 'auth'], function () {
            Route::get('token', 'AuthController@tokenList')->name('auth.token')->middleware('permission:basic.auth.token');
            Route::post('token', 'AuthController@makeToken')->name('auth.token.make')->middleware('permission:basic.auth.token.make');
            Route::delete('token/{id}', 'AuthController@destroy')->name('auth.token.destroy')->middleware('permission:basic.auth.token.destroy');
        });
        // 渠道
        Route::group(['prefix' => 'channel'], function () {
            Route::get('', 'ChannelController@list')->name('channel.list')->middleware('permission:advertise.channel');
            Route::post('{id?}', 'ChannelController@save')->name('channel.save')->middleware('permission:advertise.channel.edit');

            // 买量APP数据
            Route::get('{channel_id}/app', 'ChannelController@app')->name('campaign.channel.app')->middleware('permission:advertise.campaign');

        });

        // 应用管理
        Route::group(['prefix'=>'app', 'middleware' => 'permission:advertise.app'], function () {
            Route::get('', 'AppController@list')->name('app');
            Route::get('data', 'AppController@data')->name('app.data');

            //编辑
//            Route::post('{id?}', 'AppController@save')->name('app.save')->middleware('permission:advertise.app.edit');
            Route::post('{id}/enable', 'AppController@enable')->name('app.enable')->middleware('permission:advertise.app.edit');
            Route::post('{id}/disable', 'AppController@disable')->name('app.disable')->middleware('permission:advertise.app.edit');

            //删除
//        Route::delete('destroy', 'AppController@destroy')->name('app.destroy')->middleware('permission:app.destroy');
        });

        // 活动管理
        Route::group(['prefix'=>'campaign'], function () {
            Route::get('', 'CampaignController@list')->name('advertise.campaign');
            //编辑
            Route::post('{id?}', 'CampaignController@save')->name('campaign.save')->middleware('permission:campaign.advertise.edit');
            Route::post('{id}/enable', 'CampaignController@enable')->name('campaign.enable')->middleware('permission:advertise.campaign.edit');
            Route::post('{id}/disable', 'CampaignController@disable')->name('campaign.disable')->middleware('permission:advertise.campaign.edit');
            //删除
//        Route::delete('destroy', 'CampaignController@destroy')->name('campaign.destroy')->middleware('permission:campaign.destroy');

            // 子渠道数据
            Route::get('{campaign_id}/channel', 'CampaignController@channel')->name('campaign.channel')->middleware('permission:advertise.campaign');

            // 广告
            Route::group(['prefix'=>'{campaign_id}/ad', 'middleware' => 'permission:advertise.campaign'], function () {
                Route::get('', 'AdController@list')->name('campaign.ad');
                //编辑
                Route::post('{id?}', 'AdController@save')->name('campaign.ad.save')->middleware('permission:advertise.campaign.ad.edit');
                Route::post('{id}/enable', 'AdController@enable')->name('campaign.ad.enable')->middleware('permission:advertise.campaign.ad.edit');
                Route::post('{id}/disable', 'AdController@disable')->name('campaign.ad.disable')->middleware('permission:advertise.campaign.ad.edit');
                Route::post('{id}/pass', 'AdController@passReview')->name('campaign.ad.review.pass')->middleware('permission:advertise.campaign.ad.edit');

                //删除
//            Route::delete('destroy', 'AdController@destroy')->name('campaign.ad.destroy')->middleware('permission:campaign.ad.destroy');
            });
        });

        // 区域
//        Route::group(['prefix'=>'{campaign_id}/region', 'middleware' => 'permission:campaign'], function () {
//            Route::get('data', 'RegionController@data')->name('campaign.region.data');
//            Route::get('list', 'RegionController@list')->name('campaign.region');
//        });

        // 统计
        Route::group(['prefix'=>'statis', 'middleware' => 'permission:advertise.statis'], function () {
            Route::get('total', 'StatisController@total')->name('statis.total');
            Route::get('group', 'StatisController@group')->name('statis.device');
            Route::get('group/channel', 'StatisController@groupByChannel')->name('statis.device');
            Route::get('device', 'StatisController@device')->name('statis.device');
            Route::get('device/channel', 'StatisController@deviceByChannel')->name('statis.device');
            Route::get('device/app', 'StatisController@deviceByApp')->name('statis.device');
        });

        //文件
        Route::post('Asset', 'AssetController@processMediaFiles')->name('asset.process');
    });

});
