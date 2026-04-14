<?php

namespace App\Models\DMS;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentType extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'name',
        'code',
        'description',
        'parent_id',
        'numbering_format',
        'amendment_limit',
        'is_active',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'amendment_limit' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the parent document type
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class, 'parent_id');
    }

    /**
     * Get the children document types
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(DocumentType::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get all documents of this type
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get permissions for this document type
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function permissions()
    {
        return $this->morphMany(DocumentPermission::class, 'permissionable');
    }

    /**
     * Get approval workflows for this document type
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function approvalWorkflows(): HasMany
    {
        return $this->hasMany(DocumentApprovalWorkflow::class);
    }

    /**
     * Get the user who created this type
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the inherited numbering format
     *
     * @return string
     */
    public function getInheritedNumberingFormat(): string
    {
        if ($this->numbering_format) {
            return $this->numbering_format;
        }

        if ($this->parent) {
            return $this->parent->getInheritedNumberingFormat();
        }

        return '{TYPE_CODE}-{YEAR}-{SEQ}';
    }

    /**
     * Get the inherited amendment limit
     *
     * @return int
     */
    public function getInheritedAmendmentLimit(): int
    {
        if ($this->amendment_limit !== null) {
            return $this->amendment_limit;
        }

        if ($this->parent) {
            return $this->parent->getInheritedAmendmentLimit();
        }

        return 5;
    }

    /**
     * Get the full path of this document type for breadcrumb trail
     *
     * @return string
     */
    public function getFullPath(): string
    {
        $path = [$this->name];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent->name);
            $parent = $parent->parent;
        }

        return implode(' > ', $path);
    }

    /**
     * Get all ancestor document types
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAncestors()
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    /**
     * Scope to get only active types
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get root types (no parent)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }
}

