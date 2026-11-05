<?php

namespace Database\Seeders;

use App\Modules\Platform\Models\FeatureFlag;
use Illuminate\Database\Seeder;

class FeatureFlagSeeder extends Seeder
{
    /**
     * Everything starts off; the owner switches features on from the admin console.
     */
    public function run(): void
    {
        $flags = [
            'auto_release_enabled' => [true, 'Release vendor funds automatically by rule (STA03, DEC07). Off until the owner approves a policy.'],
            'paid_enquiries_enabled' => [true, 'Charge for enquiries (COM04). Off at launch.'],
            'iap_ios_enabled' => [true, 'Offer Apple in-app purchase for Pro, featured and job posts in the iOS app (docs/01 §8.1).'],
            'ai_moderation_enabled' => [false, 'Send borderline messages to the AI moderation assist (docs/05 #1).'],
        ];

        foreach ($flags as $key => [$isPublic, $description]) {
            FeatureFlag::firstOrCreate(['key' => $key], ['enabled' => false, 'is_public' => $isPublic, 'description' => $description]);
        }
    }
}
