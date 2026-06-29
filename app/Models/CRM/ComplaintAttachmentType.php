<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class ComplaintAttachmentType extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'complaint_attachment_types';

    protected $fillable = [
        'name',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];
}
