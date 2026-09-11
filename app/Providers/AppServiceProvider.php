<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\ActivityLog;
use App\Models\Cashbox;
use App\Models\Company;
use App\Models\CompanyApiToken;
use App\Models\CompanyFeature;
use App\Models\Customer;
use App\Models\CustomerExternalLink;
use App\Models\DailyExpense;
use App\Models\DailyExpenseParty;
use App\Models\ExternalInvoice;
use App\Models\ExternalInvoiceIntegration;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\SystemNotification;
use App\Models\Transaction;
use App\Models\User;
use App\Observers\AuditObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        foreach (['view', 'create', 'update', 'delete', 'export'] as $action) {
            Gate::define('sippar.daily_accounts.'.$action, function (User $user): bool {
                return in_array($user->role, ['admin', 'accountant'], true)
                    && $user->company?->isSippar()
                    && $user->company->status === 'active'
                    && (! $user->company->subscription_end || now()->startOfDay()->lte($user->company->subscription_end));
            });
        }

        View::composer('*', function ($view) {
            $companyId = auth()->user()?->company_id;
            static $currencies = [];

            if ($companyId && ! array_key_exists($companyId, $currencies)) {
                $currencies[$companyId] = Setting::where('company_id', $companyId)->value('currency') ?: 'IQD';
            }

            $view->with('companyCurrency', $companyId ? $currencies[$companyId] : 'IQD');
        });

        foreach ([
            DailyExpense::class,
            DailyExpenseParty::class,
            Account::class,
            Cashbox::class,
            Company::class,
            CompanyFeature::class,
            CompanyApiToken::class,
            CustomerExternalLink::class,
            ExternalInvoice::class,
            ExternalInvoiceIntegration::class,
            Customer::class,
            Payment::class,
            Receipt::class,
            Setting::class,
            Supplier::class,
            Transaction::class,
            User::class,
        ] as $model) {
            $model::observe(AuditObserver::class);
        }

        RateLimiter::for('external-api', fn (Request $request) => Limit::perMinute(120)->by(hash('sha256', (string) $request->bearerToken()).'|'.$request->ip())
        );

        // Also prune once per day on normal web traffic, so retention works
        // even when the host has not configured Laravel's scheduler worker.
        if (! $this->app->runningInConsole()) {
            Cache::remember(
                'audit-log-pruned-'.now()->toDateString(),
                now()->endOfDay(),
                function () {
                    ActivityLog::query()
                        ->where('created_at', '<', now()->subDays(30))
                        ->toBase()
                        ->delete();

                    SystemNotification::query()
                        ->where('created_at', '<', now()->subDays(30))
                        ->delete();

                    return true;
                }
            );
        }
    }
}
