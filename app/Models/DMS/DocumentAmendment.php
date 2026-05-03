<?php

namespace App\Models\DMS;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Gate;

class DocumentAmendment extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'document_id',
        'amendment_number',
        'amendment_reason',
        'amendment_description',
        'requested_by',
        'requested_at',
        'authorized_by',
        'authorized_at',
        'authorization_status',
        'authorization_comment',
        'amended_by',
        'amended_at',
        'file_before_path',
        'file_after_path',
        'approved_by',
        'approved_at',
        'approval_status',
        'approval_comment',
        'status',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'authorized_at' => 'datetime',
        'amended_at' => 'datetime',
        'approved_at' => 'datetime',
        'amendment_number' => 'integer',
    ];

    /**
     * Get the document this amendment belongs to
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user who requested the amendment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who authorized the amendment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    /**
     * Get the user who performed the amendment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function amender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'amended_by');
    }

    /**
     * Get the user who approved the amendment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope to get pending amendments
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'requested');
    }

    /**
     * Scope to get approved amendments
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get rejected amendments
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Authorize the amendment
     *
     * @param string|null $comment
     * @return bool
     */
    public function authorize(?string $comment = null): bool
    {
        return $this->update([
            'status' => 'authorized',
            'authorization_status' => 'approved',
            'authorized_by' => auth()->id(),
            'authorized_at' => now(),
            'authorization_comment' => $comment,
        ]);
    }

    /**
     * Approve the amendment
     *
     * @param string|null $comment
     * @return bool
     */
    public function approve(?string $comment = null): bool
    {
        return $this->update([
            'status' => 'approved',
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_comment' => $comment,
        ]);
    }

    /**
     * Reject the amendment
     *
     * @param string|null $comment
     * @param string $stage
     * @return bool
     */
    public function reject(?string $comment = null, string $stage = 'authorization'): bool
    {
        $data = [
            'status' => 'rejected',
        ];

        if ($stage === 'authorization') {
            $data['authorization_status'] = 'rejected';
            $data['authorized_by'] = auth()->id();
            $data['authorized_at'] = now();
            $data['authorization_comment'] = $comment;
        } else {
            $data['approval_status'] = 'rejected';
            $data['approved_by'] = auth()->id();
            $data['approved_at'] = now();
            $data['approval_comment'] = $comment;
        }

        return $this->update($data);
    }

    /**
     * Check if user can authorize this amendment
     *
     * @param \App\User $user
     * @return bool
     */
    public function canAuthorize($user): bool
    {
        return Gate::forUser($user)->allows('authorizeAmendment', $this->document);
    }

    /**
     * Check if user can approve this amendment
     *
     * @param \App\User $user
     * @return bool
     */
    public function canApprove($user): bool
    {
        return Gate::forUser($user)->allows('approveAmendment', $this->document);
    }
}

