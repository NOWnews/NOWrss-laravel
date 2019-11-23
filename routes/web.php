<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome')->with('uuid', 'none');
});

Route::get('/rss/{uuid}', 'RssController@index');

Route::get('/subChannel/rssPetsmao', 'RssController@petsmaoRss2');

Route::resource('feeds', 'FeedController')->middleware('ipcheck');

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    return "Cache is cleared";
});

Route::get('/subChannel/babyouRss/{type}', 'RssController@babyouRss2');

Route::get('/subChannel/nowGamesRss/{type}', 'RssController@nowGamesRss');

Route::get('/subChannel/petsmaoRss/{type}', 'RssController@petsmaoRss');

Route::get('/subChannel/chinapostRss/{type}', 'RssController@chinapostRss');

