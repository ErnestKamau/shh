<?php

namespace App\Http\Resources\Portal;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Represents a CRM contact's profile as returned by the portal API.
 * Constructed manually — not extending the standard $resource pattern —
 * because we need both the User and the CustomerContact records.
 */
class ContactProfileResource extends JsonResource
{
    protected $user;
    protected $contact;

    public function __construct($user, $contact)
    {
        parent::__construct($user);
        $this->user    = $user;
        $this->contact = $contact;
    }

    public function toArray($request): array
    {
        $customer = $this->contact?->customer;

        return [
            'id'              => $this->user->id,
            'email'           => $this->user->email,
            'first_name'      => $this->user->first_name ?? $this->contact?->first_name,
            'middle_name'     => $this->user->middle_name ?? $this->contact?->middle_name,
            'last_name'       => $this->user->last_name ?? $this->contact?->last_name,
            'phone'           => $this->user->phone,
            'crm_contact_id'  => $this->user->crm_contact_id,
            'can_submit_sample' => (bool) ($this->contact?->can_submit_sample ?? false),
            'customer'        => $customer ? [
                'id'   => $customer->id,
                'code' => $customer->code,
                'name' => $customer->name,
            ] : null,
        ];
    }
}
