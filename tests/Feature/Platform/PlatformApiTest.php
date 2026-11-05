<?php

namespace Tests\Feature\Platform;

use App\Modules\Platform\Services\Settings;
use Database\Seeders\FeatureFlagSeeder;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlatformApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([LanguageSeeder::class, FeatureFlagSeeder::class, SettingSeeder::class]);
    }

    public function test_health_reports_ok_with_a_request_id(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertHeader('X-Request-Id');
    }

    public function test_config_lists_languages_and_only_public_flags(): void
    {
        $this->getJson('/api/v1/config')
            ->assertOk()
            ->assertJsonPath('data.default_language', 'en')
            ->assertJsonPath('data.languages.0.code', 'en')
            ->assertJsonPath('data.languages.1.code', 'ig')
            ->assertJsonPath('data.features.auto_release_enabled', false)
            ->assertJsonMissingPath('data.features.ai_moderation_enabled');
    }

    public function test_accept_language_selects_igbo(): void
    {
        $this->getJson('/api/v1/config', ['Accept-Language' => 'ig, en;q=0.8'])
            ->assertHeader('Content-Language', 'ig')
            ->assertJsonPath('data.locale', 'ig');
    }

    public function test_unsupported_language_falls_back_to_english(): void
    {
        $this->getJson('/api/v1/config', ['Accept-Language' => 'fr-FR'])
            ->assertHeader('Content-Language', 'en');
    }

    public function test_outdated_mobile_app_must_update_but_can_still_read_config(): void
    {
        app(Settings::class)->set('app.min_version.android', '2.0.0');
        $headers = ['X-Platform' => 'android', 'X-App-Version' => '1.4.0'];

        $this->getJson('/api/v1/me', $headers)
            ->assertStatus(426)
            ->assertJsonPath('code', 'app_update_required')
            ->assertJsonPath('min_version', '2.0.0');

        $this->getJson('/api/v1/config', $headers)->assertOk();
    }

    public function test_errors_share_one_shape(): void
    {
        $this->getJson('/api/v1/me')
            ->assertStatus(401)
            ->assertJsonStructure(['code', 'message', 'fields', 'retryable'])
            ->assertJson(['code' => 'unauthenticated', 'retryable' => false]);
    }
}
