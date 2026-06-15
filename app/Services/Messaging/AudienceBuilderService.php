<?php

namespace App\Services\Messaging;

use App\Models\Messaging\MessageAudience;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\CRMCustomer;
use Illuminate\Support\Collection;

class AudienceBuilderService
{
    /**
     * Get the query builder for audience contacts.
     */
    public function getAudienceContactsQuery(MessageAudience $audience)
    {
        $rules = $audience->rules_json ?? [];
        $tenantId = $audience->tenant_id;

        $query = CustomerContact::query()
            ->join('crm_customers', 'crm_customer_contacts.crm_customer_id', '=', 'crm_customers.id')
            ->where('crm_customer_contacts.active', 1);

        // Scope by company/tenant
        $query->where(function ($q) use ($tenantId) {
            $q->where('crm_customers.company_id', $tenantId)
              ->orWhere('crm_customer_contacts.company_id', $tenantId);
        });

        // Exclude contacts who have explicitly opted out
        $query->leftJoin('customer_whatsapp_preferences', function ($join) use ($tenantId) {
            $join->on('crm_customer_contacts.id', '=', 'customer_whatsapp_preferences.customer_id')
                 ->where('customer_whatsapp_preferences.tenant_id', '=', $tenantId);
        })->where(function ($q) {
            $q->whereNull('customer_whatsapp_preferences.opted_in')
              ->orWhere('customer_whatsapp_preferences.opted_in', 1);
        });

        // Apply filters based on rules_json
        if (!empty($rules['country_id'])) {
            $query->where('crm_customers.country_id', $rules['country_id']);
        }

        if (isset($rules['is_internal'])) {
            $query->where('crm_customers.is_internal', (bool) $rules['is_internal']);
        }

        return $query->select('crm_customer_contacts.*', 'crm_customers.name as customer_name');
    }

    /**
     * Resolve the targeted list of contacts for an audience definition.
     */
    public function resolveAudienceContacts(MessageAudience $audience): Collection
    {
        $contacts = $this->getAudienceContactsQuery($audience)->get();

        return $contacts->map(function ($contact) {
            $phone = $contact->mobile ?: $contact->telephone;
            $firstName = $contact->first_name ?? '';
            $lastName = $contact->last_name ?? '';
            $fullName = trim($firstName . ' ' . $lastName);
            
            return [
                'name' => $fullName ?: 'Valued Customer',
                'phone' => $phone,
                'customer_name' => $contact->customer_name,
                'contact_id' => $contact->id
            ];
        })->filter(function ($item) {
            return !empty($item['phone']);
        });
    }
}
