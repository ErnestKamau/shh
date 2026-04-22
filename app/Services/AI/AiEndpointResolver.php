<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;

class AiEndpointResolver
{
    /** @var string[]|null */
    private static ?array $cachedCandidates = null;

    /** @var string|null */
    private static ?string $cachedResolved = null;

    /**
     * Resolve the best reachable AI base URL.
     *
     * Resolution order:
        * 1) Explicit env/config URLs (IMARA_AI_ENDPOINT, AI_SERVICE_URL, AI_INFERENCE_URL, AI_API_URL)
     * 2) Config fallback list (imara_ai.api_base_urls)
     * 3) Hard fallback: 8080 then 8081
     */
    public static function resolve(?string $preferred = null): string
    {
        if ($preferred) {
            return rtrim($preferred, '/');
        }

        if (self::$cachedResolved !== null) {
            return self::$cachedResolved;
        }

        $candidates = self::candidates();
        foreach ($candidates as $baseUrl) {
            if (self::isReachable($baseUrl)) {
                self::$cachedResolved = $baseUrl;
                return $baseUrl;
            }
        }

        self::$cachedResolved = $candidates[0] ?? 'http://127.0.0.1:8081';
        return self::$cachedResolved;
    }

    /**
     * @return string[]
     */
    public static function candidates(): array
    {
        if (self::$cachedCandidates !== null) {
            return self::$cachedCandidates;
        }

        $urls = [];

        $fromEnv = [
            env('IMARA_AI_ENDPOINT'),
            env('AI_SERVICE_URL'),
            env('AI_INFERENCE_URL'),
            env('AI_API_URL'),
        ];

        foreach ($fromEnv as $url) {
            if (!empty($url)) {
                $urls[] = rtrim((string) $url, '/');
            }
        }

        $fromConfig = config('imara_ai.api_base_urls', []);
        if (is_array($fromConfig)) {
            foreach ($fromConfig as $url) {
                if (!empty($url)) {
                    $urls[] = rtrim((string) $url, '/');
                }
            }
        }

        $urls[] = 'http://127.0.0.1:8080';
        $urls[] = 'http://127.0.0.1:8081';

        $seen = [];
        $deduped = [];
        foreach ($urls as $url) {
            if (!isset($seen[$url])) {
                $seen[$url] = true;
                $deduped[] = $url;
            }
        }

        self::$cachedCandidates = $deduped;
        return self::$cachedCandidates;
    }

    private static function isReachable(string $baseUrl): bool
    {
        try {
            $live = Http::timeout(2)->connectTimeout(1)->get($baseUrl . '/health/live');
            if ($live->successful()) {
                return true;
            }

            $ready = Http::timeout(2)->connectTimeout(1)->get($baseUrl . '/health/ready');
            if ($ready->successful()) {
                return true;
            }

            $legacy = Http::timeout(2)->connectTimeout(1)->get($baseUrl . '/health');
            return $legacy->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
