<?php

header('Access-Control-Allow-Origin: *');

if (env('APP_ENV') == 'production'){
    URL::forceScheme('https');
}

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);

Route::get('/', function () {
    return view('welcome');
});
Route::group(['prefix' => '/advertiser123'], function () {

    Route::get('/teste/{id}', 'Advertiser\AdvertiserController@getTeste');

    Route::get('/reports/{token}', 'Advertiser\AdvertiserController@reports');
    Route::get('/start/{idAdvertiser}/{token}', 'Advertiser\AdvertiserController@startCampaing');
    Route::get('/pause/{idAdvertiser}/{token}', 'Advertiser\AdvertiserController@pauseCampaing');
    Route::get('/approve/{idAdvertiser}/{token}', 'Advertiser\AdvertiserController@approve');
    Route::get('/update/{idAdvertiser}/{token}', 'Advertiser\AdvertiserController@updateLineItem');
    Route::get('/token/{key}', 'Advertiser\AdvertiserController@getGenerateToken');
    Route::post('/{key}', 'Advertiser\AdvertiserController@postInfo');

    Route::get('/update-teste', 'Advertiser\AdvertiserController@updateLineItemTeste');

});


Route::group(['prefix' => '/run'], function () {
    AdvancedRoute::controller('admanager', 'Painel\AdManagerController');
    Route::get('domain/update-posts', 'Painel\DomainrController@UpdatePosts');

    Route::get('admanager/advertiser/report', 'Advertiser\AdvertiserController@ReportAdvertiser');

    Route::post('data-analytics', 'Painel\InfluencersPostsController@getProgramaticRealTime');

    AdvancedRoute::controller('data-influencers', 'Painel\InfluencersPostsController');

    AdvancedRoute::controller('plugin', 'Painel\PluginController');

});

AdvancedRoute::controller('/', 'Site\HomeController');

Auth::routes();