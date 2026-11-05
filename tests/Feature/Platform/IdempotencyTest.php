<?php

namespace Tests\Feature\Platform;

use App\Modules\Identity\Models\User;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Offline clients retry with the same Idempotency-Key (OFF05, AT08).
 */
class IdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private string $key;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
        Sanctum::actingAs(User::factory()->create());
        $this->key = (string) Str::uuid7();
    }

    public function test_a_retry_gets_the_stored_response_and_writes_once(): void
    {
        $first = $this->send(['capability' => 'job_seeker'])->assertCreated();
        $second = $this->send(['capability' => 'job_seeker'])
            ->assertCreated()
            ->assertHeader('Idempotent-Replayed', 'true');

        $this->assertSame($first->json(), $second->json());
        $this->assertDatabaseCount('user_capabilities', 1);
        $this->assertDatabaseCount('idempotency_keys', 1);
    }

    public function test_the_same_key_with_a_different_body_is_refused(): void
    {
        $this->send(['capability' => 'job_seeker'])->assertCreated();

        $this->send(['capability' => 'employer'])
            ->assertStatus(409)
            ->assertJsonPath('code', 'idempotency_mismatch');
    }

    public function test_a_failed_request_is_not_stored_so_the_fixed_draft_can_be_resent(): void
    {
        $this->send(['capability' => 'not-a-capability'])->assertStatus(422);

        $this->send(['capability' => 'job_seeker'])->assertCreated();
        $this->assertDatabaseCount('idempotency_keys', 1);
    }

    public function test_a_malformed_key_is_rejected(): void
    {
        $this->postJson('/api/v1/me/capabilities', ['capability' => 'job_seeker'], ['Idempotency-Key' => 'bad key!'])
            ->assertStatus(400)
            ->assertJsonPath('code', 'idempotency_key_invalid');
    }

    private function send(array $body)
    {
        return $this->postJson('/api/v1/me/capabilities', $body, ['Idempotency-Key' => $this->key]);
    }
}
