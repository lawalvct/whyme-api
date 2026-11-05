<?php

namespace App\Modules\Platform\Services;

use App\Modules\Platform\Models\Language;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Languages
{
    private const CACHE_KEY = 'platform:languages';

    /**
     * @return Collection<int, Language>
     */
    public function enabled(): Collection
    {
        return Cache::remember(self::CACHE_KEY, 300, fn () => Language::where('enabled', true)->orderBy('sort')->get());
    }

    /**
     * @return list<string>
     */
    public function enabledCodes(): array
    {
        $codes = $this->enabled()->pluck('code')->all();

        return $codes === [] ? [config('app.fallback_locale')] : $codes;
    }

    public function isEnabled(?string $code): bool
    {
        return $code !== null && in_array($code, $this->enabledCodes(), true);
    }

    public function default(): string
    {
        return $this->enabled()->firstWhere('is_default', true)->code ?? config('app.fallback_locale');
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
