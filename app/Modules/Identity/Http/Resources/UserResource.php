<?php

namespace App\Modules\Identity\Http\Resources;

use App\Modules\Identity\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The signed-in user's own profile. Never used for public views: those get a
 * separate resource without phone or email (COM01, SAF01).
 *
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'locale' => $this->locale,
            'status' => $this->status->value,
            'version' => $this->version,
            'phone_verified_at' => $this->phone_verified_at?->toIso8601String(),
            'capabilities' => CapabilityResource::collection($this->whenLoaded('capabilities')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
