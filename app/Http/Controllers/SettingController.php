<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

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
            'currency' => ['nullable', 'string', 'max:20'],
            'company_logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = [
            'company_id' => $companyId,
            'company_name' => $request->company_name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'currency' => $request->currency,
        ];

        if ($request->hasFile('company_logo')) {

            $logoPath = $request
                ->file('company_logo')
                ->store('logos', 'public');

            $data['company_logo'] = $logoPath;
        }

        Setting::updateOrCreate(
            [
                'company_id' => $companyId,
            ],
            $data
        );

        return redirect('/settings')
            ->with('success', __('تم تحديث إعدادات الشركة بنجاح'));
    }
}
