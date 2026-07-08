<?php

namespace App\Models\Registry;

use App\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistryRequestAction extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'registry_request_id',
        'action_type',
        'from_stage',
        'to_stage',
        'comment',
        'payload',
        'performed_by',
        'performed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'performed_at' => 'datetime',
        ];
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(RegistryRequest::class, 'registry_request_id');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function displayComment(): ?string
    {
        $comment = $this->comment;

        if (! is_string($comment) || $comment === '') {
            return $comment;
        }

        if (preg_match('/^Assigned to user #([0-9a-fA-F-]{36})$/', $comment, $matches) !== 1) {
            return $comment;
        }

        $userName = User::query()->whereKey($matches[1])->value('name');

        return $userName !== null
            ? 'Assigned to ' . $userName
            : $comment;
    }
}
