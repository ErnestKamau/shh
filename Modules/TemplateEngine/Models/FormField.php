<?php

namespace Modules\TemplateEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FormField extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    
    protected $casts = [
        'meta' => 'array',
        'validation_rules' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(TemplateSection::class, 'section_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(FormFieldOption::class)->orderBy('order_index');
    }

    public function datasetBinding(): HasOne
    {
        return $this->hasOne(TemplateDatasetBinding::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'parent_field_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(FormField::class, 'parent_field_id')->orderBy('order_index');
    }
}
