<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = [
            'logo' => fn (Blueprint $table) => $table->string('logo')->nullable(),
            'phone' => fn (Blueprint $table) => $table->string('phone')->nullable(),
            'email' => fn (Blueprint $table) => $table->string('email')->nullable(),
            'address' => fn (Blueprint $table) => $table->text('address')->nullable(),
            'subscription_start' => fn (Blueprint $table) => $table->date('subscription_start')->nullable(),
            'subscription_end' => fn (Blueprint $table) => $table->date('subscription_end')->nullable(),
            'status' => fn (Blueprint $table) => $table->enum('status', ['active', 'inactive', 'expired'])->default('active'),
            'max_users' => fn (Blueprint $table) => $table->integer('max_users')->default(5),
        ];

        foreach ($columns as $column => $definition) {
            if (! Schema::hasColumn('companies', $column)) {
                Schema::table('companies', $definition);
            }
        }
    }

    public function down(): void
    {
        // These columns also belong to the original companies-table migration,
        // so rolling this compatibility migration back must not remove them.
    }
};
