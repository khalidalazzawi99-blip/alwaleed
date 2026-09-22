<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class SettingController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | عرض إعدادات الشركة الحالية
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        if (
            !auth()->check() ||
            !in_array(auth()->user()->role, ['admin', 'super_admin'])
        ) {
            abort(403);
        }

        /*
        | Super Admin ما عنده company_id
        | فهنا نخليه يتعامل فقط إذا كان مربوط بشركة
        */
        $companyId = auth()->user()->company_id;

        if (!$companyId) {
            return view('settings.index', [
                'setting' => null,
            ]);
        }

        $setting = Setting::where('company_id', $companyId)
            ->first();

        return view('settings.index', [
            'setting' => $setting,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | تحديث إعدادات الشركة الحالية
    |--------------------------------------------------------------------------
    */
    public function update(Request $request)
    {
        if (
            !auth()->check() ||
            !in_array(auth()->user()->role, ['admin', 'super_admin'])
        ) {
            abort(403);
        }

        $companyId = auth()->user()->company_id;

        if (!$companyId) {
            abort(403, __('هذا الحساب غير مرتبط بشركة.'));
        }

        $request->validate([
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'currency' => ['required', 'in:IQD,USD'],
            'company_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $company = auth()->user()->company;
        $setting = Setting::where('company_id', $companyId)->first();
        $oldLogoPath = $setting?->company_logo ?: $company?->logo;

        $data = [
            'company_id' => $companyId,
            'company_name' => $request->company_name ?: $company?->name ?: 'Company',
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'currency' => $request->currency,
        ];

        $newLogoPath = null;

        if ($request->hasFile('company_logo')) {
            $newLogoPath = $request->file('company_logo')->store('logos', 'public');

            if (! $newLogoPath || ! Storage::disk('public')->exists($newLogoPath)) {
                throw ValidationException::withMessages([
                    'company_logo' => __('تعذر حفظ الشعار. يرجى المحاولة مرة أخرى.'),
                ]);
            }

            $data['company_logo'] = $newLogoPath;
        }

        try {
            DB::transaction(function () use ($companyId, $data, $newLogoPath, $company): void {
                Setting::updateOrCreate(['company_id' => $companyId], $data);

                if ($newLogoPath && $company) {
                    $company->update(['logo' => $newLogoPath]);
                }
            });
        } catch (Throwable $exception) {
            if ($newLogoPath) {
                Storage::disk('public')->delete($newLogoPath);
            }

            throw $exception;
        }

        if ($newLogoPath && $oldLogoPath && $oldLogoPath !== $newLogoPath) {
            Storage::disk('public')->delete($oldLogoPath);
        }

        return redirect('/settings')
            ->with('success', __('تم تحديث إعدادات الشركة بنجاح'));
    }
}
