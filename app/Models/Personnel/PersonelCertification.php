<?php

namespace App\Models\Personnel;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class PersonelCertification extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'personnel_user_certifications';

    protected $fillable = [
        'user_id',
        'title',
        'certifying_body',
        'valid_from',
        'valid_to',
        'attachment_path',
        'created_by',
    ];
}
