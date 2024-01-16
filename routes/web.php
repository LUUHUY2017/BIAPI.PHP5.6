<?php
use App\User;
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

// Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/404Page', function () {
	echo "Error 401 - Unauthorized";
})->name('login');
Route::get('/document', 'APIDocumentController@index');
Route::get('/document/pocGetTraffic', 'APIDocumentController@pocGetTraffic');
Route::get('/document/pocGetEmailTemplate', 'APIDocumentController@pocGetEmailTemplate');