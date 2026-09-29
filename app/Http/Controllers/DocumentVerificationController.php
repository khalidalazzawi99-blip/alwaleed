<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Setting;

class DocumentVerificationController extends Controller
{
    public function show(string $token)
    {
        $document = Receipt::with(['customer', 'supplier'])
            ->where('verification_token', $token)->first();
        $type = 'receipt';

        if (! $document) {
            $document = Payment::with(['customer', 'supplier'])
                ->where('verification_token', $token)->first();
            $type = 'payment';
        }

        if (! $document) {
            return response()->view('documents.verify', ['valid' => false], 404);
        }

        $currency = Setting::where('company_id', $document->company_id)->value('currency') ?: 'IQD';
        $companyName = Company::whereKey($document->company_id)->value('name');
        $brand = app(\App\Services\CompanyBrandService::class)->profile($document->company_id);

        return view('documents.verify', compact('document', 'type', 'currency', 'brand', 'companyName') + ['valid' => true]);
    }
}
