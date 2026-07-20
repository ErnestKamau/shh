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
 * Also unwraps nested encryption from accidental double-encrypt save cycles,
 * and avoids re-encrypting values that are already valid ciphertext.
 */
class SafeEncrypted implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (! is_string($value) || ! str_starts_with($value, 'eyJ')) {
            return $value;
        }

        $current = $value;
        $guard = 0;

        while (
            is_string($current)
            && $current !== ''
            && str_starts_with($current, 'eyJ')
            && $guard < 30
        ) {
            try {
                $next = Crypt::decryptString($current);
            } catch (Throwable $e) {
                if ($guard === 0) {
                    Log::warning('SafeEncrypted: could not decrypt', [
                        'model' => $model::class,
                        'key'   => $key,
                        'error' => $e->getMessage(),
                    ]);
                }

                break;
            }

            if ($next === $current) {
                break;
            }

            $current = $next;
            $guard++;
        }

        return $current;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $stringValue = (string) $value;

        // Avoid double-encrypting an already-encrypted payload.
        if (str_starts_with($stringValue, 'eyJ')) {
            try {
                Crypt::decryptString($stringValue);

                return $stringValue;
            } catch (Throwable) {
                // Not valid ciphertext — treat as plaintext and encrypt below.
            }
        }

        return Crypt::encryptString($stringValue);
    }
}
