<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind', 40)->index();
            $table->string('title');
            $table->text('message');
            $table->string('link')->nullable();
            $table->string('fingerprint')->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();
            $table->unique(['user_id', 'fingerprint'], 'notifications_user_fingerprint_unique');
            $table->index(['user_id', 'read_at', 'created_at'], 'notifications_inbox_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
    }
};
