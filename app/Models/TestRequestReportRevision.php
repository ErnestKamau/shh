<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestRequestReportRevision extends Model
{
    protected $fillable = [
        'batch_id',
        'revision_no',
        'language',
        'notes',
        'report_url',
        'report_online_url',
        'generated_by',
    ];

    public static array $languages = [
        'en' => 'English',
        'ar' => 'Arabic (عربي)',
        'pt' => 'Portuguese (Português)',
    ];

    public function storedFileUrl(): ?string
    {
        $online = trim((string) ($this->report_online_url ?? ''));
        if ($online !== '') {
            return $online;
        }

        $relative = trim((string) ($this->report_url ?? ''));
        if ($relative === '') {
            return null;
        }

        return url('/storage'.$relative);
    }
}
