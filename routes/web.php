<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\GuardController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resource('clients', ClientController::class)->except(['show']);
    Route::resource('guards', GuardController::class)->except(['show']);
    Route::resource('deployments', DeploymentController::class)->except(['show']);

    Route::get('user-roles', [UserRoleController::class, 'index'])->name('user-roles.index');
    Route::patch('user-roles/{user}', [UserRoleController::class, 'update'])->name('user-roles.update');
});

require __DIR__.'/settings.php';
