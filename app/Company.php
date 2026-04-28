<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Company extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;

    protected $casts = [
        'name' => 'encrypted',
        'location' => 'encrypted',
        'address' => 'encrypted',
        'website' => 'encrypted',
        'license_key' => 'encrypted',
        'license_expiry' => 'encrypted',
        'client_number' => 'encrypted',
        'email' => 'encrypted',
        'cell_phone' => 'encrypted',
        'street' => 'encrypted',
        'fax' => 'encrypted',
    ];

  public function labs(){
    return $this->hasMany('App\Lab');
  }
}
