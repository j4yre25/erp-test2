<?php

use App\Http\Controllers\AccountReceivableController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\GeneralManagerApprovalController;
use App\Http\Controllers\GuardController;
use App\Http\Controllers\PayrollPeriodController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    Route::resource('clients', ClientController::class)->except(['show']);
    Route::resource('guards', GuardController::class)->except(['show']);
    Route::resource('deployments', DeploymentController::class)->except(['show']);

    Route::resource('payroll-periods', PayrollPeriodController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('payroll-periods/{payroll_period}/close', [PayrollPeriodController::class, 'close'])->name('payroll-periods.close');

    Route::resource('statement-of-accounts', AccountReceivableController::class)->parameters([
        'statement-of-accounts' => 'account_receivable',
    ]);
    Route::post('statement-of-accounts/{account_receivable}/submit', [AccountReceivableController::class, 'submit'])->name('statement-of-accounts.submit');

    Route::prefix('gm')->name('gm.')->group(function () {
        Route::get('statement-of-accounts', [GeneralManagerApprovalController::class, 'index'])->name('statement-of-accounts.index');
        Route::get('statement-of-accounts/{account_receivable}', [GeneralManagerApprovalController::class, 'show'])->name('statement-of-accounts.show');
        Route::post('statement-of-accounts/{account_receivable}/approve', [GeneralManagerApprovalController::class, 'approve'])->name('statement-of-accounts.approve');
        Route::post('statement-of-accounts/{account_receivable}/reject', [GeneralManagerApprovalController::class, 'reject'])->name('statement-of-accounts.reject');
    });

    Route::get('user-roles', [UserRoleController::class, 'index'])->name('user-roles.index');
    Route::patch('user-roles/{user}', [UserRoleController::class, 'update'])->name('user-roles.update');
});

require __DIR__.'/settings.php';
