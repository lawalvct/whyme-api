<?php

namespace App\Modules\Platform\Services;

use App\Modules\Platform\Models\FeatureFlag;
use Illuminate\Support\Facades\Cache;

/**
 * On/off switches such as auto_release_enabled and paid_enquiries_enabled.
 * Unknown flags are off, so a missing row can never switch something on.
 */
class Features
{
    private const CACHE_KEY = 'platform:features';

    public function enabled(string $key): bool
    {
        return $this->all()[$key]['enabled'] ?? false;
    }

    public function set(string $key, bool $enabled): void
    {
        FeatureFlag::updateOrCreate(['key' => $key], ['enabled' => $enabled]);
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, bool>
     */
    public function public(): array
    {
        return collect($this->all())->filter(fn ($f) => $f['is_public'])->map(fn ($f) => $f['enabled'])->all();
    }

    /**
     * @return array<string, array{enabled: bool, is_public: bool}>
     */
    private function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 60, fn () => FeatureFlag::all()
            ->mapWithKeys(fn (FeatureFlag $f) => [$f->key => ['enabled' => $f->enabled, 'is_public' => $f->is_public]])
            ->all());
    }
}
