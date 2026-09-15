<?php

namespace App;

use App\Casts\SafeEncrypted;
use App\Enums\CompanyCode;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Company extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'code',
        'name',
        'code',
        'logo',
        'favicon',
        'report_logo',
        'watermark',
        'location',
        'address',
        'country_id',
        'website',
        'license_key',
        'license_expiry',
        'active',
        'client_number',
        'email',
        'cell_phone',
        'telephone',
        'street',
        'fax',
        'po_box',
        'show_on_reports',
        'maintenance_start_year',
        'maintenance_start_month',
        'maintenance_end_year',
        'maintenance_end_month',
    ];

    protected $casts = [
        'name' => SafeEncrypted::class,
        'location' => SafeEncrypted::class,
        'address' => SafeEncrypted::class,
        'website' => SafeEncrypted::class,
        'license_key' => SafeEncrypted::class,
        'license_expiry' => SafeEncrypted::class,
        'client_number' => SafeEncrypted::class,
        'email' => SafeEncrypted::class,
        'cell_phone' => SafeEncrypted::class,
        'street' => SafeEncrypted::class,
        'fax' => SafeEncrypted::class,
        'po_box' => SafeEncrypted::class,
    ];

    /**
     * @param  string|CompanyCode  ...$codes
     */
    public function hasCode(string|CompanyCode ...$codes): bool
    {
        $current = $this->normalizedCode();
        if ($current === null) {
            return false;
        }

        foreach ($codes as $code) {
            $value = $code instanceof CompanyCode
                ? $code->value
                : strtolower(trim($code));

            if ($value !== '' && $current === $value) {
                return true;
            }
        }

        return false;
    }

    public function normalizedCode(): ?string
    {
        $code = strtolower(trim((string) ($this->code ?? '')));

        return $code === '' ? null : $code;
    }

    public function labs()
    {
        return $this->hasMany('App\Lab');
    }

    protected function code(): Attribute
    {
        return Attribute::make(
            set: function (?string $value): ?string {
                $normalized = strtolower(trim((string) $value));

                return $normalized === '' ? null : $normalized;
            },
        );
    }

    public function reportLogos()
    {
        return $this->hasMany(CompanyReportLogo::class);
    }

    public function getReportLogoPath(string $name): ?string
    {
        $logo = $this->reportLogos()->where('name', $name)->first();
        if ($logo) {
            return $logo->logo_path;
        }

        return $this->report_logo; // fallback
    }
}
