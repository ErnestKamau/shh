<?php

namespace App\Models\Assets;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AssetLocation extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $fillable = ['location_code', 'name', 'is_active'];
}
