<?php

namespace Modules\TemplateEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\User;

class FormTemplateVariable extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'config' => 'array',
        'validation_rules' => 'array',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
