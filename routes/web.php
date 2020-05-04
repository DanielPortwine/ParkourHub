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
    Route::get('/spots', 'UserController@spots')->name('user_spots');
    Route::get('/hitlist', 'UserController@hitlist')->name('user_hitlist');
    Route::get('/challenges', 'UserController@challenges')->name('user_challenges');
    Route::get('/entries', 'UserController@entries')->name('user_entries');
});

Route::prefix('spots')->middleware('verified')->group(function() {
    Route::get('/', 'SpotController@index')->name('spots');
    Route::get('/spot/{id}', 'SpotController@view')->name('spot_view');
    Route::get('/fetch', 'SpotController@fetch')->name('spot_fetch');
    Route::post('/create', 'SpotController@create')->name('spot_create');
    Route::get('/edit/{id}', 'SpotController@edit')->name('spot_edit');
    Route::post('/edit/{id}', 'SpotController@update')->name('spot_update');
    Route::get('/delete/{id}', 'SpotController@delete')->name('spot_delete');
    Route::get('/search', 'SpotController@search')->name('spot_search');
    Route::get('/add_to_hitlist/{id}', 'SpotController@addToHitlist')->name('add_to_hitlist');
    Route::get('/tick_off_hitlist/{id}', 'SpotController@tickOffHitlist')->name('tick_off_hitlist');
});

Route::prefix('challenges')->middleware('verified')->group(function() {
    Route::get('/challenge/{id}', 'ChallengeController@view')->name('challenge_view');
    Route::get('/create', 'ChallengeController@create')->name('challenge_create');
    Route::post('/create', 'ChallengeController@save')->name('challenge_save');
    Route::get('/edit/{id}', 'ChallengeController@edit')->name('challenge_edit');
    Route::post('/edit/{id}', 'ChallengeController@update')->name('challenge_update');
    Route::get('/delete/{id}', 'ChallengeController@delete')->name('challenge_delete');
    Route::post('/enter/{id}', 'ChallengeController@enter')->name('challenge_enter');
    Route::get('/win/{id}', 'ChallengeController@win')->name('challenge_win');
});

Route::get('/thanks', function() {
    return view('subscription_thanks');
})->name('subscription_thanks');
