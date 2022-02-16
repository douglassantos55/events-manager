<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\LoginController;
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
    return inertia('Welcome');
})->middleware('auth')->name('dashboard');

Route::controller(EventController::class)->group(function () {
    Route::get('/events', 'index')->middleware('auth')->name('events.index');
    Route::get('/events/new', 'create')->middleware('auth')->name('events.create');
});

Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::get('/logout', 'logout')->name('logout');
    Route::post('/login', 'authenticate')->name('authenticate');
});
