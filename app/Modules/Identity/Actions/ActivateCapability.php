<?php

namespace App\Modules\Identity\Actions;

use App\Modules\Identity\Enums\Capability;
use App\Modules\Identity\Enums\CapabilityStatus;
use App\Modules\Identity\Enums\VerificationState;
use App\Modules\Identity\Models\TermsAcceptance;
use App\Modules\Identity\Models\TermsVersion;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Models\UserCapability;
use App\Modules\Platform\Exceptions\ApiException;
use App\Modules\Platform\Services\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Adds a capability to the same account: no second signup and no role switch
 * (ACC07). Seller capabilities record which terms version was accepted, and in
 * which language, before they are enabled (AT62, I18N06). Verification is separate (TRU01).
 */
class ActivateCapability
{
    /**
     * @return array{capability: UserCapability, created: bool}
     */
    public function handle(User $user, Capability $capability, ?int $termsVersionId, Request $request): array
    {
        $existing = $user->capabilities()->where('capability', $capability)->first();
        if ($existing) {
            return ['capability' => $existing, 'created' => false];
        }

        $terms = null;
        if ($capability->requiresSellerTerms()) {
            $terms = TermsVersion::published()->where('type', TermsVersion::TYPE_SELLER)->find($termsVersionId);
            if (! $terms) {
                throw ApiException::field('terms_required', 'terms_version_id');
            }
        }

        $record = DB::transaction(function () use ($user, $capability, $terms, $request) {
            $acceptance = $terms ? TermsAcceptance::create([
                'user_id' => $user->id,
                'terms_version_id' => $terms->id,
                'locale' => $terms->locale,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'accepted_at' => now(),
            ]) : null;

            return UserCapability::create([
                'user_id' => $user->id,
                'capability' => $capability,
                'status' => CapabilityStatus::Active,
                'verification_state' => VerificationState::Unverified,
                'terms_acceptance_id' => $acceptance?->id,
                'activated_at' => now(),
            ]);
        });

        Audit::record('user.capability_activated', $record, new: ['capability' => $capability->value], actor: $user);

        return ['capability' => $record, 'created' => true];
    }
}
