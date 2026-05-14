<?php

namespace App\Models\Equipments;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class VerificationLog extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;

	/**
	 * @var list<string>
	 */
	protected $fillable = [
		'equipment_id',
		'verification_date',
		'procedure',
		'reference_standard',
		'response',
		'remarks',
		'operator_id',
		'supplier_id',
		'maintainance_type',
		'edit_by',
		'is_delete',
	];
}
