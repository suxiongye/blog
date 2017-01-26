<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of the routes that are handled
| by your application. Just tell Laravel the URIs it should respond
| to using a Closure or controller method. Build something great!
|
*/
Route::get('/', function () {
    return redirect('/blog');
});

Route::get('blog', 'BlogController@index');
Route::get('blog/{slug}', 'BlogController@showPost');

Route::get('contact', 'ContactController@showForm');
Route::post('contact', 'ContactController@sendContactInfo');

Route::get('rss', 'BlogController@rss');
Route::get('sitemap.xml', 'BlogController@siteMap');
//Admin area
Route::get('admin', function (){
   return redirect('/admin/post');
});
Auth::routes();

Route::group(['namespace' => 'Admin', 'middleware' => 'auth'], function (){
    Route::resource('admin/post', 'PostController', ['except' => 'show']);
    Route::resource('admin/tag', 'TagController', ['except' => 'show']);
    Route::get('admin/upload', 'UploadController@index');
    Route::post('admin/upload/file', 'UploadController@uploadFile');
    Route::delete('admin/upload/file', 'UploadController@deleteFile');
    Route::post('admin/upload/folder', 'UploadController@createFolder');
    Route::delete('admin/upload/folder', 'UploadController@deleteFolder');
});



Route::get('/home', 'HomeController@index');
