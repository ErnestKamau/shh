<?php

namespace App\Support;

class TranslationImportHeaders
{
    /**
     * @param array<int, string> $headers
     * @return array<int, string>
     */
    public static function normalize(array $headers): array
    {
        return array_map(
            fn (string $header): string => static::normalizeHeader($header),
            $headers
        );
    }

    public static function normalizeHeader(string $header): string
    {
        $header = strtolower(trim($header));

        if (in_array($header, ['module', 'modulo', 'group'], true)) {
            return 'group';
        }

        if (in_array($header, ['key', 'slug', 'translation_key'], true)) {
            return 'key';
        }

        if (preg_match('/\(([a-z]{2})\)$/', $header, $matches) === 1) {
            return $matches[1];
        }

        return match ($header) {
            'english' => 'en',
            'portuguese' => 'pt',
            'arabic' => 'ar',
            'swahili' => 'sw',
            default => $header,
        };
    }
}
