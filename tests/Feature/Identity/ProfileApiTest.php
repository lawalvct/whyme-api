<?php

namespace Tests\Feature\Identity;

use App\Modules\Identity\Models\TermsVersion;
use App\Modules\Identity\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
    }

    public function test_update_with_the_current_version_saves_and_bumps_the_version(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson('/api/v1/me', ['version' => 1, 'name' => 'Chidi Okafor', 'locale' => 'ig'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Chidi Okafor')
            ->assertJsonPath('data.locale', 'ig')
            ->assertJsonPath('data.version', 2);
    }

    public function test_a_stale_edit_gets_a_conflict_with_the_current_record(): void
    {
        $user = User::factory()->create(['name' => 'Name from the other device']);
        $user->forceFill(['version' => 2])->save();
        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/me', ['version' => 1, 'name' => 'Stale offline edit'])
            ->assertStatus(409)
            ->assertJsonPath('code', 'conflict')
            ->assertJsonPath('data.version', 2)
            ->assertJsonPath('data.name', 'Name from the other device');
    }

    public function test_only_enabled_languages_can_be_chosen(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->patchJson('/api/v1/me', ['version' => 1, 'locale' => 'fr'])
            ->assertStatus(422)
            ->assertJsonPath('code', 'validation_failed');
    }

    public function test_seller_capability_needs_the_current_seller_terms(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/v1/me/capabilities', ['capability' => 'vendor'])
            ->assertStatus(422)
            ->assertJsonPath('code', 'terms_required');

        $terms = TermsVersion::create([
            'type' => TermsVersion::TYPE_SELLER, 'version' => '1.0', 'locale' => 'ig',
            'title' => 'Seller terms', 'body' => 'Draft', 'published_at' => now(),
        ]);

        $this->postJson('/api/v1/me/capabilities', ['capability' => 'vendor', 'terms_version_id' => $terms->id])
            ->assertCreated()
            ->assertJsonPath('data.capability', 'vendor')
            ->assertJsonPath('data.verification_state', 'unverified');

        $this->assertDatabaseHas('terms_acceptances', ['terms_version_id' => $terms->id, 'locale' => 'ig']);
    }
}
