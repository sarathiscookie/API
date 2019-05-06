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
    return view('auth.login');
});

Auth::routes([
	'register' => false, //Disable Registration
	'reset' => false //Disable Reset Password
]);

//Route::get('/home', 'HomeController@index')->name('home');

/*
|--------------------------------------------------------------------------
| Web Routes for admin
|--------------------------------------------------------------------------
|
| Here is the web routes for admin.
|
*/
Route::prefix('admin')->group(function(){
	Route::group(['middleware' => ['auth', 'Admin']], function () {
		//Admin dashboard
		Route::get('dashboard', 'Admin\DashboardController@index');
	});
});

/*
|--------------------------------------------------------------------------
| Web Routes for manager
|--------------------------------------------------------------------------
|
| Here is the web routes for manager.
|
*/
Route::prefix('manager')->group(function(){
	Route::group(['middleware' => ['auth', 'Manager']], function () {
		//Manager dashboard
		Route::get('dashboard', 'Manager\DashboardController@index');
	});
});

/*
|--------------------------------------------------------------------------
| Web Routes for employee
|--------------------------------------------------------------------------
|
| Here is the web routes for employee.
|
*/
Route::prefix('employee')->group(function(){
	Route::group(['middleware' => ['auth', 'Employee']], function () {
		//Employee dashboard
		Route::get('dashboard', 'Employee\DashboardController@index');
	});
});
