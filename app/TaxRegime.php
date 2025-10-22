<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class TaxRegime extends Model implements Auditable
{
	use \OwenIt\Auditing\Auditable;
    
    protected $table = 'tax_regime';
    
    protected $fillable = [
        'registered_by',
        'value',
        'active',
        'end_date',
    ];
    
    protected $casts = [
        'active' => 'boolean',
        'end_date' => 'datetime',
    ];
    
    /**
     * Get the user who registered this tax regime.
     */
    public function registeredBy()
    {
        return $this->belongsTo(\App\User::class, 'registered_by');
    }
}
