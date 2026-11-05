<?php

namespace App\Modules\Identity\Services;

use App\Modules\Identity\Enums\DevicePlatform;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Signs a user in on one device. Mobile gets one revocable Sanctum token per
 * device (kept in secure storage); the website gets Sanctum's cookie session.
 */
class SessionIssuer
{
    /**
     * @param  array{id: string, platform: string, name?: ?string, app_version?: ?string}  $device
     * @return string|null The plain-text bearer token, or null for a cookie session.
     */
    public function issue(User $user, array $device, Request $request): ?string
    {
        $record = UserDevice::updateOrCreate(
            ['user_id' => $user->id, 'device_id' => $device['id']],
            [
                'platform' => $device['platform'],
                'name' => $device['name'] ?? null,
                'app_version' => $device['app_version'] ?? null,
                'last_seen_at' => now(),
            ],
        );

        $user->forceFill(['last_login_at' => now()])->save();

        if ($record->platform === DevicePlatform::Web && $request->hasSession()) {
            Auth::guard('web')->login($user);
            $request->session()->regenerate();

            return null;
        }

        // One live token per device: signing in again replaces the old one.
        if ($record->token_id) {
            $user->tokens()->whereKey($record->token_id)->delete();
        }

        $token = $user->createToken(
            "device:{$record->device_id}",
            ['*'],
            now()->addDays(config('whyme.tokens.expire_days')),
        );

        $record->forceFill(['token_id' => $token->accessToken->getKey()])->save();

        return $token->plainTextToken;
    }

    public function isKnownDevice(User $user, string $deviceId): bool
    {
        return $user->devices()->where('device_id', $deviceId)->exists();
    }
}
