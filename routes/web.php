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
    Route::get('/manage', 'UserController@manage')->withoutMiddleware('verified')->middleware('auth')->name('user_manage');
    Route::post('/manage', 'UserController@update')->withoutMiddleware('verified')->middleware('auth')->name('user_update');
    Route::get('/reset_password', 'UserController@resetPassword')->withoutMiddleware('verified')->middleware('auth')->name('user_reset_password');
    Route::get('/obfuscate/{field}', 'UserController@obfuscate')->withoutMiddleware('verified')->middleware('auth')->name('obfuscate');
    Route::get('/delete', 'UserController@delete')->withoutMiddleware('verified')->middleware('auth')->name('user_delete');
    Route::get('/spots', 'UserController@spots')->name('user_spots');
    Route::get('/hitlist', 'UserController@hitlist')->name('user_hitlist');
    Route::get('/hitlist/completed', 'UserController@hitlistCompleted')->name('user_hitlist_completed');
    Route::get('/reviews', 'UserController@reviews')->name('user_reviews');
    Route::get('/comments', 'UserController@comments')->name('user_comments');
    Route::get('/challenges', 'UserController@challenges')->name('user_challenges');
    Route::get('/entries', 'UserController@entries')->name('user_entries');
    Route::get('/movements', 'UserController@movements')->middleware('isPremium')->name('user_movements');
    Route::get('/equipment', 'UserController@Equipment')->middleware('isPremium')->name('user_equipment');
    Route::get('/fetch_hometown_bounding', 'UserController@fetchHometownBounding');
    Route::get('/follow/{id}', 'UserController@follow')->name('user_follow');
    Route::get('/unfollow/{id}', 'UserController@unfollow')->name('user_unfollow');
    Route::get('/remove_follower/{id}', 'UserController@removeFollower')->name('user_remove_follower');
    Route::get('/followers', 'UserController@followers')->name('user_followers');
    Route::get('/following', 'UserController@following')->name('user_following');
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

Route::prefix('spots')->middleware('verified')->group(function() {
    Route::get('/', 'SpotController@listing')->withoutMiddleware('verified')->name('spot_listing');
    Route::get('/map', 'SpotController@index')->withoutMiddleware('verified')->name('spots');
    Route::get('/fetch', 'SpotController@fetch')->withoutMiddleware('verified')->name('spot_fetch');
    Route::get('/spot/{id}/{tab?}', 'SpotController@view')->withoutMiddleware('verified')->name('spot_view');
    Route::post('/create', 'SpotController@create')->middleware('optimizeImages')->name('spot_create');
    Route::get('/edit/{id}', 'SpotController@edit')->name('spot_edit');
    Route::post('/edit/{id}', 'SpotController@update')->middleware('optimizeImages')->name('spot_update');
    Route::get('/delete/{id}/{from?}', 'SpotController@delete')->name('spot_delete');
    Route::get('/search', 'SpotController@search')->withoutMiddleware('verified')->name('spot_search');
    Route::get('/add_to_hitlist/{id}', 'SpotController@addToHitlist')->name('add_to_hitlist');
    Route::get('/remove_from_hitlist/{id}', 'SpotController@removeFromHitlist')->name('remove_from_hitlist');
    Route::get('/tick_off_hitlist/{id}', 'SpotController@tickOffHitlist')->name('tick_off_hitlist');
    Route::get('/report/{spot}', 'SpotController@report')->name('spot_report');
    Route::get('/discard_reports/{spot}', 'SpotController@discardReports')->name('spot_report_discard');
    Route::post('/add_movement/{spot}', 'SpotController@addMovement')->name('spot_add_movement');
    Route::get('/remove_movement/{spotID}/{movement}', 'SpotController@removeMovement')->name('spot_remove_movement');
    Route::post('/link_workout', 'SpotController@linkWorkout')->name('spot_workout_link');
});

Route::prefix('/reviews')->middleware('verified')->group(function() {
    Route::post('/create', 'ReviewController@create')->name('review_create');
    Route::get('/edit/{id}', 'ReviewController@edit')->name('review_edit');
    Route::post('/edit/{id}', 'ReviewController@update')->name('review_update');
    Route::get('/delete/{id}/{from?}', 'ReviewController@delete')->name('review_delete');
    Route::get('/report/{review}', 'ReviewController@report')->name('review_report');
    Route::get('/discard_reports/{review}', 'ReviewController@discardReports')->name('review_report_discard');
});

Route::prefix('/spot_comments')->middleware('verified')->group(function() {
    Route::post('/create', 'SpotCommentController@create')->middleware('optimizeImages')->name('spot_comment_create');
    Route::get('/edit/{id}', 'SpotCommentController@edit')->name('spot_comment_edit');
    Route::post('/edit/{id}', 'SpotCommentController@update')->middleware('optimizeImages')->name('spot_comment_update');
    Route::get('/delete/{id}/{from?}', 'SpotCommentController@delete')->name('spot_comment_delete');
    Route::get('/report/{spotComment}', 'SpotCommentController@report')->name('spot_comment_report');
    Route::get('/discard_reports/{spotComment}', 'SpotCommentController@discardReports')->name('spot_comment_report_discard');
});

Route::prefix('challenges')->middleware('verified')->group(function() {
    Route::get('/', 'ChallengeController@listing')->withoutMiddleware('verified')->name('challenge_listing');
    Route::get('/challenge/{id}', 'ChallengeController@view')->withoutMiddleware('verified')->name('challenge_view');
    Route::post('/create', 'ChallengeController@create')->middleware('optimizeImages')->name('challenge_create');
    Route::get('/edit/{id}', 'ChallengeController@edit')->name('challenge_edit');
    Route::post('/edit/{id}', 'ChallengeController@update')->middleware('optimizeImages')->name('challenge_update');
    Route::get('/delete/{id}/{redirect?}', 'ChallengeController@delete')->name('challenge_delete');
    Route::post('/enter/{id}', 'ChallengeController@enter')->name('challenge_enter');
    Route::get('/win/{id}', 'ChallengeController@win')->name('challenge_win');
    Route::get('/report/{challenge}', 'ChallengeController@report')->name('challenge_report');
    Route::get('/discard_reports/{challenge}', 'ChallengeController@discardReports')->name('challenge_report_discard');
    Route::get('/entries/report/{challengeEntry}', 'ChallengeController@reportEntry')->name('entry_report');
    Route::get('/entries/discard_reports/{challengeEntry}', 'ChallengeController@discardEntryReports')->name('entry_report_discard');
    Route::get('/entries/delete/{challengeEntry}', 'ChallengeController@deleteEntry')->name('entry_delete');
});

Route::prefix('movements')->middleware(['verified', 'isPremium'])->group(function() {
    Route::get('/', 'MovementController@listing')->name('movement_listing');
    Route::get('/view/{id}/{tab?}', 'MovementController@view')->name('movement_view');
    Route::get('/create', 'MovementController@create')->name('movement_create');
    Route::post('/create', 'MovementController@store')->name('movement_store');
    Route::get('/edit/{id}', 'MovementController@edit')->name('movement_edit');
    Route::post('/edit/{id}', 'MovementController@update')->name('movement_update');
    Route::get('/delete/{id}/{redirect?}', 'MovementController@delete')->name('movement_delete');
    Route::get('/report/{movement}', 'MovementController@report')->name('movement_report');
    Route::get('/discard_reports/{movement}', 'MovementController@discardReports')->name('movement_report_discard');
    Route::post('/link_progression', 'MovementController@linkProgression')->name('movements_link');
    Route::post('/unlink_progression', 'MovementController@unlinkProgression')->name('movements_unlink');
    Route::post('/link_exercise', 'MovementController@linkExercise')->name('movement_exercise_link');
    Route::post('/unlink_exercise', 'MovementController@unlinkExercise')->name('movement_exercise_unlink');
    Route::post('/link_equipment', 'MovementController@linkEquipment')->name('movement_equipment_link');
    Route::post('/unlink_equipment', 'MovementController@unlinkEquipment')->name('movement_equipment_unlink');
    Route::get('/officialise/{id}', 'MovementController@officialise')->name('movement_officialise');
    Route::get('/unofficialise/{id}', 'MovementController@unofficialise')->name('movement_unofficialise');
    Route::post('/set_movement_baseline', 'MovementController@setMovementBaseline')->name('set_movement_baseline');
});

Route::prefix('equipment')->middleware(['verified', 'isPremium'])->group(function() {
    Route::get('/', 'EquipmentController@listing')->name('equipment_listing');
    Route::get('/view/{id}', 'EquipmentController@view')->name('equipment_view');
    Route::get('/create', 'EquipmentController@create')->name('equipment_create');
    Route::post('/create', 'EquipmentController@store')->name('equipment_store');
    Route::get('/edit/{id}', 'EquipmentController@edit')->name('equipment_edit');
    Route::post('/edit/{id}', 'EquipmentController@update')->name('equipment_update');
    Route::get('/delete/{id}', 'EquipmentController@delete')->name('equipment_delete');
    Route::get('/report/{equipment}', 'EquipmentController@report')->name('equipment_report');
    Route::get('/discard_reports/{equipment}', 'EquipmentController@discardReports')->name('equipment_report_discard');
});

Route::prefix('workouts')->middleware(['verified', 'isPremium'])->group(function() {
    Route::get('/', 'WorkoutController@index')->name('workout_listing');
    Route::get('/my_workouts', 'WorkoutController@myWorkouts')->name('workout_listing_user');
    Route::get('/view/{id}/{tab?}', 'WorkoutController@view')->name('workout_view');
    Route::get('/create', 'WorkoutController@create')->name('workout_create');
    Route::post('/create', 'WorkoutController@store')->name('workout_store');
    Route::get('/edit/{id}', 'WorkoutController@edit')->name('workout_edit');
    Route::post('/edit/{id}', 'WorkoutController@update')->name('workout_update');
    Route::get('/delete/{id}', 'WorkoutController@delete')->name('workout_delete');
    Route::get('/bookmarks', 'WorkoutController@bookmarks')->name('workout_bookmark_listing');
    Route::get('/bookmark/{id}', 'WorkoutController@bookmark')->name('workout_bookmark');
    Route::get('/unbookmark/{id}', 'WorkoutController@unbookmark')->name('workout_unbookmark');
    Route::get('/getMovementFields', 'WorkoutController@getMovementFields')->name('movement_fields_search');
    Route::get('/deleteMovement/{id}', 'WorkoutController@deleteMovement')->name('workout_movement_delete');
    Route::prefix('recorded')->group(function() {
        Route::get('/', 'RecordedWorkoutController@index')->name('recorded_workout_listing');
        Route::get('/view/{id}', 'RecordedWorkoutController@view')->name('recorded_workout_view');
        Route::get('/create/{id}', 'RecordedWorkoutController@create')->name('recorded_workout_create');
        Route::post('/create/{id}', 'RecordedWorkoutController@store')->name('recorded_workout_store');
        Route::get('/edit/{id}', 'RecordedWorkoutController@edit')->name('recorded_workout_edit');
        Route::post('/edit/{id}', 'RecordedWorkoutController@update')->name('recorded_workout_update');
        Route::get('/delete/{id}', 'RecordedWorkoutController@delete')->name('recorded_workout_delete');
    });
    Route::prefix('plan')->group(function() {
        Route::get('/', 'WorkoutPlanController@index')->name('workout_plan');
        Route::post('/', 'WorkoutPlanController@addWorkout')->name('add_workout_to_plan');
        Route::get('/remove_workout/{id}', 'WorkoutPlanController@removeWorkout')->name('workout_plan_remove_workout');
    });
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
