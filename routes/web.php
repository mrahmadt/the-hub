<?php

use Illuminate\Support\Facades\Route;

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


Route::get('login', 'Auth\LoginController@show')->name('login');
Route::get('login/{driver}', 'Auth\LoginController@redirectToProvider')->name('social.oauth');
Route::get('login/{driver}/callback', 'Auth\LoginController@handleProviderCallback')->name('social.callback');
Route::get('logout', 'Auth\LoginController@logout')->name('logout');

Route::group(['middleware' => 'auth'], function () {

Route::get('/', 'ApplicationsController@myapps')->name('home');
Route::get('/home', 'ApplicationsController@myapps')->name('welcome.home');
Route::get('/categories/{category?}','CategoriesController@index')->name('showCategory');
Route::get('/tab/iframe/{tab_id}','TabsController@iframe')->name('showIframeTab');
Route::get('/tab/page/{tab_id}','TabsController@page')->name('showPageTab');
Route::get('/myapps','ApplicationsController@myapps')->name('showMyApps');
Route::get('/application/info/{application_id}','ApplicationsController@appinfo')->name('showAppinfo');
Route::get('/application/pin/{application_id}', 'ApplicationsController@pin');
Route::get('/application/unpin/{application_id}', 'ApplicationsController@unpin');
Route::get('/page/{page_id}', 'PagesController@view');


});

Route::get('/manifest.json', function () {
    return response(view('manifest_json'),200, ['Content-Type' => 'application/json']);
});

