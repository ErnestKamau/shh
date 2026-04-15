<?php

namespace App\Models\AI;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $connection = 'pgsql_ai';
    protected $table = 'ai.ai_settings';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    /**
     * Get a setting by key, with optional default.
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::find($key);
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a setting.
     */
    public static function set(string $key, $value, ?string $description = null)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'description' => $description]
        );
    }
}
