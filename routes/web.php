<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CashboxController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);

        Route::get('/backup', [BackupController::class, 'download']);

        Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    });

    Route::middleware(['role:admin,accountant'])->group(function () {
        Route::get('/cashbox', [CashboxController::class, 'index']);

        Route::get('/reports', [ReportController::class, 'index']);
        Route::get('/reports/print', [ReportController::class, 'print']);

        Route::get('/receipts', [ReceiptController::class, 'index']);
        Route::post('/receipts', [ReceiptController::class, 'store']);
        Route::get('/receipts/{receipt}/edit', [ReceiptController::class, 'edit']);
        Route::put('/receipts/{receipt}', [ReceiptController::class, 'update']);
        Route::delete('/receipts/{receipt}', [ReceiptController::class, 'destroy']);
        Route::get('/receipts/{id}/print', [ReceiptController::class, 'print']);

        Route::get('/payments', [PaymentController::class, 'index']);
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit']);
        Route::put('/payments/{payment}', [PaymentController::class, 'update']);
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy']);
        Route::get('/payments/{id}/print', [PaymentController::class, 'print']);
    });

    Route::middleware(['role:admin,data_entry'])->group(function () {
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::post('/customers', [CustomerController::class, 'store']);
        Route::get('/customers/{customer}', [CustomerController::class, 'show']);
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy']);
        Route::get('/customers/{customer}/print', [CustomerController::class, 'print']);

        Route::get('/suppliers', [SupplierController::class, 'index']);
        Route::post('/suppliers', [SupplierController::class, 'store']);
        Route::get('/suppliers/{supplier}', [SupplierController::class, 'show']);
        Route::get('/suppliers/{supplier}/print', [SupplierController::class, 'print']);
    });

});