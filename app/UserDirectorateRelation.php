<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserDirectorateRelation extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'user_directorate_relation';

    protected $fillable = [
        'user_id',
        'directorate_id',
    ];

    protected $casts = [
        'directorate_id' => 'string',
        'user_id' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function directorate()
    {
        return $this->belongsTo(Directorate::class, 'directorate_id');
    }
}
