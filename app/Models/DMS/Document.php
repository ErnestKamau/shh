<?php

namespace App\Models\DMS;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $fillable = [
        'document_type_id',
        'document_number',
        'title',
        'description',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'owner_id',
        'created_by',
        'version_number',
        'amendment_count',
        'status',
        'approval_status',
        'approved_by',
        'approved_at',
        'is_archived',
        'archived_by',
        'archived_at',
        'archive_reason',
        'expiry_date',
        'is_expiring',
        'last_expiry_notification_sent_at',
        'tags',
    ];

    protected $casts = [
        'is_archived' => 'boolean',
        'is_expiring' => 'boolean',
        'tags' => 'array',
        'expiry_date' => 'date',
        'approved_at' => 'datetime',
        'archived_at' => 'datetime',
        'last_expiry_notification_sent_at' => 'datetime',
        'file_size' => 'integer',
        'version_number' => 'integer',
        'amendment_count' => 'integer',
    ];

    /**
     * Get the document type
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Get the owner of the document
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the creator of the document
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all versions of this document
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderBy('version_number', 'desc');
    }

    /**
     * Get all amendments for this document
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function amendments(): HasMany
    {
        return $this->hasMany(DocumentAmendment::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get permissions for this document
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function permissions()
    {
        return $this->morphMany(DocumentPermission::class, 'permissionable');
    }

    /**
     * Get the user who approved this document
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who archived this document
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function archiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    /**
     * Get notifications for this document
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(DocumentNotification::class);
    }

    /**
     * Scope to get only active documents
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope to get only archived documents
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * Scope to filter by status
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get expiring documents
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpiring($query)
    {
        return $query->where('is_expiring', true)
                     ->whereNotNull('expiry_date');
    }

    /**
     * Check if user can access this document with specified permission
     *
     * @param \App\User $user
     * @param string $permissionType
     * @return bool
     */
    public function canUserAccess($user, string $permissionType = 'view'): bool
    {
        // Check document-level permissions first
        $hasPermission = $this->permissions()
            ->where(function($query) use ($user) {
                $query->where(function($q) use ($user) {
                    $q->where('subject_type', User::class)
                      ->where('subject_id', $user->id);
                })->orWhere(function($q) use ($user) {
                    $q->where('subject_type', 'App\\Models\\Role')
                      ->whereIn('subject_id', $user->roles->pluck('id'));
                });
            })
            ->where('permission_type', $permissionType)
            ->exists();

        if ($hasPermission) {
            return true;
        }

        // Fall back to document type permissions
        return $this->documentType->permissions()
            ->where(function($query) use ($user) {
                $query->where(function($q) use ($user) {
                    $q->where('subject_type', User::class)
                      ->where('subject_id', $user->id);
                })->orWhere(function($q) use ($user) {
                    $q->where('subject_type', 'App\\Models\\Role')
                      ->whereIn('subject_id', $user->roles->pluck('id'));
                });
            })
            ->where('permission_type', $permissionType)
            ->exists();
    }

    /**
     * Increment the amendment count
     *
     * @return void
     */
    public function incrementAmendmentCount(): void
    {
        $this->increment('amendment_count');
        
        $amendmentLimit = $this->documentType->getInheritedAmendmentLimit();
        
        if ($this->amendment_count >= $amendmentLimit) {
            $this->createNewVersion('Amendment limit reached');
        }
    }

    /**
     * Create a new version of this document
     *
     * @param string $reason
     * @return DocumentVersion
     */
    public function createNewVersion(string $reason): DocumentVersion
    {
        $newVersionNumber = $this->version_number + 1;
        
        $version = DocumentVersion::create([
            'document_id' => $this->id,
            'version_number' => $newVersionNumber,
            'file_path' => $this->file_path,
            'file_name' => $this->file_name,
            'file_size' => $this->file_size,
            'mime_type' => $this->mime_type,
            'version_reason' => $reason,
            'created_by' => auth()->id(),
        ]);

        $this->update([
            'version_number' => $newVersionNumber,
            'amendment_count' => 0,
        ]);

        return $version;
    }

    /**
     * Archive this document
     *
     * @param string $reason
     * @return bool
     */
    public function archive(string $reason): bool
    {
        return $this->update([
            'is_archived' => true,
            'archived_by' => auth()->id(),
            'archived_at' => now(),
            'archive_reason' => $reason,
            'status' => 'archived',
        ]);
    }

    /**
     * Restore this document from archive
     *
     * @return bool
     */
    public function restore(): bool
    {
        return $this->update([
            'is_archived' => false,
            'archived_by' => null,
            'archived_at' => null,
            'archive_reason' => null,
            'status' => 'approved',
        ]);
    }

    /**
     * Approve this document
     *
     * @return bool
     */
    public function approve(): bool
    {
        return $this->update([
            'status' => 'approved',
            'approval_status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
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

    /**
     * Check if document is nearing expiry
     *
     * @param int $days
     * @return bool
     */
    public function isNearingExpiry(int $days = 30): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->diffInDays(now()) <= $days && $this->expiry_date->isFuture();
    }

    /**
     * Check if document is expired
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }

        return $this->expiry_date->isPast();
    }
}

