<?php

namespace App;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CompanyReportLogo extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'company_id',
        'name',
        'logo_path',
        'position_vertical',
        'position_horizontal',
        'show_on_every_page',
        'report_type',
    ];

    protected $casts = [
        'show_on_every_page' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
