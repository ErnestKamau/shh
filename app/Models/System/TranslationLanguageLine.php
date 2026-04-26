<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;
use Spatie\TranslationLoader\LanguageLine;

class TranslationLanguageLine extends LanguageLine implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $table = 'language_lines';
}
