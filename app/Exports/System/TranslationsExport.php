<?php

namespace App\Exports\System;

use App\Models\System\Language;
use App\Models\System\TranslationLanguageLine;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TranslationsExport implements FromCollection, WithHeadings
{
    /** @var array<int, string> */
    private array $languageCodes;

    public function __construct(?array $languageCodes = null)
    {
        $this->languageCodes = $languageCodes ?? Language::query()->active()->orderBy('name')->pluck('code')->all();
    }

    public function collection(): Collection
    {
        return TranslationLanguageLine::query()
            ->orderBy('group')
            ->orderBy('key')
            ->get()
            ->map(function (TranslationLanguageLine $line): array {
                $row = [
                    'group' => (string) $line->group,
                    'key' => (string) $line->key,
                ];

                $text = is_array($line->text) ? $line->text : [];

                foreach ($this->languageCodes as $code) {
                    $row[$code] = (string) ($text[$code] ?? '');
                }

                return $row;
            });
    }

    public function headings(): array
    {
        $languageHeaders = Language::query()
            ->active()
            ->orderBy('name')
            ->get()
            ->map(fn (Language $language): string => $language->name . ' (' . $language->code . ')')
            ->all();

        return array_merge(['Module', 'Key'], $languageHeaders);
    }
}
