<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Modules\Identity\Actions\LoginUser;
use App\Modules\Identity\Actions\RegisterUser;
use App\Modules\Identity\Actions\RequestOtp;
use App\Modules\Identity\Actions\ResetPassword;
use App\Modules\Identity\Enums\OtpPurpose;
use App\Modules\Identity\Http\Requests\LoginRequest;
use App\Modules\Identity\Http\Requests\RegisterRequest;
use App\Modules\Identity\Http\Requests\RequestOtpRequest;
use App\Modules\Identity\Http\Requests\ResetPasswordRequest;
use App\Modules\Identity\Http\Resources\UserResource;
use App\Modules\Identity\Models\UserDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Signup, sign-in, recovery and sign-out. These need a connection (OFF02), so
 * they don't go through the offline outbox or the idempotency store.
 */
class AuthController
{
    public function requestOtp(RequestOtpRequest $request, RequestOtp $action): JsonResponse
    {
        $action->handle(
            $request->validated('phone'),
            OtpPurpose::from($request->validated('purpose')),
            $request->ip(),
            App::getLocale(),
        );

        // Identical for every phone, so the endpoint can't be used to find accounts.
        return response()->json(['data' => [
            'expires_in' => config('whyme.otp.ttl_seconds'),
            'resend_in' => config('whyme.otp.resend_cooldown_seconds'),
        ]], 202);
    }

    public function register(RegisterRequest $request, RegisterUser $action): JsonResponse
    {
        $result = $action->handle($request->validated(), $request);

        return response()->json(['data' => [
            'user' => UserResource::make($result['user']->load('capabilities')),
            'token' => $result['token'],
        ]], 201);
    }

    public function login(LoginRequest $request, LoginUser $action): JsonResponse
    {
        $result = $action->handle($request->validated(), $request);

        if (isset($result['otp_required'])) {
            return response()->json(['data' => [
                'otp_required' => true,
                'expires_in' => config('whyme.otp.ttl_seconds'),
            ]], 202);
        }

        return response()->json(['data' => [
            'user' => UserResource::make($result['user']->load('capabilities')),
            'token' => $result['token'],
        ]]);
    }

    public function resetPassword(ResetPasswordRequest $request, ResetPassword $action): JsonResponse
    {
        $action->handle($request->validated('phone'), $request->validated('code'), $request->validated('password'));

        return response()->json(['data' => ['reset' => true]]);
    }

    public function logout(Request $request): Response
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            UserDevice::where('token_id', $token->getKey())->update(['token_id' => null, 'push_token' => null]);
            $token->delete();
        } else {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->noContent();
    }
}
