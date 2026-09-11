<?php

namespace App\Http\Requests;

use App\Services\SipparCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DailyExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('sippar.daily_accounts.'.($this->isMethod('POST') ? 'create' : 'update')) ?? false;
    }

    public function rules(): array
    {
        $company = app(SipparCompany::class)->forUser($this->user());

        return [
            'expense_date' => ['required', 'date_format:Y-m-d'],
            'amount' => ['required', 'numeric', 'gt:0', 'max:9999999999999999.99', 'decimal:0,2'],
            'currency' => ['required', Rule::in(config('daily_accounts.currencies'))],
            'party_id' => ['required', 'integer', Rule::exists('daily_expense_parties', 'id')->where('company_id', $company->id)->where('is_active', true)],
            'notes' => ['nullable', 'string', 'max:5000'],
            'company_id' => ['prohibited'], 'created_by' => ['prohibited'],
            'day' => ['prohibited'], 'month' => ['prohibited'],
        ];
    }
}
