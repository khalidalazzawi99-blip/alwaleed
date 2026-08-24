<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_module_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('module', 40);
            $table->string('reference', 100)->nullable();
            $table->string('name');
            $table->date('record_date');
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('status', 40)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'module']);
        });

        Schema::table('cashboxes', function (Blueprint $table) {
            $table->string('name')->default('الصندوق الرئيسي')->after('company_id');
            $table->boolean('is_active')->default(true)->after('balance');
        });

        Schema::create('voucher_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('voucher_type', 20);
            $table->unsignedBigInteger('voucher_id');
            $table->string('original_name');
            $table->string('path');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
            $table->index(['company_id', 'voucher_type', 'voucher_id'], 'voucher_attachment_lookup');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_attachments');
        Schema::table('cashboxes', function (Blueprint $table) {
            $table->dropColumn(['name', 'is_active']);
        });
        Schema::dropIfExists('feature_module_records');
    }
};
