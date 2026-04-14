<?php

namespace App\Models\MethodSequences;

use App\Analyte;
use App\AnalysisMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class MethodSequence extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use HasFactory, SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name',
        'description',
        'analyte_id',
        'method_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the analyte for this method sequence.
     */
    public function analyte(): BelongsTo
    {
        return $this->belongsTo(Analyte::class);
    }

    /**
     * Get the analysis method for this method sequence.
     */
    public function method(): BelongsTo
    {
        return $this->belongsTo(AnalysisMethod::class, 'method_id');
    }

    /**
     * Get the versions for this method sequence.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(MethodSequenceVersion::class);
    }

    /**
     * Get the active version for this method sequence.
     */
    public function activeVersion(): HasOne
    {
        return $this->hasOne(MethodSequenceVersion::class)->where('is_active', true);
    }

    /**
     * Get the latest version for this method sequence.
     */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(MethodSequenceVersion::class)->latest('version_number');
    }
}

