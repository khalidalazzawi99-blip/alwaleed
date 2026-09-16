<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voucher_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 20);
            $table->unsignedSmallInteger('year');
            $table->unsignedBigInteger('last_number')->default(0);
            $table->timestamps();
            $table->unique(['company_id', 'document_type', 'year'], 'voucher_sequences_scope_unique');
        });

        foreach (['receipts', 'payments'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('verification_token', 64)->nullable()->unique();
                $table->string('status', 20)->default('active')->index();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('cancelled_at')->nullable();
                $table->string('cancellation_reason', 1000)->nullable();
            });
        }

        $this->backfill('receipts', 'receipt_no', 'receipt_date', 'receipt', 'RCP');
        $this->backfill('payments', 'payment_no', 'payment_date', 'payment', 'PAY');

        Schema::table('receipts', function (Blueprint $table) {
            $table->dropUnique('receipts_party_number_unique');
            $table->unique(['company_id', 'receipt_no'], 'receipts_company_number_unique');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('payments_party_number_unique');
            $table->unique(['company_id', 'payment_no'], 'payments_company_number_unique');
        });
    }

    private function backfill(string $table, string $numberColumn, string $dateColumn, string $type, string $prefix): void
    {
        $used = [];
        $last = [];

        DB::table($table)->orderBy('company_id')->orderBy('id')->get()->each(function ($row) use (
            $table, $numberColumn, $dateColumn, $prefix, &$used, &$last
        ) {
            $year = (int) substr((string) ($row->{$dateColumn} ?: $row->created_at ?: now()), 0, 4);
            $year = $year ?: now()->year;
            $scope = $row->company_id.'|'.$year;
            $number = (string) $row->{$numberColumn};
            $valid = preg_match('/^'.preg_quote($prefix, '/').'-'.$year.'-(\d{6})$/', $number, $match) === 1;

            if ($valid && empty($used[$row->company_id.'|'.$number])) {
                $sequence = (int) $match[1];
                $last[$scope] = max($last[$scope] ?? 0, $sequence);
            } else {
                do {
                    $sequence = ($last[$scope] ?? 0) + 1;
                    $last[$scope] = $sequence;
                    $number = sprintf('%s-%d-%06d', $prefix, $year, $sequence);
                } while (! empty($used[$row->company_id.'|'.$number]));
            }

            $used[$row->company_id.'|'.$number] = true;
            DB::table($table)->where('id', $row->id)->update([
                $numberColumn => $number,
                'verification_token' => Str::random(64),
                'status' => 'active',
            ]);
        });

        foreach ($last as $scope => $number) {
            [$companyId, $year] = explode('|', $scope);
            if (! $companyId) {
                continue;
            }
            DB::table('voucher_sequences')->insert([
                'company_id' => $companyId,
                'document_type' => $type,
                'year' => $year,
                'last_number' => $number,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('receipts', function (Blueprint $table) {
            $table->dropUnique('receipts_company_number_unique');
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['verification_token', 'status', 'created_by', 'updated_by', 'cancelled_by', 'cancelled_at', 'cancellation_reason']);
            $table->unique(['company_id', 'customer_id', 'receipt_no'], 'receipts_party_number_unique');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('payments_company_number_unique');
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['verification_token', 'status', 'created_by', 'updated_by', 'cancelled_by', 'cancelled_at', 'cancellation_reason']);
            $table->unique(['company_id', 'supplier_id', 'payment_no'], 'payments_party_number_unique');
        });
        Schema::dropIfExists('voucher_sequences');
    }
};
