<?php

namespace Tests\Feature\Identity;

use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\Sms\ArraySmsGateway;
use App\Modules\Identity\Services\Sms\SmsGateway;
use Database\Seeders\LanguageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    private const PHONE = '+2348031234567';

    private ArraySmsGateway $sms;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(LanguageSeeder::class);
        $this->sms = new ArraySmsGateway;
        $this->app->instance(SmsGateway::class, $this->sms);
    }

    public function test_signup_with_sms_code_creates_one_account_and_a_device_token(): void
    {
        $response = $this->register()
            ->assertCreated()
            ->assertJsonPath('data.user.phone', self::PHONE)
            ->assertJsonPath('data.user.locale', 'en')
            ->assertJsonPath('data.user.version', 1);

        $this->assertNotEmpty($response->json('data.token'));
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('user_devices', ['device_id' => 'device-android-0001', 'platform' => 'android']);
    }

    public function test_signup_saves_the_language_the_app_was_using(): void
    {
        $this->withHeader('Accept-Language', 'ig');

        $this->register()->assertCreated()->assertJsonPath('data.user.locale', 'ig');
    }

    public function test_wrong_code_is_rejected_and_the_attempt_is_counted(): void
    {
        $this->postJson('/api/v1/auth/otp', ['phone' => '0803 123 4567', 'purpose' => 'register'])->assertAccepted();
        $wrong = $this->sms->lastCodeFor(self::PHONE) === '000000' ? '111111' : '000000';

        $this->postJson('/api/v1/auth/register', $this->registration($wrong))
            ->assertStatus(422)
            ->assertJsonPath('code', 'otp_invalid')
            ->assertJsonStructure(['fields' => ['code']]);

        $this->assertDatabaseHas('otp_challenges', ['phone' => self::PHONE, 'attempts' => 1]);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_otp_response_does_not_reveal_whether_a_phone_has_an_account(): void
    {
        User::factory()->create(['phone' => self::PHONE]);

        $existing = $this->postJson('/api/v1/auth/otp', ['phone' => '08031234567', 'purpose' => 'register'])->assertAccepted();
        $unknown = $this->postJson('/api/v1/auth/otp', ['phone' => '08039876543', 'purpose' => 'register'])->assertAccepted();

        $this->assertSame($existing->json(), $unknown->json());
        // The existing user gets a "sign in instead" SMS, not a code.
        $this->assertNull($this->sms->lastCodeFor(self::PHONE));
        $this->assertCount(2, $this->sms->sent);
    }

    public function test_sign_in_on_a_known_device_returns_a_token(): void
    {
        $this->register()->assertCreated();

        $this->login()->assertOk()->assertJsonPath('data.user.phone', self::PHONE);
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->register()->assertCreated();

        $this->login(password: 'not-the-password')
            ->assertStatus(422)
            ->assertJsonPath('code', 'invalid_credentials');
    }

    public function test_a_new_device_needs_an_sms_code(): void
    {
        $this->register()->assertCreated();

        $this->login(deviceId: 'device-ios-0002')
            ->assertStatus(202)
            ->assertJsonPath('data.otp_required', true);

        $this->login(deviceId: 'device-ios-0002', code: $this->sms->lastCodeFor(self::PHONE))
            ->assertOk();

        $this->assertDatabaseCount('user_devices', 2);
    }

    public function test_password_reset_signs_out_every_device(): void
    {
        $this->register()->assertCreated();
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->postJson('/api/v1/auth/otp', ['phone' => self::PHONE, 'purpose' => 'reset_password'])->assertAccepted();
        $this->postJson('/api/v1/auth/password/reset', [
            'phone' => self::PHONE,
            'code' => $this->sms->lastCodeFor(self::PHONE),
            'password' => 'a-new-password',
        ])->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
        $this->login(password: 'a-new-password')->assertOk();
    }

    public function test_sign_out_revokes_the_device_token(): void
    {
        $token = $this->register()->json('data.token');

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertNoContent();
        $this->app['auth']->forgetGuards();

        $this->withToken($token)->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_suspended_account_cannot_sign_in(): void
    {
        User::factory()->suspended()->create(['phone' => self::PHONE]);

        $this->login(password: 'password')
            ->assertForbidden()
            ->assertJsonPath('code', 'account_suspended');
    }

    private function register(): TestResponse
    {
        $this->postJson('/api/v1/auth/otp', ['phone' => '0803 123 4567', 'purpose' => 'register'])->assertAccepted();

        return $this->postJson('/api/v1/auth/register', $this->registration($this->sms->lastCodeFor(self::PHONE)));
    }

    /**
     * @return array<string, mixed>
     */
    private function registration(?string $code): array
    {
        return [
            'name' => 'Ada Obi',
            'phone' => '0803 123 4567',
            'password' => 'secret-pass',
            'code' => $code,
            'device' => ['id' => 'device-android-0001', 'platform' => 'android', 'app_version' => '1.0.0'],
        ];
    }

    private function login(string $password = 'secret-pass', string $deviceId = 'device-android-0001', ?string $code = null): TestResponse
    {
        return $this->postJson('/api/v1/auth/login', array_filter([
            'phone' => self::PHONE,
            'password' => $password,
            'code' => $code,
            'device' => ['id' => $deviceId, 'platform' => 'android'],
        ]));
    }
}
