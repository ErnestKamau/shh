<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestRequestReportLanguageFile extends Model
{
    protected $fillable = [
        'batch_id',
        'revision_no',
        'language',
        'report_url',
        'report_online_url',
    ];

    /**
     * @return array<string, string>
     */
    public static function languageLabels(): array
    {
        return TestRequestReportRevision::$languages;
    }

    public function label(): string
    {
        return self::languageLabels()[$this->language] ?? strtoupper($this->language);
    }
}
