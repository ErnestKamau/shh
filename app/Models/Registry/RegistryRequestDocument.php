<?php

namespace App\Models\Registry;

use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistryRequestDocument extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'registry_request_id',
        'original_name',
        'stored_path',
        'mime_type',
        'file_size',
        'version',
        'uploaded_by',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(RegistryRequest::class, 'registry_request_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
