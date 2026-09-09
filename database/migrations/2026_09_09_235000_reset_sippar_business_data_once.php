<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

return new class extends Migration
{
    public $withinTransaction = false;

    private const RESET_KEY = 'sippar-business-reset-2026-09-09';

    public function up(): void
    {
        if (!Schema::hasTable('company_data_reset_backups')) {
            Schema::create('company_data_reset_backups', function (Blueprint $table) {
                $table->string('reset_key')->primary();
                $table->unsignedBigInteger('company_id');
                $table->string('company_code');
                $table->string('company_name');
                $table->longText('encrypted_payload');
                $table->timestamp('completed_at')->nullable();
                $table->timestamp('created_at');
            });
        }

        DB::transaction(function (): void {
            $completed = DB::table('company_data_reset_backups')->where('reset_key', self::RESET_KEY)->first();
            if ($completed) {
                if (!$completed->completed_at) {
                    throw new RuntimeException('An incomplete Sippar reset requires manual review.');
                }
                return;
            }

            // The user's named tenant was identified as SIPPAR / Sippar lights.
            // Never select by a numeric ID, partial name, or first company.
            $matches = DB::table('companies')->whereRaw('LOWER(code) = ?', ['sippar'])->lockForUpdate()->get();
            if ($matches->isEmpty()) {
                return;
            }
            if ($matches->count() !== 1 || !preg_match('/sippar|سيبار/iu', $matches->first()->name)) {
                throw new RuntimeException('Sippar reset stopped: company identity does not match.');
            }
            $company = $matches->first();
            $companyId = $company->id;
            $ids = [];
            foreach (['customers', 'suppliers', 'cashboxes', 'accounts', 'invoices'] as $table) {
                $ids[$table] = Schema::hasTable($table)
                    ? DB::table($table)->where('company_id', $companyId)->lockForUpdate()->pluck('id')->all()
                    : [];
            }

            // Refuse inconsistent links instead of changing another tenant through FK actions.
            foreach (['receipts', 'payments', 'external_invoices', 'invoices', 'customer_external_links', 'cashbox_logs'] as $table) {
                if (!Schema::hasTable($table)) continue;
                foreach (['customer_id' => 'customers', 'supplier_id' => 'suppliers', 'cashbox_id' => 'cashboxes'] as $column => $parent) {
                    if (!Schema::hasColumn($table, $column)) continue;
                    $foreign = DB::table($table)
                        ->where(fn ($query) => $query->where('company_id', '!=', $companyId)->orWhereNull('company_id'))
                        ->whereIn($column, $ids[$parent])->exists();
                    if ($foreign) throw new RuntimeException('Sippar reset stopped: cross-company relationship in '.$table.'.');
                }
            }
            if (DB::table('cashbox_customer')->where(function ($query) use ($ids) {
                $query->where(fn ($q) => $q->whereIn('cashbox_id', $ids['cashboxes'])->whereNotIn('customer_id', $ids['customers']))
                    ->orWhere(fn ($q) => $q->whereIn('customer_id', $ids['customers'])->whereNotIn('cashbox_id', $ids['cashboxes']));
            })->exists()) {
                throw new RuntimeException('Sippar reset stopped: cross-company account/customer link.');
            }

            $queries = [];
            if (Schema::hasTable('invoice_items')) {
                $queries['invoice_items'] = DB::table('invoice_items')->whereIn('invoice_id', $ids['invoices']);
            }
            $queries['transactions'] = DB::table('transactions')->whereIn('account_id', $ids['accounts']);
            $queries['cashbox_customer'] = DB::table('cashbox_customer')->whereIn('cashbox_id', $ids['cashboxes']);
            foreach ([
                'voucher_attachments', 'external_invoices', 'customer_external_links', 'integration_logs',
                'invoices', 'receipts', 'payments', 'cashbox_logs', 'feature_module_records',
                'accounts', 'cashboxes', 'customers', 'suppliers', 'activity_logs', 'system_notifications',
            ] as $table) {
                if (Schema::hasTable($table)) {
                    $queries[$table] = DB::table($table)->where('company_id', $companyId);
                }
            }
            $preserved = ['companies', 'users', 'settings', 'company_features', 'company_api_tokens',
                'external_invoice_integrations', 'company_data_reset_backups'];
            foreach (Schema::getTables() as $table) {
                $name = $table['name'];
                if (isset($queries[$name]) || in_array($name, $preserved, true)) continue;
                if (Schema::hasColumn($name, 'company_id') && DB::table($name)->where('company_id', $companyId)->exists()) {
                    throw new RuntimeException('Sippar reset stopped: unreviewed company table '.$name.'.');
                }
            }

            $snapshot = ['company' => (array) $company, 'tables' => [], 'files' => []];
            foreach ($queries as $table => $query) {
                $snapshot['tables'][$table] = (clone $query)->lockForUpdate()->get()->map(fn ($row) => (array) $row)->all();
            }
            foreach ($snapshot['tables']['voucher_attachments'] ?? [] as $attachment) {
                $path = $attachment['path'];
                if (!str_starts_with($path, 'voucher-attachments/'.$companyId.'/') || str_contains($path, '..') || str_contains($path, '\\')) {
                    throw new RuntimeException('Sippar reset stopped: unexpected attachment path.');
                }
                if (Storage::disk('local')->exists($path)) {
                    $snapshot['files'][$path] = base64_encode(Storage::disk('local')->get($path));
                }
            }
            $plain = json_encode($snapshot, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            $encrypted = Crypt::encryptString($plain);
            if (!hash_equals(hash('sha256', $plain), hash('sha256', Crypt::decryptString($encrypted)))) {
                throw new RuntimeException('Sippar backup verification failed.');
            }
            DB::table('company_data_reset_backups')->insert([
                'reset_key' => self::RESET_KEY, 'company_id' => $companyId, 'company_code' => $company->code,
                'company_name' => $company->name, 'encrypted_payload' => $encrypted,
                'completed_at' => null, 'created_at' => now(),
            ]);
            foreach ($queries as $query) {
                (clone $query)->delete();
            }
            DB::table('cashboxes')->insert([
                'company_id' => $companyId, 'name' => 'الصندوق الرئيسي', 'account_type' => 'cash',
                'bank_name' => null, 'account_number' => null, 'balance' => 0, 'is_active' => true,
                'integration_id' => 'B-'.Str::ulid(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('company_data_reset_backups')->where('reset_key', self::RESET_KEY)->update(['completed_at' => now()]);
        });

        // Database commit precedes file cleanup; the encrypted backup includes file contents.
        // Retrying cleanup never repeats the business-data deletion.
        $backup = DB::table('company_data_reset_backups')->where('reset_key', self::RESET_KEY)->first();
        if ($backup?->completed_at) {
            $snapshot = json_decode(Crypt::decryptString($backup->encrypted_payload), true, 512, JSON_THROW_ON_ERROR);
            foreach (array_keys($snapshot['files']) as $path) {
                if (!str_starts_with($path, 'voucher-attachments/'.$backup->company_id.'/') || str_contains($path, '..') || str_contains($path, '\\')) {
                    throw new RuntimeException('Unexpected backup attachment path.');
                }
                if (Storage::disk('local')->exists($path) && !Storage::disk('local')->delete($path)) {
                    throw new RuntimeException('Could not remove a backed-up attachment.');
                }
            }
        }
    }

    public function down(): void
    {
        throw new RuntimeException('This one-time data reset cannot be reversed automatically. Retain the encrypted backup for recovery.');
    }
};
