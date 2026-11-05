<?php

namespace Database\Seeders;

use App\Modules\Platform\Models\Language;
use App\Modules\Platform\Services\Languages;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * English is the default for everyone; Igbo is opt-in (I18N01, I18N02).
     */
    public function run(): void
    {
        Language::updateOrCreate(['code' => 'en'], ['name' => 'English', 'native_name' => 'English', 'enabled' => true, 'is_default' => true, 'sort' => 1]);
        Language::updateOrCreate(['code' => 'ig'], ['name' => 'Igbo', 'native_name' => 'Igbo', 'enabled' => true, 'is_default' => false, 'sort' => 2]);

        app(Languages::class)->forget();
    }
}
