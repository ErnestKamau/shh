<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class DocumentPublication extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use SoftDeletes, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'document_id',
        'publish_scope',
        'publish_targets',
        'published_by',
        'published_at',
        'is_active'
    ];

    protected $casts = [
        'publish_targets' => 'array',
        'published_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function publisher()
    {
        return $this->belongsTo(\App\User::class, 'published_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByScope($query, $scope)
    {
        return $query->where('publish_scope', $scope);
    }

    public function scopeByTarget($query, $targetId, $scope)
    {
        return $query->where('publish_scope', $scope)
                    ->whereJsonContains('publish_targets', (string)$targetId);
    }
}
