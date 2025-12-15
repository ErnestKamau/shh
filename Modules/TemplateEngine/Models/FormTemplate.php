<?php

namespace Modules\TemplateEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\User;

class FormTemplate extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function sections(): HasMany
    {
        return $this->hasMany(TemplateSection::class)->orderBy('order_index');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('order_index');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(TemplateSubmission::class);
    }
    
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function process()
    {
        return $this->morphTo();
    }
}
