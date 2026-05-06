<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Database\Seeders\NutritionalStatusSeeder;
use Livewire\Volt\Volt;

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

Volt::route('/', 'pages.auth.login');

Route::view('/dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('/edit_beneficiaries', 'edit_beneficiaries')
    ->middleware(['auth', 'verified'])
    ->name('edit_beneficiaries');

Route::view('/generate_forms', 'generate_forms')
    ->middleware(['auth', 'verified'])
    ->name('generate_forms');

Route::view('/track/enrollees', 'trackEnrollees')
    ->middleware(['auth', 'verified'])
    ->name('track_enrollees');

Route::view('/profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('/generate/reports', 'generateReports')
    ->middleware(['auth'])
    ->name('generate_reports');

Route::get('/run-nutritional-seeder', function () {
    if (!app()->environment('local')) {
        abort(403);
    }

    Artisan::call('db:seed', [
        '--class' => NutritionalStatusSeeder::class,
        '--force' => true,
    ]);

    return response(Artisan::output());
})->name('run.nutritional.seeder');

require __DIR__ . '/auth.php';
