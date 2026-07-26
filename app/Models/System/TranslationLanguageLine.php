<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Cache;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\TranslationLoader\LanguageLine;

class TranslationLanguageLine extends LanguageLine implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'language_lines';

    public function flushGroupCache(): void
    {
        static::flushGroupCacheForAllLocales((string) $this->group);
    }

    public static function flushGroupCacheForAllLocales(string $group): void
    {
        $locales = Language::query()->active()->pluck('code')->all();

        $locales = array_merge($locales, array_filter([
            config('app.locale'),
            config('app.fallback_locale'),
            'en',
            'sw',
            'pt',
            'ar',
        ]));

        $locales = array_values(array_unique(array_map(
            static fn ($locale): string => strtolower(trim((string) $locale)),
            $locales,
        )));

        foreach ($locales as $locale) {
            if ($locale === '') {
                continue;
            }

            Cache::forget(static::getCacheKey($group, $locale));
        }
    }
}
