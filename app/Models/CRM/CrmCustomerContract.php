<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class CrmCustomerContract extends Model implements Auditable
{
    use HasUuids;
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $table = 'crm_customer_contracts';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_to' => 'date',
            'is_current' => 'boolean',
            'file_size' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CRMCustomer::class, 'crm_customer_id');
    }

    public function hasFile(): bool
    {
        return filled($this->file_path);
    }

    public function isPreviewable(): bool
    {
        $extension = strtolower((string) ($this->file_extension ?: pathinfo((string) $this->original_name, PATHINFO_EXTENSION)));
        $mime = strtolower((string) $this->mime_type);

        if (in_array($extension, ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp'], true)) {
            return true;
        }

        return str_starts_with($mime, 'image/') || $mime === 'application/pdf';
    }

    public function isImagePreview(): bool
    {
        $extension = strtolower((string) ($this->file_extension ?: pathinfo((string) $this->original_name, PATHINFO_EXTENSION)));
        $mime = strtolower((string) $this->mime_type);

        return in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp'], true)
            || str_starts_with($mime, 'image/');
    }
}
