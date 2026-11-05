<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Stored in the same transaction as the write, so a retry after a lost
        // response gets the original response back (OFF05, AT08).
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->id();
            $table->string('owner', 64);
            $table->string('key', 64);
            $table->string('method', 10);
            $table->string('path');
            $table->char('request_hash', 64);
            $table->unsignedSmallInteger('status_code');
            $table->longText('response_body');
            $table->timestamp('created_at')->index();
            $table->unique(['owner', 'key']);
        });

        Schema::create('languages', function (Blueprint $table) {
            $table->string('code', 8)->primary();
            $table->string('name');
            $table->string('native_name');
            $table->boolean('enabled')->default(false);
            $table->boolean('is_default')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value')->nullable();
            $table->string('description')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->boolean('enabled')->default(false);
            $table->boolean('is_public')->default(false);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Append-only record of staff and sensitive user actions (ADM02, NFR02).
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('actor_type', 20);
            $table->string('actor_id', 26)->nullable();
            $table->string('action', 100);
            $table->string('subject_type', 100)->nullable();
            $table->string('subject_id', 26)->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('request_id', 64)->nullable();
            $table->timestamp('created_at')->index();
            $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('languages');
        Schema::dropIfExists('idempotency_keys');
    }
};
