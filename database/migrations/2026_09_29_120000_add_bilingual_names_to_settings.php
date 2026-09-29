<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->string('company_name_ar')->nullable()->after('company_name');
            $table->string('company_name_en')->nullable()->after('company_name_ar');
        });
    }

    public function down(): void
    {
        Schema::table('settings', fn (Blueprint $table) => $table->dropColumn(['company_name_ar', 'company_name_en']));
    }
};
