<?php

use App\Http\Controllers\DailyAccountsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'subscription'])->prefix('sippar/daily-accounts')->name('sippar.daily-accounts.')->group(function () {
    Route::get('/', [DailyAccountsController::class, 'index'])->middleware('can:sippar.daily_accounts.view')->name('index');
    Route::post('/', [DailyAccountsController::class, 'store'])->middleware('can:sippar.daily_accounts.create')->name('store');
    Route::post('/parties', [DailyAccountsController::class, 'storeParty'])->middleware('can:sippar.daily_accounts.create')->name('parties.store');
    Route::get('/excel', [DailyAccountsController::class, 'excel'])->middleware('can:sippar.daily_accounts.export')->name('excel');
    Route::get('/pdf', [DailyAccountsController::class, 'pdf'])->middleware('can:sippar.daily_accounts.export')->name('pdf');
    Route::get('/{expense}/edit', [DailyAccountsController::class, 'edit'])->whereNumber('expense')->middleware('can:sippar.daily_accounts.update')->name('edit');
    Route::put('/{expense}', [DailyAccountsController::class, 'update'])->whereNumber('expense')->middleware('can:sippar.daily_accounts.update')->name('update');
    Route::delete('/{expense}', [DailyAccountsController::class, 'destroy'])->whereNumber('expense')->middleware('can:sippar.daily_accounts.delete')->name('destroy');
});
