<?php

namespace App\Modules\Identity\Actions;

use App\Modules\Identity\Enums\OtpPurpose;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\OtpService;
use App\Modules\Identity\Services\SessionIssuer;
use App\Modules\Platform\Exceptions\ApiException;
use App\Modules\Platform\Services\Audit;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

/**
 * Phone signup: verify the SMS code, create the one account, sign in on this device (ACC01, ACC02).
 */
class RegisterUser
{
    public function __construct(
        private readonly OtpService $otp,
        private readonly SessionIssuer $sessions,
    ) {}

    /**
     * @param  array{name: string, phone: string, password: string, code: string, device: array{id: string, platform: string}}  $data
     * @return array{user: User, token: ?string}
     */
    public function handle(array $data, Request $request): array
    {
        $this->otp->verify($data['phone'], OtpPurpose::Register, $data['code']);

        if (User::withTrashed()->where('phone', $data['phone'])->exists()) {
            throw ApiException::field('phone_taken', 'phone');
        }

        try {
            $user = User::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'password' => $data['password'],
                'locale' => app()->getLocale(),
            ]);
        } catch (UniqueConstraintViolationException) {
            throw ApiException::field('phone_taken', 'phone');
        }

        $user->forceFill(['phone_verified_at' => now()])->save();

        $token = $this->sessions->issue($user, $data['device'], $request);
        Audit::record('user.registered', $user, actor: $user);

        return ['user' => $user, 'token' => $token];
    }
}
