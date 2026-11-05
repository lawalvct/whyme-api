<?php

namespace App\Modules\Identity\Http\Controllers;

use App\Modules\Identity\Actions\ActivateCapability;
use App\Modules\Identity\Actions\UpdateProfile;
use App\Modules\Identity\Enums\Capability;
use App\Modules\Identity\Http\Requests\ActivateCapabilityRequest;
use App\Modules\Identity\Http\Requests\UpdateProfileRequest;
use App\Modules\Identity\Http\Resources\CapabilityResource;
use App\Modules\Identity\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The signed-in user's own account and capabilities (ACC07, ACC08).
 */
class MeController
{
    public function show(Request $request): UserResource
    {
        return UserResource::make($request->user()->load('capabilities'));
    }

    public function update(UpdateProfileRequest $request, UpdateProfile $action): UserResource
    {
        return UserResource::make($action->handle($request->user(), $request->validated())->load('capabilities'));
    }

    public function activateCapability(ActivateCapabilityRequest $request, ActivateCapability $action): JsonResponse
    {
        $result = $action->handle(
            $request->user(),
            Capability::from($request->validated('capability')),
            $request->validated('terms_version_id'),
            $request,
        );

        return CapabilityResource::make($result['capability'])
            ->response()
            ->setStatusCode($result['created'] ? 201 : 200);
    }
}
