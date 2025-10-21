<?php

namespace App\Models\DMS;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DocumentVersion extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'document_id',
        'version_number',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'version_reason',
        'created_by',
        'created_at',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'file_size' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the document this version belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user who created this version
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the file path
     *
     * @return string
     */
    public function getFilePath(): string
    {
        return Storage::disk('dms')->path($this->file_path);
    }

    /**
     * Get the file URL
     *
     * @return string
     */
    public function getFileUrl(): string
    {
        return Storage::disk('dms')->url($this->file_path);
    }
}

