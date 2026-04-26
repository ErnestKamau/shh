<?php

namespace App\Models\System;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'code',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Language $language): void {
            $language->code = strtolower(trim((string) $language->code));

            if ($language->is_default) {
                static::query()
                    ->where('id', '!=', $language->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);

                $language->is_active = true;
            }
        });
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function canBeDeleted(): bool
    {
        return !$this->is_default;
    }
}
