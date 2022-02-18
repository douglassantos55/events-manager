<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\RoleController;
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

Route::controller(LoginController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::get('/logout', 'logout')->name('logout');
    Route::post('/login', 'authenticate')->name('authenticate');
});

Route::controller(EventController::class)->middleware('auth')->group(function () {
    Route::get('/events', 'index')->name('events.index');
    Route::get('/events/new', 'create')->name('events.create');
    Route::post('/events/save', 'store')->name('events.store');
    Route::get('/events/{event}', 'view')->name('events.view');
});

Route::controller(RoleController::class)->middleware('auth')->group(function () {
    Route::get('/roles', 'index')->name('roles.index');
    Route::get('/roles/new', 'create')->name('roles.create');
    Route::post('/roles/save/{role?}', 'save')->name('roles.save');
    Route::get('/roles/delete/{role}', 'destroy')->name('roles.destroy');
    Route::get('/roles/edit/{role}', 'edit')->name('roles.edit');
});
