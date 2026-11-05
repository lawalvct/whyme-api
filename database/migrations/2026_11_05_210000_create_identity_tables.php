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
        Schema::create('user_devices', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('device_id', 64);
            $table->string('platform', 10);
            $table->string('name')->nullable();
            $table->string('app_version', 20)->nullable();
            $table->string('push_token')->nullable();
            $table->unsignedBigInteger('token_id')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'device_id']);
        });

        Schema::create('otp_challenges', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('phone', 20);
            $table->string('purpose', 30);
            $table->char('code_hash', 64);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->index(['phone', 'purpose', 'created_at']);
        });

        // Versioned per language; acceptance records both (ACC07, I18N06).
        Schema::create('terms_versions', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30);
            $table->string('version', 20);
            $table->string('locale', 8);
            $table->string('title');
            $table->longText('body');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['type', 'version', 'locale']);
        });

        Schema::create('terms_acceptances', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('terms_version_id')->constrained()->restrictOnDelete();
            $table->string('locale', 8);
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('accepted_at');
        });

        // Additive capabilities on the one account; no role switching (ACC01, ACC07).
        Schema::create('user_capabilities', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('capability', 20);
            $table->string('status', 20)->default('active');
            $table->string('verification_state', 20)->default('unverified');
            $table->foreignUlid('terms_acceptance_id')->nullable()->constrained('terms_acceptances');
            $table->timestamp('activated_at');
            $table->timestamps();
            $table->unique(['user_id', 'capability']);
        });

        // Staff are separate from marketplace users (ACC04).
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->text('app_authentication_secret')->nullable();
            $table->text('app_authentication_recovery_codes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
        Schema::dropIfExists('user_capabilities');
        Schema::dropIfExists('terms_acceptances');
        Schema::dropIfExists('terms_versions');
        Schema::dropIfExists('otp_challenges');
        Schema::dropIfExists('user_devices');
    }
};
