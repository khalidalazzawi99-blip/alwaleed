<?php
use App\Http\Controllers\Api\ExternalInvoiceApiController;
use App\Http\Controllers\Api\ExternalDataApiController;
use Illuminate\Support\Facades\Route;
Route::prefix('v1')->middleware('throttle:external-api')->group(function(){
    Route::get('/external-invoices',[ExternalInvoiceApiController::class,'index'])->middleware('company.token:invoices:read');
    Route::post('/external-invoices',[ExternalInvoiceApiController::class,'store'])->middleware('company.token:invoices:write');
    Route::post('/external-invoices/{externalInvoiceId}/cancel',[ExternalInvoiceApiController::class,'cancel'])->middleware('company.token:invoices:write');
    Route::get('/external-customers',[ExternalDataApiController::class,'customers'])->middleware('company.token:customers:read');
    Route::get('/external-banks',[ExternalDataApiController::class,'banks'])->middleware('company.token:banks:read');
    Route::get('/customers/{customerId}/balance',[ExternalInvoiceApiController::class,'balance'])->middleware('company.token:balances:read');
});
