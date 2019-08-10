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

Route::resource('feeds', 'FeedController')->middleware('ipcheck');

Route::get('/clear-cache', function() {
	Artisan::call('cache:clear');
	return "Cache is cleared";
});
