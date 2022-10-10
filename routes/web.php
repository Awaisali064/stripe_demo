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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


Route::get('/subscribe/{type}', 'StripePaymentController@subscribe')->name('subscribe');
Route::get('/success/{subscreption_type}/{session_id}', 'StripePaymentController@stripe_success')->name('success');
Route::get('/cancel', 'StripePaymentController@cancel')->name('cancel');
Route::get('/onboard_account', 'StripePaymentController@onboard_account')->name('onboard_account');
Route::get('/complete_onboard', 'StripePaymentController@complete_onboard')->name('complete_onboard');
Route::get('/make_payment', 'StripePaymentController@make_payment')->name('make_payment');
Route::get('/payment_success', 'StripePaymentController@payment_success')->name('payment_success');
Route::get('/payment_failure', 'StripePaymentController@payment_failure')->name('payment_failure');
Route::get('/subscreption', 'StripePaymentController@subscreption')->name('subscreption');





Route::get('/stripe_customer_portal', 'StripePaymentController@stripe_customer_portal');
Route::POST('/stripe_webhook', 'StripePaymentController@stripe_webhook');


// Route::get('/subscribe_anual', 'StripePaymentController@subscribe_anual')->name('subscribe_anual');
