<?php

namespace Modules\TemplateEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateSection extends Model
{
    protected $guarded = ['id'];
    protected $table = 'form_template_sections';

    public function template(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class, 'section_id')->orderBy('order_index');
    }
}
