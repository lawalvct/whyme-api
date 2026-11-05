<?php

namespace App\Modules\Identity\Http\Resources;

use App\Modules\Identity\Models\UserCapability;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserCapability
 */
class CapabilityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'capability' => $this->capability->value,
            'status' => $this->status->value,
            'verification_state' => $this->verification_state->value,
            'activated_at' => $this->activated_at?->toIso8601String(),
        ];
    }
}
