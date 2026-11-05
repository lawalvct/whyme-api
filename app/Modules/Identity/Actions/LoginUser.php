<?php

namespace App\Modules\Identity\Actions;

use App\Modules\Identity\Enums\OtpPurpose;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\OtpService;
use App\Modules\Identity\Services\SessionIssuer;
use App\Modules\Platform\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Phone + password. A device the account hasn't used before also needs an SMS
 * code (docs/01 §11), which keeps OTP costs to the cases that matter.
 */
class LoginUser
{
    public function __construct(
        private readonly OtpService $otp,
        private readonly SessionIssuer $sessions,
    ) {}

    /**
     * @param  array{phone: string, password: string, code?: ?string, device: array{id: string, platform: string}}  $data
     * @return array{user: User, token: ?string}|array{otp_required: true}
     */
    public function handle(array $data, Request $request): array
    {
        $user = User::where('phone', $data['phone'])->first();

        // Hash even when the phone is unknown, so response time doesn't reveal which phones exist.
        $valid = Hash::check($data['password'], $user?->password ?? '$2y$12$'.str_repeat('a', 53));
        if (! $user || ! $valid) {
            throw new ApiException('invalid_credentials', 422);
        }

        if ($user->isSuspended()) {
            throw new ApiException('account_suspended', 403);
        }

        $isNewDevice = $user->devices()->exists() && ! $this->sessions->isKnownDevice($user, $data['device']['id']);

        if ($isNewDevice) {
            if (empty($data['code'])) {
                $this->otp->send($user->phone, OtpPurpose::NewDevice, $request->ip(), $user->locale);

                return ['otp_required' => true];
            }

            $this->otp->verify($user->phone, OtpPurpose::NewDevice, $data['code']);
        }

        return ['user' => $user, 'token' => $this->sessions->issue($user, $data['device'], $request)];
    }
}
