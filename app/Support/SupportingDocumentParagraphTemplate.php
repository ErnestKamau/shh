<?php

namespace App\Support;

class SupportingDocumentParagraphTemplate
{
    /**
     * Split paragraph body into alternating static text and named placeholders from {{name}}.
     *
     * @return list<array{type: 'text'|'placeholder', content?: string, name?: string}>
     */
    public static function segments(?string $body): array
    {
        if ($body === null || $body === '') {
            return [['type' => 'text', 'content' => '']];
        }

        $parts = preg_split('/(\{\{\s*[^}]+\s*\}\})/', $body, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return [['type' => 'text', 'content' => $body]];
        }

        $out = [];
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            if (preg_match('/^\{\{\s*([^}]+?)\s*\}\}$/', $part, $m)) {
                $name = trim((string) $m[1]);
                if ($name !== '') {
                    $out[] = ['type' => 'placeholder', 'name' => $name];
                }

                continue;
            }

            $out[] = ['type' => 'text', 'content' => $part];
        }

        return $out !== [] ? $out : [['type' => 'text', 'content' => '']];
    }
}
