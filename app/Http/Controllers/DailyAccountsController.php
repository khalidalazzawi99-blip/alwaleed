<?php

namespace App\Http\Controllers;

use App\Exports\DailyAccountsExcelExport;
use App\Http\Requests\DailyAccountsFilterRequest;
use App\Http\Requests\DailyExpenseRequest;
use App\Services\DailyAccountsPdf;
use App\Services\DailyAccountsService;
use App\Models\Cashbox;
use App\Models\CashboxLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class DailyAccountsController extends Controller
{
    public function index(DailyAccountsFilterRequest $request)
    {
        $service = new DailyAccountsService($request->user(), $request->filters());
        $report = $service->report();

        return view('daily_accounts.index', $report + [
            'expenses' => $service->query()->with(['party', 'cashbox'])->orderByDesc('expense_date')->orderByDesc('id')->paginate(20)->withQueryString(),
            'parties' => $service->company->dailyExpenseParties()->orderBy('name')->get(),
            'cashboxes' => Cashbox::operational()->where('company_id', $service->company->id)->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(DailyExpenseRequest $request)
    {
        $service = new DailyAccountsService($request->user());
        DB::transaction(function () use ($service, $request) {
            $expense = $service->company->dailyExpenses()->make($request->validated());
            $expense->created_by = $request->user()->id;
            $cashbox = Cashbox::operational()->whereKey($request->validated('cashbox_id'))->where('company_id', $service->company->id)->lockForUpdate()->firstOrFail();
            if ((float) $expense->amount > (float) $cashbox->balance) {
                throw ValidationException::withMessages(['amount' => 'مبلغ المصروف أكبر من رصيد الحساب المالي المحدد.']);
            }
            $expense->save();
            $cashbox->decrement('balance', $expense->amount);
            $cashbox->refresh();
            CashboxLog::create(['company_id' => $service->company->id, 'cashbox_id' => $cashbox->id,
                'type' => 'مصروف يومي', 'reference_no' => 'DAILY-'.$expense->id,
                'person_name' => $expense->party->name, 'amount' => $expense->amount,
                'balance_after' => $cashbox->balance, 'notes' => $expense->notes]);
        });

        return redirect()->route('sippar.daily-accounts.index', [
            'year' => substr($request->validated('expense_date'), 0, 4), 'currency' => $request->validated('currency'),
        ])->with('success', 'تمت إضافة المصروف بنجاح');
    }

    public function edit(Request $request, int $expense)
    {
        $service = new DailyAccountsService($request->user());

        return view('daily_accounts.edit', [
            'expense' => $service->record($expense),
            'parties' => $service->company->dailyExpenseParties()->where('is_active', true)->orderBy('name')->get(),
            'cashboxes' => Cashbox::operational()->where('company_id', $service->company->id)->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(DailyExpenseRequest $request, int $expense)
    {
        $service = new DailyAccountsService($request->user());
        DB::transaction(function () use ($service, $request, $expense) {
            $record = $service->record($expense);
            $accounts = Cashbox::withTrashed()->whereIn('id', array_unique([$record->cashbox_id, $request->validated('cashbox_id')]))
                ->where('company_id', $service->company->id)->lockForUpdate()->get()->keyBy('id');
            $old = $accounts->get($record->cashbox_id);
            $new = $accounts->get($request->validated('cashbox_id'));
            $available = (float) $new->balance + ($old?->id === $new->id ? (float) $record->amount : 0);
            if ((float) $request->validated('amount') > $available) {
                throw ValidationException::withMessages(['amount' => 'مبلغ المصروف أكبر من رصيد الحساب المالي المحدد.']);
            }
            $old?->increment('balance', $record->amount);
            $new->decrement('balance', $request->validated('amount'));
            $record->update($request->validated());
        });

        return redirect()->route('sippar.daily-accounts.index', [
            'year' => substr($request->validated('expense_date'), 0, 4), 'currency' => $request->validated('currency'),
        ])->with('success', 'تم تحديث المصروف بنجاح');
    }

    public function destroy(Request $request, int $expense)
    {
        $service = new DailyAccountsService($request->user());
        DB::transaction(function () use ($service, $expense) {
            $record = $service->record($expense);
            Cashbox::withTrashed()->whereKey($record->cashbox_id)->lockForUpdate()->first()?->increment('balance', $record->amount);
            $record->delete();
        });

        return redirect()->route('sippar.daily-accounts.index')->with('success', 'تم حذف المصروف بنجاح');
    }

    public function storeParty(Request $request)
    {
        $service = new DailyAccountsService($request->user());
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('daily_expense_parties')->where('company_id', $service->company->id)],
            'company_id' => ['prohibited'],
        ]);
        $service->company->dailyExpenseParties()->create($data);

        return redirect()->route('sippar.daily-accounts.index')->with('success', 'تمت إضافة الجهة بنجاح');
    }

    public function excel(DailyAccountsFilterRequest $request)
    {
        $service = new DailyAccountsService($request->user(), $request->filters());

        return Excel::download(new DailyAccountsExcelExport($service), $service->filename('xlsx'));
    }

    public function pdf(DailyAccountsFilterRequest $request, DailyAccountsPdf $pdf)
    {
        return $pdf->download(new DailyAccountsService($request->user(), $request->filters()));
    }
}
