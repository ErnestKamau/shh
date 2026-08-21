<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use OwenIt\Auditing\Contracts\Auditable;

class UnitOfMeasureConversion extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;
    protected $table = 'unit_of_measure_conversions';

    protected $fillable = [
        'uom1',
        'uom2',
        'conversion',
        'material_type_id',
        'location_id',
        'description',
    ];
}
