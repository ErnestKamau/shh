<?php

namespace Modules\TemplateEngine\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\User;

class TemplateSubmission extends Model
{
    protected $guarded = ['id'];
    protected $table = 'form_template_submissions';

    protected $casts = [
        'data' => 'array',
        'meta' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(FormTemplate::class, 'form_template_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
