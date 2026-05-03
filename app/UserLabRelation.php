<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class UserLabRelation extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $table = 'user_lab_relation';

    protected $fillable = [
        'user_id',
        'lab_id',
    ];

    protected $casts = [
        'lab_id' => 'string',
        'user_id' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function lab()
    {
        return $this->belongsTo(Lab::class, 'lab_id');
    }
}
