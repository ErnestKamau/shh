<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable;

class InventoryDepartment extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;

	protected $fillable = [
		'name',
		'module',
		'company_id',
		'location_id',
		'active',
		'department_head_id',
	];

	public function inventory_items(){
		return $this->hasMany('App\InventoryItem');
	}

	public function users(): BelongsToMany
	{
		return $this->belongsToMany(
			User::class,
			'inventory_department_users',
			'inventory_department_id',
			'user_id'
		)->withTimestamps();
	}
}
