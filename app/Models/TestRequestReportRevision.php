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
        'generated_by',
    ];

    public static array $languages = [
        'en' => 'English',
        'ar' => 'Arabic (عربي)',
        'pt' => 'Portuguese (Português)',
    ];
}
