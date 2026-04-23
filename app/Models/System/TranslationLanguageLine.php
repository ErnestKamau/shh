<?php

namespace App\Models\System;

use OwenIt\Auditing\Contracts\Auditable;
use Spatie\TranslationLoader\LanguageLine;

class TranslationLanguageLine extends LanguageLine implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'language_lines';
}
