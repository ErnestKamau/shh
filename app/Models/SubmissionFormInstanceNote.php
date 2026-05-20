<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionFormInstanceNote extends Model
{
    use HasUuids;

    public const VISIBILITY_INTERNAL = 'internal';

    public const VISIBILITY_PUBLIC = 'public';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'submission_form_instance_id',
        'body',
        'visibility',
        'created_by',
    ];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(SubmissionFormInstance::class, 'submission_form_instance_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isPublic(): bool
    {
        return $this->visibility === self::VISIBILITY_PUBLIC;
    }
}
