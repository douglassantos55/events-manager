<?php

use App\Http\Controllers\AssigneeController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MemberController;
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

Route::controller(AssigneeController::class)->middleware('auth')->group(function () {
    Route::post('/assignees/{event}/add', 'add')->name('assignees.add');
    Route::delete('/assignees/{event}/{assignee}', 'remove')->name('assignees.remove')->scopeBindings();
});

Route::controller(RoleController::class)->middleware('auth')->group(function () {
    Route::get('/roles', 'index')->name('roles.index');
    Route::get('/roles/new', 'create')->name('roles.create');
    Route::post('/roles/store', 'store')->name('roles.store');
    Route::get('/roles/edit/{role}', 'edit')->name('roles.edit');
    Route::post('/roles/update/{role}', 'update')->name('roles.update');
    Route::get('/roles/delete/{role}', 'destroy')->name('roles.destroy');
});

Route::controller(MemberController::class)->group(function () {
    Route::middleware('auth')->group(function () {
        Route::get('/members', 'index')->name('members.index');
        Route::get('/members/invite', 'invite')->name('members.invite');
        Route::post('/members/store', 'store')->name('members.store');

        Route::get('/members/edit/{member}', 'edit')->name('members.edit');
        Route::post('/members/update/{member}', 'update')->name('members.update');
        Route::get('/members/delete/{member}', 'destroy')->name('members.destroy');
    });

    Route::get('/members/join/{member}', 'join')->name('members.join');
    Route::post('/members/save/{member}', 'save')->name('members.save');
});
