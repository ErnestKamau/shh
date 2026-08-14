<?php

namespace App;

use App\Concerns\DefaultsActiveOnCreate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class StandardValue extends Model implements Auditable
{
    use DefaultsActiveOnCreate;
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
    protected $table = 'standard_values';
    protected $fillable = [
        'name', 'code', 'status', 'edited_by'
    ];

    protected function activeDefaultColumn(): string
    {
        return 'status';
    }
}
