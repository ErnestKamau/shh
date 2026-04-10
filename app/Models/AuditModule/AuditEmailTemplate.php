<?php

namespace App\Models\AuditModule;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEmailTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'template_code',
        'name',
        'subject',
        'body',
        'variables',
        'notification_type_id',
        'is_active',
        'company_id',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function notificationType(): BelongsTo
    {
        return $this->belongsTo(AuditNotificationType::class, 'notification_type_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForCompany($query)
    {
        $companyId = getUserCompany() ?? 0;
        return $query->where(function($q) use ($companyId) {
            $q->where('company_id', $companyId)->orWhere('company_id', 0);
        });
    }

    /**
     * Render template with variables
     */
    public function render(array $variables = []): array
    {
        $subject = $this->subject;
        $body = $this->body;

        // Replace placeholders in subject and body
        foreach ($variables as $key => $value) {
            $placeholder = '{{' . $key . '}}';
            $subject = str_replace($placeholder, $value, $subject);
            $body = str_replace($placeholder, $value, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }
}








