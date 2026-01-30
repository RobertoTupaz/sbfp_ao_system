<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('edit_beneficiaries', 'edit_beneficiaries')
    ->middleware(['auth', 'verified'])
    ->name('edit_beneficiaries');

Route::view('generate_forms', 'generate_forms')
    ->middleware(['auth', 'verified'])
    ->name('generate_forms');

Route::view('track/enrollees', 'trackEnrollees')
    ->middleware(['auth', 'verified'])
    ->name('track_enrollees');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
