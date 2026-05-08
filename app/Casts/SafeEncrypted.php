<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * A graceful variant of Laravel's built-in `encrypted` cast.
 *
 * On decrypt failure it returns the raw value instead of throwing a
 * DecryptException, which would crash the page for rows that were stored
 * with a different APP_KEY or before encryption was introduced.
 *
 * Use the `lims:re-encrypt` Artisan command in gcla-api-s to fix the
 * underlying data so that every row can be decrypted cleanly.
 */
class SafeEncrypted implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable $e) {
            Log::warning('SafeEncrypted: could not decrypt', [
                'model' => $model::class,
                'key'   => $key,
                'error' => $e->getMessage(),
            ]);

            return $value;
        }
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return Crypt::encryptString((string) $value);
    }
}
