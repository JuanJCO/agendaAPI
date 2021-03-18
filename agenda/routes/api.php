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

Route::group(['middleware'=> ['jwt.verify']], function() {
	Route::get('user','App\Http\Controllers\AuthController@getAuthenticatedUser');
    Route::get('logout','App\Http\Controllers\AuthController@logout');
    Route::post('contact/create','App\Http\Controllers\ContactController@store');
    Route::get('contact/index', 'App\Http\Controllers\ContactController@index');
    Route::post('contact/delete','App\Http\Controllers\ContactController@delete');
    Route::post('deleteUser', 'App\Http\Controllers\AuthController@delete');
    Route::post('contact/update/{id}','App\Http\Controllers\ContactController@update');
    Route::post('update','App\Http\Controllers\AuthController@update');
	Route::get('contact/search','App\Http\Controllers\AuthController@search');
}); 

Route::post('register', 'App\Http\Controllers\AuthController@register');
Route::post('login', 'App\Http\Controllers\AuthController@authenticate');

Route::post('password/email', 'App\Http\Controllers\AuthController@forgot');
Route::post('password/reset', 'App\Http\Controllers\AuthController@reset');
