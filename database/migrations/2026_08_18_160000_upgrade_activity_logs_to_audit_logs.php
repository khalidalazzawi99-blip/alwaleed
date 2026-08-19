<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('company_id')->constrained('users')->nullOnDelete();
            $table->string('event', 20)->nullable()->after('action');
            $table->string('auditable_type')->nullable()->after('event');
            $table->unsignedBigInteger('auditable_id')->nullable()->after('auditable_type');
            $table->json('old_values')->nullable()->after('details');
            $table->json('new_values')->nullable()->after('old_values');
            $table->string('ip_address', 45)->nullable()->after('new_values');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->index(['auditable_type', 'auditable_id'], 'activity_logs_subject_index');
            $table->index(['company_id', 'created_at'], 'activity_logs_company_date_index');
            $table->index('created_at', 'activity_logs_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_subject_index');
            $table->dropIndex('activity_logs_company_date_index');
            $table->dropIndex('activity_logs_created_at_index');
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'event', 'auditable_type', 'auditable_id', 'old_values',
                'new_values', 'ip_address', 'user_agent',
            ]);
        });
    }
};
