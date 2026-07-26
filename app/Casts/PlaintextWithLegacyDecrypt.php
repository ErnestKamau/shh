<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * For columns that must remain plaintext (e.g. users.name used in views/display)
 * but may already contain SafeEncrypted ciphertext from a prior encrypt cast.
 *
 * Reads decrypt legacy ciphertext; writes always store plaintext.
 */
class PlaintextWithLegacyDecrypt implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return (new SafeEncrypted)->get($model, $key, $value, $attributes);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $stringValue = (string) $value;

        // If a caller passes ciphertext, unwrap so we persist readable plaintext.
        if (str_starts_with($stringValue, 'eyJ')) {
            try {
                $stringValue = Crypt::decryptString($stringValue);
            } catch (Throwable $e) {
                Log::warning('PlaintextWithLegacyDecrypt: could not decrypt on write', [
                    'model' => $model::class,
                    'key' => $key,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $stringValue;
    }
}
