<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * This migration previously deleted Sippar business data after creating an
     * encrypted backup. That destructive one-time operation is permanently
     * disabled so an environment which has not recorded this migration can run
     * `artisan migrate --force` safely. Laravel will record the migration as
     * applied without changing any company data.
     *
     * Environments which already recorded the original migration are unaffected.
     */
    public function up(): void
    {
        // Intentionally left blank: never reset or delete Sippar data on deploy.
    }

    /**
     * There is no schema or data change to reverse.
     */
    public function down(): void
    {
        // Intentionally left blank.
    }
};
