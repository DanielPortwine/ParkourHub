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

Route::get('users', 'UserController@listing')->middleware('verified')->name('user_listing');
Route::prefix('user')->middleware('verified')->group(function() {
    Route::get('/view/{id}/{tab?}', 'UserController@view')->name('user_view');
    Route::get('/manage', 'UserController@manage')->name('user_manage');
    Route::post('/manage', 'UserController@update')->name('user_update');
    Route::get('/reset_password', 'UserController@resetPassword')->name('user_reset_password');
    Route::get('/obfuscate/{field}', 'UserController@obfuscate')->name('obfuscate');
    Route::get('/delete', 'UserController@delete')->name('user_delete');
    Route::get('/spots', 'UserController@spots')->name('user_spots');
    Route::get('/hitlist', 'UserController@hitlist')->name('user_hitlist');
    Route::get('/hitlist/completed', 'UserController@hitlistCompleted')->name('user_hitlist_completed');
    Route::get('/reviews', 'UserController@reviews')->name('user_reviews');
    Route::get('/challenges', 'UserController@challenges')->name('user_challenges');
    Route::get('/entries', 'UserController@entries')->name('user_entries');
    Route::get('/fetch_hometown_bounding', 'UserController@fetchHometownBounding');
    Route::get('/follow/{id}', 'UserController@follow')->name('user_follow');
    Route::get('/unfollow/{id}', 'UserController@unfollow')->name('user_unfollow');
    Route::get('/followers', 'UserController@followers')->name('user_followers');
    Route::get('/follow_requests', 'UserController@followRequests')->name('user_follow_requests');
    Route::get('/accept_follower/{id}', 'UserController@acceptFollower')->name('user_accept_follower');
    Route::get('/reject_follower/{id}', 'UserController@rejectFollower')->name('user_reject_follower');
});

Route::get('/premium', 'PremiumController@index')->name('premium');
Route::prefix('premium')->middleware('verified')->group(function() {
    Route::post('/register', 'PremiumController@register')->name('premium_register');
    Route::post('/update', 'PremiumController@update')->name('premium_update');
    Route::get('/cancel', 'PremiumController@cancel')->name('premium_cancel');
    Route::get('/resume', 'PremiumController@resume')->name('premium_resume');
    Route::get('/restart', 'PremiumController@restart')->name('premium_restart');
});

Route::get('/spots', 'SpotController@index')->name('spots');
Route::get('/spots/fetch', 'SpotController@fetch')->name('spot_fetch');
Route::prefix('spots')->middleware('verified')->group(function() {
    Route::get('/all', 'SpotController@listing')->name('spot_listing');
    Route::get('/spot/{id}/{tab?}', 'SpotController@view')->name('spot_view');
    Route::post('/create', 'SpotController@create')->middleware('optimizeImages')->name('spot_create');
    Route::get('/edit/{id}', 'SpotController@edit')->name('spot_edit');
    Route::post('/edit/{id}', 'SpotController@update')->middleware('optimizeImages')->name('spot_update');
    Route::get('/delete/{id}', 'SpotController@delete')->name('spot_delete');
    Route::get('/search', 'SpotController@search')->name('spot_search');
    Route::get('/add_to_hitlist/{id}', 'SpotController@addToHitlist')->name('add_to_hitlist');
    Route::get('/tick_off_hitlist/{id}', 'SpotController@tickOffHitlist')->name('tick_off_hitlist');
    Route::get('/report/{id}', 'SpotController@report')->name('spot_report');
    Route::get('/delete_reported/{id}', 'SpotController@deleteReported')->name('spot_report_delete');
    Route::post('/add_movement/{id}', 'SpotController@addMovement')->name('spot_add_movement');
    Route::get('/remove_movement/{spotID}/{movement}', 'SpotController@removeMovement')->name('spot_remove_movement');
});

Route::prefix('/reviews')->middleware('verified')->group(function() {
    Route::post('/create', 'ReviewController@create')->name('review_create');
    Route::get('/edit/{id}', 'ReviewController@edit')->name('review_edit');
    Route::post('/edit/{id}', 'ReviewController@update')->name('review_update');
    Route::get('/delete/{id}', 'ReviewController@delete')->name('review_delete');
    Route::get('/report/{id}', 'ReviewController@report')->name('review_report');
    Route::get('/delete_reported/{id}', 'ReviewController@deleteReported')->name('review_report_delete');
});

Route::prefix('/spot_comments')->middleware('verified')->group(function() {
    Route::post('/create', 'SpotCommentController@create')->middleware('optimizeImages')->name('spot_comment_create');
    Route::get('/edit/{id}', 'SpotCommentController@edit')->name('spot_comment_edit');
    Route::post('/edit/{id}', 'SpotCommentController@update')->middleware('optimizeImages')->name('spot_comment_update');
    Route::get('/delete/{id}', 'SpotCommentController@delete')->name('spot_comment_delete');
    Route::get('/like/{id}', 'SpotCommentController@like')->name('spot_comment_like');
    Route::get('/unlike/{id}', 'SpotCommentController@unlike')->name('spot_comment_unlike');
    Route::get('/report/{id}', 'SpotCommentController@report')->name('spot_comment_report');
    Route::get('/delete_reported/{id}', 'SpotCommentController@deleteReported')->name('spot_comment_report_delete');
});

Route::prefix('challenges')->middleware('verified')->group(function() {
    Route::get('/all', 'ChallengeController@listing')->name('challenge_listing');
    Route::get('/challenge/{id}', 'ChallengeController@view')->name('challenge_view');
    Route::post('/create', 'ChallengeController@create')->middleware('optimizeImages')->name('challenge_create');
    Route::get('/edit/{id}', 'ChallengeController@edit')->name('challenge_edit');
    Route::post('/edit/{id}', 'ChallengeController@update')->middleware('optimizeImages')->name('challenge_update');
    Route::get('/delete/{id}', 'ChallengeController@delete')->name('challenge_delete');
    Route::post('/enter/{id}', 'ChallengeController@enter')->name('challenge_enter');
    Route::get('/win/{id}', 'ChallengeController@win')->name('challenge_win');
    Route::get('/report/{id}', 'ChallengeController@report')->name('challenge_report');
    Route::get('/delete_reported/{id}', 'ChallengeController@deleteReported')->name('challenge_report_delete');
    Route::get('/entries/report/{id}', 'ChallengeController@reportEntry')->name('entry_report');
    Route::get('/entries/delete_reported/{id}', 'ChallengeController@deleteReportedEntry')->name('entry_report_delete');
});

Route::prefix('movements')->middleware('verified')->group(function() {
    Route::get('/', 'MovementController@listing')->name('movement_listing');
    Route::get('/view/{id}/{tab?}', 'MovementController@view')->name('movement_view');
    Route::post('/create', 'MovementController@create')->name('movement_create');
    Route::get('/edit/{id}', 'MovementController@edit')->name('movement_edit');
    Route::post('/edit/{id}', 'MovementController@update')->name('movement_update');
    Route::get('/delete/{id}', 'MovementController@delete')->name('movement_delete');
    Route::get('/report/{id}', 'MovementController@report')->name('movement_report');
    Route::get('/delete_reported/{id}', 'MovementController@deleteReported')->name('movement_report_delete');
    Route::post('/link', 'MovementController@link')->name('movements_link');
    Route::post('/unlink', 'MovementController@unlink')->name('movements_unlink');
    Route::get('/officialise/{id}', 'MovementController@officialise')->name('movement_officialise');
    Route::get('/unofficialise/{id}', 'MovementController@unofficialise')->name('movement_unofficialise');
    Route::get('/getMovements', 'MovementController@getMovements')->name('movement_search');
    Route::get('/getMovementCategories', 'MovementController@getMovementCategories')->name('movement_category_search');
});

Route::prefix('hometown')->middleware('verified')->group(function() {
    Route::get('/spots', 'HometownController@spots')->name('hometown_spots');
    Route::get('/challenges', 'HometownController@challenges')->name('hometown_challenges');
});

Route::prefix('admin')->middleware('verified')->group(function() {
    Route::get('/reports/{type?}', 'ReportController@index')->name('report_listing');
    Route::get('/reports/discard/{id}/{type}', 'ReportController@discard')->name('report_discard');
});

Route::prefix('ajax')->group(function() {
    Route::get('searchAddress/{search}', 'AjaxController@searchAddress');
    Route::get('searchHometown/{hometown}', 'AjaxController@searchHometown');
    Route::get('/isVerifiedLoggedIn', 'AjaxController@isVerifiedLoggedIn');
});

Route::get('/thanks', function() {
    return view('subscription_thanks');
})->name('subscription_thanks');
