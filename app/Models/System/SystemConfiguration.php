<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SystemConfiguration extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'value' => 'encrypted',
    ];

	use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'configuration_type_id',
        'value',
        'key',
        'status',
    ];

    protected $table = 'system_configurations';

    /**
     * Decrypt legacy/plaintext values that predate the encrypted cast.
     *
     * Also unwraps nested encryption from repeated save cycles where an
     * already-encrypted payload was encrypted again.
     */
    public function fromEncryptedString($value)
    {
        try {
            $decrypted = parent::fromEncryptedString($value);
        } catch (\Throwable) {
            return $value;
        }

        $guard = 0;
        while (
            is_string($decrypted)
            && $decrypted !== ''
            && str_starts_with($decrypted, 'eyJ')
            && $guard < 30
        ) {
            try {
                $next = parent::fromEncryptedString($decrypted);
            } catch (\Throwable) {
                break;
            }

            if ($next === $decrypted) {
                break;
            }

            $decrypted = $next;
            $guard++;
        }

        return $decrypted;
    }
}
