<?php

use App\Models\System\TranslationLanguageLine;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $translations = [
            'payment_method' => [
                'en' => 'Payment Type',
                'sw' => 'Aina ya Malipo',
                'pt' => 'Tipo de Pagamento',
            ],
            'select_payment_method' => [
                'en' => 'Select payment type...',
                'sw' => 'Chagua aina ya malipo...',
                'pt' => 'Selecione o tipo de pagamento...',
            ],
            'payment_terms_note' => [
                'en' => 'Payment Terms Note',
                'sw' => 'Maelezo ya Malipo',
                'pt' => 'Observação das Condições de Pagamento',
            ],
            'payment_terms_note_placeholder' => [
                'en' => 'Describe custom payment terms...',
                'sw' => 'Eleza masharti maalum ya malipo...',
                'pt' => 'Descreva as condições de pagamento personalizadas...',
            ],
            'customer_logo' => [
                'en' => 'Customer Logo',
                'sw' => 'Nembo ya Mteja',
                'pt' => 'Logo do Cliente',
            ],
            'customer_logo_help' => [
                'en' => 'Optional. JPG, PNG, GIF, WEBP, or SVG up to 5 MB.',
                'sw' => 'Si lazima. JPG, PNG, GIF, WEBP, au SVG hadi MB 5.',
                'pt' => 'Opcional. JPG, PNG, GIF, WEBP ou SVG até 5 MB.',
            ],
            'logo_removed' => [
                'en' => 'Logo removed.',
                'sw' => 'Nembo imeondolewa.',
                'pt' => 'Logo removido.',
            ],
        ];

        foreach ($translations as $key => $text) {
            TranslationLanguageLine::updateOrCreate(
                [
                    'group' => 'crm',
                    'key' => $key,
                ],
                [
                    'text' => $text,
                ]
            );
        }

        TranslationLanguageLine::flushGroupCacheForAllLocales('crm');
    }

    public function down(): void
    {
        TranslationLanguageLine::query()
            ->where('group', 'crm')
            ->whereIn('key', [
                'payment_method',
                'select_payment_method',
                'payment_terms_note',
                'payment_terms_note_placeholder',
            ])
            ->delete();

        TranslationLanguageLine::flushGroupCacheForAllLocales('crm');
    }
};
