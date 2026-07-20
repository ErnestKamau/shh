<?php

namespace App\Models\QcModule\Configurations;

use App\AnalysisMethod;
use App\Models\QcModule\QcSchemeBinding;
use App\Models\QcModule\QcSchemeRule;
use App\Standards;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class QcSchemes extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [];
    protected $table = 'qc_scheme';

    public function standards(): BelongsToMany
    {
        return $this->belongsToMany(
            Standards::class,
            'standard_qc_scheme',
            'qc_scheme_id',
            'standard_id'
        )->withTimestamps();
    }

    public function analysisMethods(): BelongsToMany
    {
        return $this->belongsToMany(
            AnalysisMethod::class,
            'method_qc_scheme',
            'qc_scheme_id',
            'method_id'
        )->withTimestamps();
    }

    public function bindings(): HasMany
    {
        return $this->hasMany(QcSchemeBinding::class, 'qc_scheme_id');
    }

    public function rules(): HasMany
    {
        return $this->hasMany(QcSchemeRule::class, 'qc_scheme_id')->orderBy('sort_order');
    }
}
