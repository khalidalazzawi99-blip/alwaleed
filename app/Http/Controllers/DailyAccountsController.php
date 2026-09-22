<?php

namespace App\Http\Controllers;

use App\Exports\DailyAccountsExcelExport;
use App\Http\Requests\DailyAccountsFilterRequest;
use App\Http\Requests\DailyExpenseRequest;
use App\Services\DailyAccountsPdf;
use App\Services\DailyAccountsService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class DailyAccountsController extends Controller
{
    public function index(DailyAccountsFilterRequest $request)
    {
        $service = new DailyAccountsService($request->user(), $request->filters());
        $report = $service->report();

        return view('daily_accounts.index', $report + [
            'expenses' => $service->query()->with('party')->orderByDesc('expense_date')->orderByDesc('id')->paginate(20)->withQueryString(),
            'parties' => $service->company->dailyExpenseParties()->orderBy('name')->get(),
        ]);
    }

    public function store(DailyExpenseRequest $request)
    {
        $service = new DailyAccountsService($request->user());
        $expense = $service->company->dailyExpenses()->make($request->validated());
        $expense->created_by = $request->user()->id;
        $expense->save();

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
        ]);
    }

    public function update(DailyExpenseRequest $request, int $expense)
    {
        $service = new DailyAccountsService($request->user());
        $service->record($expense)->update($request->validated());

        return redirect()->route('sippar.daily-accounts.index', [
            'year' => substr($request->validated('expense_date'), 0, 4), 'currency' => $request->validated('currency'),
        ])->with('success', 'تم تحديث المصروف بنجاح');
    }

    public function destroy(Request $request, int $expense)
    {
        $service = new DailyAccountsService($request->user());
        $service->record($expense)->delete();

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
