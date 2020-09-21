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
    Route::post('auth/sendcode', 'AuthController@sendCode');
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('auth/user', 'AuthController@user');
        Route::post('auth/user', 'AuthController@update');
        Route::post('auth/logout', 'AuthController@logout');
    });
    Route::apiResource('users', 'UserController')->middleware('permission:system.user');
    Route::get('users/{user}/permissions', 'UserController@permissions')->middleware('permission:system.user');
    Route::put('users/{user}/permissions', 'UserController@updatePermissions')->middleware('permission:system.user.permission');
    //    Route::apiResource('roles', 'RoleController')->middleware('permission:system.permission');
    Route::get('users/operation/log', 'UserController@opLog')->name('user.oplog')->middleware('permission:system.user.oplog');
    Route::get('roles/{role}/permissions', 'RoleController@permissions')->middleware('system.role.permission');
    Route::apiResource('permissions', 'PermissionController')->middleware('permission:system.permission');

    Route::group(['namespace' => 'Api', 'middleware' => 'operation.log'], function () {
        // Account
        Route::group(['prefix' => 'account'], function () {
            Route::get('', 'AccountController@list')->name('account')->middleware('permission:advertise.account');
            Route::get('oplog', 'AccountController@opLog')->name('account.oplog')->middleware('permission:advertise.account');
            Route::get('advertiser', 'AccountController@advertiserList')->name('account.advertiser')->middleware('permission:advertise.manage');

            // 子账号
            Route::post('{main_user_id}/assign', 'AccountController@assign')->name('account.assign')->middleware('permission:advertise.account.edit');
            Route::post('{main_user_id}/detach/{account_id}', 'AccountController@detach')->name('account.detach')->middleware('permission:advertise.account.edit');

            //Route::get('{main_user_id?}/subs','AccountController@list')->name('account')->middleware('permission:advertise.account');
            Route::get('permissions', 'AccountController@allPermission')->name('account.permission')->middleware('permission:advertise.account');
            Route::get('{id}/permission/to/{main_user_id?}', 'AccountController@permissions')->name('account.permission')->middleware('permission:advertise.account');
            Route::post('{account}/permission/to/{main_account_id}', 'AccountController@updatePermissions')->name('account.permission.update')->middleware('permission:advertise.account.edit');

            //编辑
            Route::post('{id?}', 'AccountController@save')->name('account.save')->middleware('permission:advertise.account.edit');
            Route::post('{id}/addcredit', 'AccountController@addCredit')->name('account.addcredit')->middleware('permission:advertise.account.edit');
            Route::post('{id}/addcash', 'AccountController@addCash')->name('account.addcash')->middleware('permission:advertise.account.cash');
           
            Route::post('{id}/enable', 'AccountController@enable')->name('account.enable')->middleware('permission:advertise.account.edit');
            Route::post('{id}/disable', 'AccountController@disable')->name('account.disable')->middleware('permission:advertise.account.edit');
            Route::post('{id}/advertising/enable', 'AccountController@enableAdvertising')->name('account.advertising.enable')->middleware('permission:advertise.account.edit');
            Route::post('{id}/advertising/disable', 'AccountController@disableAdvertising')->name('account.advertising.disable')->middleware('permission:advertise.account.edit');
            Route::post('{id}/publishing/enable', 'AccountController@enablePublishing')->name('account.publishing.enable')->middleware('permission:advertise.account.edit');
            Route::post('{id}/publishing/disable', 'AccountController@disablePublishing')->name('account.publishing.disable')->middleware('permission:advertise.account.edit');
            Route::post('{id}/bill', 'AccountController@setBill')->name('account.bill')->middleware('permission:advertise.bill');

            //删除
            //            Route::delete('account/destroy','AccountController@destroy')->name('account.destroy')->middleware('permission:advertise.account.destroy');
            Route::group(['prefix' => 'auth'], function () {
                Route::get('token', 'AuthController@accountTokenList')->name('auth.token')->middleware('permission:advertise.account.edit');
                Route::post('token', 'AuthController@makeAccountToken')->name('auth.token.make')->middleware('permission:advertise.account.edit');
                Route::delete('token/{id}', 'AuthController@delAccountToken')->name('auth.token.destroy')->middleware('permission:advertise.account.edit');
            });
        });

        // Bill
        Route::group(['prefix' => 'bill'], function () {
            Route::get('', 'BillController@list')->name('bill')->middleware('permission:advertise.bill');
            // 确认已支付
            Route::post('{id}/pay', 'BillController@pay')->name('bill.pay')->middleware('permission:advertise.bill.pay');
            Route::get('{id}/invoice', 'BillController@invoice')->name('bill.invoice');
            Route::get('{id}/invoice/pdf', 'BillController@invoicePdf')->name('bill.invoice.pdf');
            Route::post('{id}/invoice/send', 'BillController@sendInvoice')->name('bill.invoice.send');
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
            Route::get('data', 'ChannelController@data')->name('channel.data');
            Route::get('country-list', 'ChannelController@countryList')->name('channel.country');
            Route::get('placement-data', 'ChannelController@placementData')->name('channel.placement-data')->middleware('permission:placement.manage');
            Route::get('placement', 'ChannelController@placement')->name('channel.placement')->middleware('permission:placement.manage');

            Route::post('{id?}', 'ChannelController@save')->name('channel.save')->middleware('permission:advertise.channel.edit');
            Route::post('{id}/restart', 'ChannelController@restartChannel')->name('channel.restart')->middleware('permission:advertise.channel.edit');

            // 买量APP数据
            Route::get('{channel_id}/app', 'ChannelController@app')->name('campaign.channel.app')->middleware('permission:advertise.channel');
            Route::post('{channel_id}/app/{app_id}/joinblack', 'ChannelController@joinBlack')->name('channel.app.joinblack')->middleware('permission:advertise.channel.edit');;
            Route::post('{channel_id}/app/{app_id}/removeblack', 'ChannelController@removeBlack')->name('channel.app.removeblack')->middleware('permission:advertise.channel.edit');;
        });

        // 应用管理
        Route::group(['prefix' => 'app', 'middleware' => 'permission:advertise.app'], function () {
            Route::get('', 'AppController@list')->name('app');
            Route::get('data', 'AppController@data')->name('app.data');
            Route::get('applist', 'AppController@appList')->name('app.list')->middleware('permission:advertise.app.edit');
            
            Route::get('taglist', 'AppController@tagList')->name('app.tag.list')->middleware('permission:advertise.app.edit');
            Route::post('tag/{id?}', 'AppController@tagSave')->name('app.tag.save')->middleware('permission:advertise.app.edit');
            // 卖量Channel数据
            Route::get('{app_id}/channel', 'AppController@channel')->name('campaign.app.channel')->middleware('permission:advertise.app');
            Route::get('{app_id}/campaign', 'AppController@campaign')->name('campaign.app.campaign')->middleware('permission:advertise.app');
            
            //编辑
            Route::post('{id?}', 'AppController@save')->name('app.save')->middleware('permission:advertise.app.edit');
            Route::post('{id}/enable', 'AppController@enable')->name('app.enable')->middleware('permission:advertise.app.edit');
            Route::post('{id}/disable', 'AppController@disable')->name('app.disable')->middleware('permission:advertise.app.edit');
            Route::post('{id}/enableaudi', 'AppController@enableAudi')->name('app.enableaudi')->middleware('permission:advertise.app.edit');
            Route::post('{id}/disableaudi', 'AppController@disableAudi')->name('app.disableaudi')->middleware('permission:advertise.app.edit');
            Route::get('{id}/iosinfo', 'AppController@iosInfo')->name('app.iosinfo');
            //删除
            //        Route::delete('destroy', 'AppController@destroy')->name('app.destroy')->middleware('permission:app.destroy');
        });

        // 活动管理
        Route::group(['prefix' => 'campaign'], function () {
            Route::get('', 'CampaignController@list')->name('advertise.campaign');
            //编辑
            Route::post('{id?}', 'CampaignController@save')->name('campaign.save')->middleware('permission:campaign.advertise.edit');
            Route::post('{id}/enable', 'CampaignController@enable')->name('campaign.enable')->middleware('permission:advertise.campaign.edit');
            Route::post('{id}/disable', 'CampaignController@disable')->name('campaign.disable')->middleware('permission:advertise.campaign.edit');
            Route::post('{id}/restart', 'CampaignController@clearRedis')->name('campaign.clear')->middleware('permission:advertise.campaign.restart');
            //删除
            //        Route::delete('destroy', 'CampaignController@destroy')->name('campaign.destroy')->middleware('permission:campaign.destroy');

            // 子渠道数据
            Route::get('{campaign_id}/channel', 'CampaignController@channel')->name('campaign.channel')->middleware('permission:advertise.campaign');

            Route::group(['prefix' => '{campaign_id}/channel/{channel_id}', 'middleware' => 'permission:advertise.campaign.edit'], function () {
                Route::post('joinblack', 'CampaignController@joinBlack')->name('campaign.channel.joinblack');
                Route::post('removeblack', 'CampaignController@removeBlack')->name('campaign.channel.removeblack');
                Route::post('joinwhite', 'CampaignController@joinWhite')->name('campaign.channel.joinwhite');
                Route::post('removewhite', 'CampaignController@removeWhite')->name('campaign.channel.removewhite');
            });
            Route::get('adreview', 'AdController@listReview')->name('campaign.ad');
            // 广告
            Route::get('ad/taglist', 'AdController@tagList')->name('campaign.ad.tag.list')->middleware('permission:advertise.campaign.ad.edit');
            Route::get('ad/tagall', 'AdController@tagAll')->name('campaign.ad.tag.list')->middleware('permission:advertise.campaign.ad.edit');
            Route::post('ad/tag/{id?}', 'AdController@tagSave')->name('campaign.ad.tag.save')->middleware('permission:advertise.campaign.ad.edit');

            Route::group(['prefix' => '{campaign_id}/ad', 'middleware' => 'permission:advertise.campaign'], function () {
                Route::get('', 'AdController@list')->name('campaign.ad');
                //编辑
                Route::post('{id?}', 'AdController@save')->name('campaign.ad.save')->middleware('permission:advertise.campaign.ad.edit');
                Route::post('{id}/enable', 'AdController@enable')->name('campaign.ad.enable')->middleware('permission:advertise.campaign.ad.edit');
                Route::post('{id}/disable', 'AdController@disable')->name('campaign.ad.disable')->middleware('permission:advertise.campaign.ad.edit');
                Route::post('{id}/restart', 'AdController@clearRedis')->name('campaign.ad.clear')->middleware('permission:advertise.campaign.ad.restart');
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
        Route::group(['prefix' => 'statis', 'middleware' => 'permission:advertise.statis'], function () {
            Route::get('total', 'StatisController@total')->name('statis.total');
            Route::get('newadd', 'StatisController@newAdd')->name('statis.newadd');
            Route::get('group', 'StatisController@group')->name('statis.device');
            Route::get('group/channel', 'StatisController@groupByChannel')->name('statis.device');
            Route::get('device', 'StatisController@device')->name('statis.device');
            Route::get('device/channel', 'StatisController@deviceByChannel')->name('statis.device');
            Route::get('device/app', 'StatisController@deviceByApp')->name('statis.device');
            Route::get('device/country', 'StatisController@deviceByCountry')->name('statis.device.country');
        });

        Route::group(['prefix' => 'audience', 'middleware' => 'permission:audience.manage'], function () {
            Route::post('upload', 'AudienceController@upload')->name('audience.uplolad');
            Route::get('upload/log', 'AudienceController@idfaLog')->name('audience.uplolad.log');
            Route::get('app', 'AudienceController@getApp')->name('audience.app');
            Route::get('taglist', 'AudienceController@taglist')->name('audience.taglist');
            Route::get('tag/{tag_id}/apps', 'AudienceController@tagApps')->name('audience.tagapps');
            Route::put('tag/{tag_id}/apps', 'AudienceController@updateTagApps')->name('audience.tagapps');
        });
        //文件
        Route::post('Asset', 'AssetController@processMediaFiles')->name('asset.process');
    });
});
