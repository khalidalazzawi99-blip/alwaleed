<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\CashboxController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ExternalInvoiceController;
use App\Http\Controllers\FeatureModuleController;
use App\Http\Controllers\VoucherAttachmentController;


/*
|--------------------------------------------------------------------------
| الصفحة الرئيسية
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect('/login');
});


/*
|--------------------------------------------------------------------------
| تسجيل الدخول والخروج
|--------------------------------------------------------------------------
*/

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout']);


/*
|--------------------------------------------------------------------------
| تغيير اللغة
|--------------------------------------------------------------------------
|
| ar = العربية
| en = English
|
*/

Route::get('/language/{locale}', function (string $locale) {

    if (!in_array($locale, ['ar', 'en'], true)) {
        abort(404);
    }

    session([
        'locale' => $locale,
    ]);

    return back();

})->name('language.switch');


/*
|--------------------------------------------------------------------------
| مالك النظام - Super Admin
|--------------------------------------------------------------------------
|
| لوحة المالك وإدارة الشركات ما تمر على subscription
| لأن مالك النظام ما عنده company_id ولا اشتراك شركة.
|
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | لوحة مالك النظام
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin/dashboard',
        [DashboardController::class, 'superAdmin']
    );


    /*
    |--------------------------------------------------------------------------
    | إدارة الشركات
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/admin/companies',
        [CompanyController::class, 'index']
    );

    Route::get(
        '/admin/companies/create',
        [CompanyController::class, 'create']
    );

    Route::post(
        '/admin/companies',
        [CompanyController::class, 'store']
    );

    Route::get(
        '/admin/companies/{company}',
        [CompanyController::class, 'show']
    );

    Route::get(
        '/admin/companies/{company}/edit',
        [CompanyController::class, 'edit']
    );

    Route::put(
        '/admin/companies/{company}',
        [CompanyController::class, 'update']
    );

    Route::patch(
        '/admin/companies/{company}/features',
        [CompanyController::class, 'updateFeature']
    )->name('admin.companies.features.update');

    Route::delete(
        '/admin/companies/{company}',
        [CompanyController::class, 'destroy']
    );


    /*
    |--------------------------------------------------------------------------
    | الاشتراكات
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/admin/companies/{company}/renew',
        [CompanyController::class, 'renew']
    );

    Route::post(
        '/admin/companies/{company}/activate',
        [CompanyController::class, 'activate']
    );

    Route::post(
        '/admin/companies/{company}/deactivate',
        [CompanyController::class, 'deactivate']
    );


    /*
    |--------------------------------------------------------------------------
    | النسخة الاحتياطية
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/backup',
        [BackupController::class, 'download']
    );
});


/*
|--------------------------------------------------------------------------
| صفحات الشركات
|--------------------------------------------------------------------------
|
| أي مستخدم تابع لشركة لازم:
| 1. يكون مسجل دخول
| 2. اشتراك شركته يكون فعال
|
*/

Route::middleware(['auth', 'subscription'])->group(function () {

    foreach (['inventory', 'sales', 'purchases', 'payroll', 'projects', 'installments'] as $module) {
        Route::middleware(['role:admin,accountant', 'feature:'.$module])->prefix('modules/'.$module)->group(function () use ($module) {
            Route::get('/', [FeatureModuleController::class, 'index'])->defaults('module', $module)->name('modules.'.$module);
            Route::post('/', [FeatureModuleController::class, 'store'])->defaults('module', $module);
            Route::put('/{record}', [FeatureModuleController::class, 'update'])->defaults('module', $module);
            Route::delete('/{record}', [FeatureModuleController::class, 'destroy'])->defaults('module', $module);
        });
    }

    Route::middleware(['role:admin,accountant', 'feature:voucher_attachments'])->prefix('voucher-attachments')->group(function () {
        Route::get('/', [VoucherAttachmentController::class, 'index'])->name('voucher-attachments.index');
        Route::post('/', [VoucherAttachmentController::class, 'store']);
        Route::get('/{attachment}/download', [VoucherAttachmentController::class, 'download'])->name('voucher-attachments.download');
        Route::delete('/{attachment}', [VoucherAttachmentController::class, 'destroy']);
    });

    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    /*
    |--------------------------------------------------------------------------
    | Dashboard الشركة
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/dashboard',
        [DashboardController::class, 'index']
    );


    /*
    |--------------------------------------------------------------------------
    | إدارة المستخدمين والإعدادات
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:super_admin,admin'])->group(function () {

        Route::get(
            '/users',
            [UserController::class, 'index']
        );

        Route::post(
            '/users',
            [UserController::class, 'store']
        );

        Route::put(
            '/users/{user}',
            [UserController::class, 'update']
        );

        Route::delete(
            '/users/{user}',
            [UserController::class, 'destroy']
        );


        /*
        | سجل النشاطات
        */

        Route::get(
            '/activity-logs',
            [ActivityLogController::class, 'index']
        );


        /*
        | الإعدادات
        */

        Route::get(
            '/settings',
            [SettingController::class, 'index']
        );

        Route::post(
            '/settings',
            [SettingController::class, 'update']
        );

        Route::get('/external-invoices', [ExternalInvoiceController::class, 'index'])->name('external-invoices.index');
        Route::post('/external-invoices/integration', [ExternalInvoiceController::class, 'saveIntegration'])->name('external-invoices.integration');
        Route::post('/external-invoices/customer-link', [ExternalInvoiceController::class, 'linkCustomer'])->name('external-invoices.customer-link');
        Route::post('/external-invoices/tokens', [ExternalInvoiceController::class, 'createToken'])->name('external-invoices.tokens.create');
        Route::delete('/external-invoices/tokens/{token}', [ExternalInvoiceController::class, 'revokeToken'])->name('external-invoices.tokens.revoke');
    });


    /*
    |--------------------------------------------------------------------------
    | المالية
    | المدير + المحاسب
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:admin,accountant'])->group(function () {

        /*
        | الصندوق
        */

        Route::get(
            '/cashbox',
            [CashboxController::class, 'index']
        );

        Route::post('/cashbox/{cashbox}/transactions', [CashboxController::class, 'transaction']);

        Route::middleware('feature:multiple_cashboxes')->group(function () {
            Route::post('/cashbox', [CashboxController::class, 'store']);
            Route::put('/cashbox/{cashbox}', [CashboxController::class, 'update']);
            Route::delete('/cashbox/{cashbox}', [CashboxController::class, 'destroy']);
        });


        /*
        | التقارير
        */

        Route::get(
            '/reports',
            [ReportController::class, 'index']
        );

        Route::get(
            '/reports/print',
            [ReportController::class, 'print']
        );

        Route::get('/reports/pdf', [ReportController::class, 'pdf']);
        Route::get('/reports/excel', [ReportController::class, 'excel']);


        /*
        |--------------------------------------------------------------------------
        | سندات القبض
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/receipts',
            [ReceiptController::class, 'index']
        );

        Route::post(
            '/receipts',
            [ReceiptController::class, 'store']
        );

        Route::get(
            '/receipts/{receipt}/edit',
            [ReceiptController::class, 'edit']
        );

        Route::put(
            '/receipts/{receipt}',
            [ReceiptController::class, 'update']
        );

        Route::delete(
            '/receipts/{receipt}',
            [ReceiptController::class, 'destroy']
        );

        Route::get(
            '/receipts/{id}/print',
            [ReceiptController::class, 'print']
        );

        Route::get('/receipts/{id}/pdf', [ReceiptController::class, 'pdf']);
        Route::get('/receipts/{id}/excel', [ReceiptController::class, 'excel']);


        /*
        |--------------------------------------------------------------------------
        | سندات الصرف
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/payments',
            [PaymentController::class, 'index']
        );

        Route::post(
            '/payments',
            [PaymentController::class, 'store']
        );

        Route::get(
            '/payments/{payment}/edit',
            [PaymentController::class, 'edit']
        );

        Route::put(
            '/payments/{payment}',
            [PaymentController::class, 'update']
        );

        Route::delete(
            '/payments/{payment}',
            [PaymentController::class, 'destroy']
        );

        Route::get(
            '/payments/{id}/print',
            [PaymentController::class, 'print']
        );

        Route::get('/payments/{id}/pdf', [PaymentController::class, 'pdf']);
        Route::get('/payments/{id}/excel', [PaymentController::class, 'excel']);
    });


    /*
    |--------------------------------------------------------------------------
    | الزبائن والموردين
    | المدير + إدخال البيانات
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:admin,data_entry'])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | الزبائن
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/customers',
            [CustomerController::class, 'index']
        );

        Route::post(
            '/customers',
            [CustomerController::class, 'store']
        );

        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit']);
        Route::put('/customers/{customer}', [CustomerController::class, 'update']);

        Route::get(
            '/customers/{customer}',
            [CustomerController::class, 'show']
        );

        Route::delete(
            '/customers/{customer}',
            [CustomerController::class, 'destroy']
        );

        Route::get(
            '/customers/{customer}/print',
            [CustomerController::class, 'print']
        );

        Route::get('/customers/{customer}/pdf', [CustomerController::class, 'pdf']);
        Route::get('/customers/{customer}/excel', [CustomerController::class, 'excel']);


        /*
        |--------------------------------------------------------------------------
        | الموردين
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/suppliers',
            [SupplierController::class, 'index']
        );

        Route::post(
            '/suppliers',
            [SupplierController::class, 'store']
        );

        Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit']);
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update']);

        Route::get(
            '/suppliers/{supplier}',
            [SupplierController::class, 'show']
        );

        Route::get(
            '/suppliers/{supplier}/print',
            [SupplierController::class, 'print']
        );

        Route::get('/suppliers/{supplier}/pdf', [SupplierController::class, 'pdf']);
        Route::get('/suppliers/{supplier}/excel', [SupplierController::class, 'excel']);
    });

});
