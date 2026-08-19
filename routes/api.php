<?php
use App\Http\Controllers\Api\ExternalInvoiceApiController;
use Illuminate\Support\Facades\Route;
Route::prefix('v1')->middleware('throttle:external-api')->group(function(){
    Route::get('/external-invoices',[ExternalInvoiceApiController::class,'index'])->middleware('company.token:invoices:read');
    Route::post('/external-invoices',[ExternalInvoiceApiController::class,'store'])->middleware('company.token:invoices:write');
    Route::get('/customers/{externalCustomerId}/balance',[ExternalInvoiceApiController::class,'balance'])->middleware('company.token:balances:read');
});
