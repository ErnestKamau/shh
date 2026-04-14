<?php

namespace App\Models\MethodSequences;

use OwenIt\Auditing\Contracts\Auditable;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MethodSequenceVersion extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'method_sequence_id',
        'version_number',
        'is_active',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the method sequence that owns this version.
     */
    public function methodSequence(): BelongsTo
    {
        return $this->belongsTo(MethodSequence::class);
    }

    /**
     * Get the user who created this version.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved this version.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the stages for this version.
     */
    public function stages(): HasMany
    {
        return $this->hasMany(MethodSequenceStage::class)->orderBy('order');
    }
}

