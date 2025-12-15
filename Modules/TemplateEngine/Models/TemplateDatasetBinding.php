<?php

namespace Modules\TemplateEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateDatasetBinding extends Model
{
    protected $guarded = ['id'];
    protected $table = 'form_template_dataset_bindings';

    protected $casts = [
        'filters' => 'array',
    ];

    public function field(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'form_field_id');
    }
}
