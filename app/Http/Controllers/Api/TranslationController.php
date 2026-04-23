<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\System\TranslationLanguageLine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $lang = trim((string) $request->query('lang', ''));
        $lang = $lang !== '' ? strtolower($lang) : null;

        $lines = TranslationLanguageLine::query()
            ->select(['group', 'key', 'text'])
            ->orderBy('group')
            ->orderBy('key')
            ->get();

        $grouped = [];

        foreach ($lines as $line) {
            $group = (string) $line->group;
            $key = (string) $line->key;
            $text = is_array($line->text) ? $line->text : [];

            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }

            if ($lang !== null) {
                $grouped[$group][$key] = (string) ($text[$lang] ?? $text['en'] ?? '');
            } else {
                $grouped[$group][$key] = $text;
            }
        }

        return response()->json([
            'data' => $grouped,
            'meta' => [
                'lang' => $lang,
                'groups' => count($grouped),
                'keys' => $lines->count(),
            ],
        ]);
    }
}
