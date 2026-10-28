<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\KudiaFactoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'subscription', 'role:admin,accountant,data_entry', 'feature:kudia_factory'])
    ->prefix('kudia')->name('kudia.')->group(function () {
        Route::get('/', [KudiaFactoryController::class, 'index'])->name('index');
        Route::get('/customers', [KudiaFactoryController::class, 'customers'])->name('customers.index');
        Route::post('/customers', [KudiaFactoryController::class, 'storeCustomer'])->name('customers.store');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        Route::get('/customers/{customer}/print', [CustomerController::class, 'print'])->name('customers.print');
        Route::get('/customers/{customer}/pdf', [CustomerController::class, 'pdf'])->name('customers.pdf');
        Route::get('/customers/{customer}/excel', [CustomerController::class, 'excel'])->name('customers.excel');
        Route::get('/invoices/create', [KudiaFactoryController::class, 'create'])->name('invoices.create');
        Route::post('/invoices', [KudiaFactoryController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice}', [KudiaFactoryController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/edit', [KudiaFactoryController::class, 'edit'])->name('invoices.edit');
        Route::put('/invoices/{invoice}', [KudiaFactoryController::class, 'update'])->name('invoices.update');
        Route::delete('/invoices/{invoice}', [KudiaFactoryController::class, 'destroy'])->name('invoices.destroy');
        Route::get('/invoices/{invoice}/print', [KudiaFactoryController::class, 'print'])->name('invoices.print');
        Route::get('/invoices/{invoice}/pdf', [KudiaFactoryController::class, 'pdf'])->name('invoices.pdf');
    });
