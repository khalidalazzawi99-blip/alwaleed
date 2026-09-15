<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Keep the row as a tombstone so every historical foreign key remains
        // valid, while removing it completely from normal application queries.
        DB::table('cashboxes')->where('is_system_legacy', true)->whereNull('deleted_at')->update([
            'is_active' => false,
            'deleted_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('cashboxes')->where('is_system_legacy', true)->update(['deleted_at' => null]);
    }
};
