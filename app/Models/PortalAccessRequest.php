<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class PortalAccessRequest extends Model
{
    protected $fillable = [
        'full_name_or_organisation_enc',
        'address_enc',
        'zone',
        'tin_number_enc',
        'email_enc',
        'phone_number_enc',
        'postal_code',
        'request_ip_enc',
        'user_agent_enc',
        'status',
        'review_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function getFullNameOrOrganisationAttribute(): ?string
    {
        return $this->decryptOrPlain($this->getAttribute('full_name_or_organisation_enc'));
    }

    public function getAddressAttribute(): ?string
    {
        return $this->decryptOrPlain($this->getAttribute('address_enc'));
    }

    public function getTinNumberAttribute(): ?string
    {
        return $this->decryptOrPlain($this->getAttribute('tin_number_enc'));
    }

    public function getEmailAttribute(): ?string
    {
        return $this->decryptOrPlain($this->getAttribute('email_enc'));
    }

    public function getPhoneNumberAttribute(): ?string
    {
        return $this->decryptOrPlain($this->getAttribute('phone_number_enc'));
    }

    public function getRequestIpAttribute(): ?string
    {
        return $this->decryptOrPlain($this->getAttribute('request_ip_enc'));
    }

    public function getUserAgentAttribute(): ?string
    {
        return $this->decryptOrPlain($this->getAttribute('user_agent_enc'));
    }

    private function decryptOrPlain($value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable $e) {
            return $value;
        }
    }
}
