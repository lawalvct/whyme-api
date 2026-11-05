<?php

namespace Database\Seeders;

use App\Modules\Identity\Models\TermsVersion;
use Illuminate\Database\Seeder;

class DraftTermsSeeder extends Seeder
{
    /**
     * Local development only: placeholder terms so seller activation can be tried.
     * Real terms come from the owner and their lawyer (DEC17).
     */
    public function run(): void
    {
        foreach ([TermsVersion::TYPE_PLATFORM, TermsVersion::TYPE_SELLER] as $type) {
            TermsVersion::firstOrCreate(
                ['type' => $type, 'version' => '0.1-draft', 'locale' => 'en'],
                ['title' => ucfirst($type).' terms (DRAFT)', 'body' => 'Placeholder. Replace with the approved terms before launch.', 'published_at' => now()],
            );
        }
    }
}
