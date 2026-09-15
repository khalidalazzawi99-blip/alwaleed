<?php

namespace App\Http\Requests;

use App\Services\SipparCompany;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DailyAccountsFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('sippar.daily_accounts.view') ?? false;
    }

    public function rules(): array
    {
        $company = app(SipparCompany::class)->forUser($this->user());

        return [
            'year' => ['nullable', 'integer', 'between:1900,9998'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => array_merge(['nullable', 'date_format:Y-m-d'], $this->filled('from') ? ['after_or_equal:from'] : []),
            'party_id' => ['nullable', 'integer', Rule::exists('daily_expense_parties', 'id')->where('company_id', $company->id)],
            'currency' => ['nullable', Rule::in(config('daily_accounts.currencies'))],
            'notes' => ['nullable', 'string', 'max:200'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => array_merge(['nullable', 'numeric', 'min:0'], $this->filled('min_amount') ? ['gte:min_amount'] : []),
            'company_id' => ['prohibited'],
        ];
    }

    public function filters(): array
    {
        $filters = array_filter($this->validated(), fn ($value) => $value !== null && $value !== '');
        unset($filters['currency']);

        return array_replace(['year' => (int) now()->year], $filters);
    }
}
