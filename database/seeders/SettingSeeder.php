<?php

namespace Database\Seeders;

use App\Modules\Platform\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Owner-editable values. Existing values are never overwritten.
     */
    public function run(): void
    {
        $settings = [
            'app.min_version.android' => ['0.0.0', false, 'Oldest Android app version allowed to call the API.'],
            'app.min_version.ios' => ['0.0.0', false, 'Oldest iOS app version allowed to call the API.'],
            'support.contact' => [null, true, 'How users reach WHY ME support (DEC22). Shown in the apps.'],
        ];

        foreach ($settings as $key => [$value, $isPublic, $description]) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value, 'is_public' => $isPublic, 'description' => $description]);
        }
    }
}
