<?php

namespace App\Models\Equipments;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class EquipmentDisposalFile extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'disposal_id',
        'file_path',
        'file_name',
        'file_type',
        'mime_type',
        'file_size',
        'description',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the disposal this file belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function disposal(): BelongsTo
    {
        return $this->belongsTo(EquipmentDisposal::class, 'disposal_id');
    }

    /**
     * Get the user who uploaded this file
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scope to filter by file type
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $fileType
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $fileType)
    {
        return $query->where('file_type', $fileType);
    }
}

