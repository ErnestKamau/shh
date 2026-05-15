<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentAttachment extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'equipment_attachments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'equipment_id',
        'title',
        'description',
        'attachment',
        'upload_by',
        'edit_by',
    ];
}
