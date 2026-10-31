<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\KudiaFactoryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'subscription', 'role:admin,accountant,data_entry', 'feature:kudia_factory'])
    ->prefix('kudia')->name('kudia.')->group(function () {
        Route::get('/', [KudiaFactoryController::class, 'index'])->name('index');
        Route::get('/customers', [KudiaFactoryController::class, 'customers'])->name('customers.index');
        Route::post('/customers', [KudiaFactoryController::class, 'storeCustomer'])->name('customers.store');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
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

        Route::middleware('role:admin,accountant')->group(function () {
            foreach (['receipts' => \App\Http\Controllers\ReceiptController::class, 'payments' => \App\Http\Controllers\PaymentController::class] as $resource => $controller) {
                $parameter = $resource === 'receipts' ? 'receipt' : 'payment';
                Route::get('/'.$resource, [$controller, 'index'])->name($resource.'.index');
                Route::post('/'.$resource, [$controller, 'store'])->name($resource.'.store');
                Route::get('/'.$resource.'/{'.$parameter.'}/edit', [$controller, 'edit'])->name($resource.'.edit');
                Route::put('/'.$resource.'/{'.$parameter.'}', [$controller, 'update'])->name($resource.'.update');
                Route::delete('/'.$resource.'/{'.$parameter.'}', [$controller, 'destroy'])->name($resource.'.destroy');
                Route::get('/'.$resource.'/{id}/print', [$controller, 'print'])->name($resource.'.print');
                Route::get('/'.$resource.'/{id}/pdf', [$controller, 'pdf'])->name($resource.'.pdf');
                Route::get('/'.$resource.'/{id}/excel', [$controller, 'excel'])->name($resource.'.excel');
            }
        });

        Route::middleware('role:admin,data_entry')->group(function () {
            $controller = \App\Http\Controllers\PartyDebtTransactionController::class;
            Route::post('/party-debt-transactions', [$controller, 'store'])->name('party-debt-transactions.store');
            Route::get('/party-debt-transactions/{transaction}/edit', [$controller, 'edit'])->name('party-debt-transactions.edit');
            Route::put('/party-debt-transactions/{transaction}', [$controller, 'update'])->name('party-debt-transactions.update');
            Route::delete('/party-debt-transactions/{transaction}', [$controller, 'destroy'])->name('party-debt-transactions.destroy');
        });
    });
