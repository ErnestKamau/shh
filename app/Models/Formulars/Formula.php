<?php

namespace App\Models\Formulars;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Formula extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the formula versions for this formula.
     */
    public function formulaVersions(): HasMany
    {
        return $this->hasMany(FormulaVersion::class);
    }

    /**
     * Get the active formula version.
     */
    public function activeVersion(): HasOne
    {
        return $this->hasOne(FormulaVersion::class)->where('is_active', true);
    }

    /**
     * Get the latest formula version.
     */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(FormulaVersion::class)->latest('version_number');
    }
}
