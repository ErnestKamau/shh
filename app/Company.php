<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Casts\SafeEncrypted;

class Company extends Model implements Auditable
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

	use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'code',
        'name',
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
    ];

    /**
     * Normalize a business code: trim, uppercase, collapse separators.
     */
    public static function normalizeCode(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        $normalized = strtoupper(trim($code));
        $normalized = preg_replace('/[^A-Z0-9_-]+/', '-', $normalized) ?? '';
        $normalized = trim($normalized, '-_');

        return $normalized === '' ? null : $normalized;
    }

    public static function findByCode(string $code): ?self
    {
        $normalized = self::normalizeCode($code);
        if ($normalized === null) {
            return null;
        }

        return self::query()->where('code', $normalized)->first();
    }

    public function setCodeAttribute(?string $value): void
    {
        $this->attributes['code'] = self::normalizeCode($value);
    }

    public function labs()
    {
        return $this->hasMany('App\Lab');
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
