<?php

namespace App\Services\CRM;

use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerContact;

class CustomerContactPrefillService
{
    public function resolveDefaultContact(CRMCustomer $customer, ?string $preferredContactId = null): ?CustomerContact
    {
        $customer->loadMissing(['contacts', 'mainContact']);

        if ($preferredContactId !== null && $preferredContactId !== '') {
            $preferred = CustomerContact::query()
                ->where('crm_customer_id', $customer->id)
                ->where('id', $preferredContactId)
                ->where('active', 1)
                ->first();

            if ($preferred !== null) {
                return $preferred;
            }
        }

        if ($customer->mainContact !== null) {
            return $customer->mainContact;
        }

        return $customer->contacts->first();
    }

    /**
     * @return array{
     *     contact: ?CustomerContact,
     *     contact_id: string,
     *     contact_name: string,
     *     address: string,
     *     tel_fax: string,
     *     mobile: string,
     *     email: string
     * }
     */
    public function buildCustomerDetailFields(CRMCustomer $customer, ?CustomerContact $contact = null): array
    {
        $contact ??= $this->resolveDefaultContact($customer);

        $contactName = '';
        $contactId = '';
        if ($contact !== null) {
            $contactName = trim(implode(' ', array_filter([
                (string) ($contact->first_name ?? ''),
                (string) ($contact->middle_name ?? ''),
                (string) ($contact->last_name ?? ''),
            ])));
            $contactId = (string) $contact->id;
        }

        $address = (string) ($customer->physical_address ?? $customer->postal_address ?? '');
        $telFax = (string) ($contact?->telephone ?? $customer->telephone1 ?? $customer->telephone2 ?? '');
        $mobile = (string) ($contact?->mobile ?? $contact?->telephone ?? $customer->telephone2 ?? $customer->telephone1 ?? '');
        $email = (string) ($contact?->email ?? $customer->email ?? '');

        return [
            'contact' => $contact,
            'contact_id' => $contactId,
            'contact_name' => $contactName,
            'address' => $address,
            'tel_fax' => $telFax,
            'mobile' => $mobile,
            'email' => $email,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function buildTrfPrefillMap(CRMCustomer $customer, ?CustomerContact $contact = null): array
    {
        $fields = $this->buildCustomerDetailFields($customer, $contact);
        $canonicalName = trim((string) ($customer->name ?? ''));

        return [
            'customer_name' => $canonicalName,
            'customer_address' => $fields['address'],
            'customer_phone' => $fields['tel_fax'],
            'mobile_number' => $fields['mobile'],
            'contact_person' => $fields['contact_id'] !== '' ? $fields['contact_id'] : $fields['contact_name'],
            'customer_email' => $fields['email'],
            'sampling_location' => '',
            'client_name' => $canonicalName,
            'customer' => $canonicalName,
            'client' => $canonicalName,
            'address' => $fields['address'],
            'physical_address' => (string) ($customer->physical_address ?? ''),
            'postal_address' => (string) ($customer->postal_address ?? ''),
            'phone' => $fields['tel_fax'],
            'telephone' => $fields['tel_fax'],
            'phone_number' => $fields['tel_fax'],
            'telephone_number' => $fields['tel_fax'],
            'tel_fax_no' => $fields['tel_fax'],
            'email' => $fields['email'],
            'email_address' => $fields['email'],
            'contact' => $fields['contact_name'],
            'contact_name' => $fields['contact_name'],
            'crm_contact_id' => $fields['contact_id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function buildContactCommunicationFields(CustomerContact $contact, CRMCustomer $customer): array
    {
        $telFax = (string) ($contact->telephone ?? $customer->telephone1 ?? $customer->telephone2 ?? '');
        $mobile = (string) ($contact->mobile ?? $contact->telephone ?? $customer->telephone2 ?? $customer->telephone1 ?? '');
        $email = (string) ($contact->email ?? $customer->email ?? '');

        return [
            'customer_phone' => $telFax,
            'mobile_number' => $mobile,
            'customer_email' => $email,
            'phone' => $telFax,
            'telephone' => $telFax,
            'phone_number' => $telFax,
            'telephone_number' => $telFax,
            'tel_fax_no' => $telFax,
            'email' => $email,
            'email_address' => $email,
        ];
    }
}
