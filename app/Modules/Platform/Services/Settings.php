<?php

namespace App\Modules\Platform\Services;

use App\Modules\Platform\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Owner-editable configuration (prices, limits, copy, min app versions).
 * Cached briefly; writes clear the cache so changes apply without a release.
 */
class Settings
{
    private const CACHE_KEY = 'platform:settings';

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key]['value'] ?? $default;
    }

    public function set(string $key, mixed $value, ?string $description = null, ?bool $isPublic = null): void
    {
        $setting = Setting::firstOrNew(['key' => $key]);
        $setting->value = $value;
        $setting->description = $description ?? $setting->description;
        $setting->is_public = $isPublic ?? $setting->is_public ?? false;
        $setting->save();
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<string, mixed>
     */
    public function public(): array
    {
        return collect($this->all())->filter(fn ($s) => $s['is_public'])->map(fn ($s) => $s['value'])->all();
    }

    /**
     * @return array<string, array{value: mixed, is_public: bool}>
     */
    private function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 60, fn () => Setting::all()
            ->mapWithKeys(fn (Setting $s) => [$s->key => ['value' => $s->value, 'is_public' => $s->is_public]])
            ->all());
    }
}
