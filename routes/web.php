<?php

use Illuminate\Support\Facades\Auth;
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

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Auth::routes();

Route::get('email/verify', 'Auth\VerificationController@show')->name('verification.notice');
Route::get('email/verify/{id}/{hash}', 'Auth\VerificationController@verify')->name('verification.verify');
Route::post('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');

Route::get('/home', 'HomeController@index')->name('home')->middleware('verified');

Route::post('/home', 'UserController@update')->name('user_update');
Route::post('/subscribe', 'UserController@subscribe')->name('user_subscribe');

Route::prefix('user')->middleware('verified')->group(function() {
    Route::get('manage', 'UserController@manage')->name('user_manage');
    Route::post('manage', 'UserController@update')->name('user_update');
    Route::get('obfuscate/{field}', 'UserController@obfuscate')->name('obfuscate');
    Route::get('delete', 'UserController@delete')->name('user_delete');
});

Route::get('/thanks', function() {
    return view('subscription_thanks');
})->name('subscription_thanks');
