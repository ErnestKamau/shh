<?php

namespace App\Models\AuditModule;

use App\Models\AuditModule\Concerns\ScopesAuditConfigurationForCompany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use OwenIt\Auditing\Contracts\Auditable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEmailTemplate extends Model implements Auditable
{
    use HasUuids;
    use ScopesAuditConfigurationForCompany;

    protected $keyType = 'string';
    public $incrementing = false;

    use \OwenIt\Auditing\Auditable;

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








