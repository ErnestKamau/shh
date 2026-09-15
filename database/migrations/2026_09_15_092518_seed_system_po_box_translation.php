<?php

use App\Models\System\TranslationLanguageLine;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        TranslationLanguageLine::updateOrCreate(
            [
                'group' => 'system',
                'key' => 'po_box',
            ],
            [
                'text' => [
                    'en' => 'PO Box',
                    'sw' => 'Sanduku la Posta',
                ],
            ]
        );

        TranslationLanguageLine::flushGroupCacheForAllLocales('system');
    }

    public function down(): void
    {
        TranslationLanguageLine::query()
            ->where('group', 'system')
            ->where('key', 'po_box')
            ->delete();

        TranslationLanguageLine::flushGroupCacheForAllLocales('system');
    }
};
