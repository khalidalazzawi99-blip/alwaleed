<?php

namespace App\Http\Controllers;

use App\Models\FeatureModuleRecord;
use Illuminate\Http\Request;

class FeatureModuleController extends Controller
{
    private const MODULES = ['inventory', 'sales', 'purchases', 'payroll', 'projects', 'installments'];

    public function index(string $module)
    {
        $this->ensureModule($module);
        $records = FeatureModuleRecord::where('company_id', auth()->user()->company_id)
            ->where('module', $module)->latest('record_date')->latest('id')->get();

        return view('feature_modules.index', compact('module', 'records'));
    }

    public function store(Request $request, string $module)
    {
        $this->ensureModule($module);
        $data = $request->validate($this->rules($module));
        if ($module !== 'installments') {
            $data['paid_amount'] = 0;
        }
        FeatureModuleRecord::create($data + ['company_id' => auth()->user()->company_id, 'module' => $module]);
        return back()->with('success', 'تمت إضافة السجل بنجاح');
    }

    public function update(Request $request, FeatureModuleRecord $record, string $module)
    {
        $this->ensureOwned($module, $record);
        $data = $request->validate($this->rules($module));
        if ($module !== 'installments') {
            $data['paid_amount'] = 0;
        }
        $record->update($data);
        return back()->with('success', 'تم تحديث السجل بنجاح');
    }

    private function rules(string $module): array
    {
        return [
            'reference' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'record_date' => ['required', 'date'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount' => $module === 'installments'
                ? ['nullable', 'numeric', 'min:0', 'lte:amount']
                : ['nullable'],
            'status' => ['required', 'in:active,pending,completed,cancelled'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function destroy(FeatureModuleRecord $record, string $module)
    {
        $this->ensureOwned($module, $record);
        $record->delete();
        return back()->with('success', 'تم حذف السجل');
    }

    private function ensureModule(string $module): void
    {
        abort_unless(in_array($module, self::MODULES, true), 404);
    }

    private function ensureOwned(string $module, FeatureModuleRecord $record): void
    {
        $this->ensureModule($module);
        abort_unless($record->company_id === auth()->user()->company_id && $record->module === $module, 404);
    }
}
