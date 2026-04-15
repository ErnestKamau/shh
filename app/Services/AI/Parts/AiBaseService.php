<?php

namespace App\Services\AI\Parts;

use Illuminate\Support\Facades\Log;
use App\Models\AI\AiSetting;

abstract class AiBaseService
{
    protected string $apiBaseUrl;
    protected string $connection = 'pgsql_ai';

    public function __construct()
    {
        $this->apiBaseUrl = rtrim(config('imara_ai.api_base_url'), '/');
    }

    /**
     * Get an AI-specific setting from the database with fallbacks.
     */
    public function getSetting(string $key, $default = null)
    {
        $dbValue = AiSetting::get($key);
        if ($dbValue !== null) {
            return $dbValue;
        }

        $configKey = "imara_ai.defaults.{$key}";
        if (config()->has($configKey)) {
            return config($configKey);
        }

        $legacyKey = "imara_ai.{$key}";
        if (config()->has($legacyKey)) {
            return config($legacyKey);
        }

        return $default;
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        Log::channel('stack')->$level("AI_SERVICE: {$message}", $context);
    }
}
