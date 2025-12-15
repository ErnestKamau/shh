<?php

namespace App\Models\Assets;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class AssetType extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    protected $fillable = ['asset_code', 'descripton', 'is_active'];
}
