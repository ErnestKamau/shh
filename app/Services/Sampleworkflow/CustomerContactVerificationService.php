<?php

namespace App\Services\Sampleworkflow;

use App\Models\CRM\CustomerContact;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomerContactVerificationService
{
    private const MAX_FAILED_ATTEMPTS = 5;

    /**
     * @return array{contact_id: string, signer_name: string}
     */
    public function verify(
        string $crmCustomerId,
        string $contactId,
        string $password,
        ?string $acceptanceFormId = null
    ): array {
        if (trim($crmCustomerId) === '' || trim($contactId) === '') {
            throw ValidationException::withMessages([
                'password' => ['Invalid contact or password.'],
            ]);
        }

        $contact = CustomerContact::query()
            ->where('id', $contactId)
            ->where('crm_customer_id', $crmCustomerId)
            ->where('active', 1)
            ->where('can_login', 1)
            ->first();

        if (! $contact) {
            if (trim($password) !== '') {
                $this->guardFailedAttempts($acceptanceFormId);
                $this->recordFailedAttempt($acceptanceFormId);
            }

            throw ValidationException::withMessages([
                'password' => ['Invalid contact or password.'],
            ]);
        }

        if (trim($password) === '') {
            $this->clearFailedAttempts($acceptanceFormId);

            return [
                'contact_id' => (string) $contact->id,
                'signer_name' => $this->contactDisplayName($contact),
            ];
        }

        $this->guardFailedAttempts($acceptanceFormId);

        $user = $this->resolvePortalUser($contact, $crmCustomerId);

        if (! $user || ! Hash::check($password, (string) $user->password)) {
            $this->recordFailedAttempt($acceptanceFormId);
            throw ValidationException::withMessages([
                'password' => ['Invalid contact or password.'],
            ]);
        }

        $this->clearFailedAttempts($acceptanceFormId);

        return [
            'contact_id' => (string) $contact->id,
            'signer_name' => $this->contactDisplayName($contact),
        ];
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    public function portalContactsForCustomer(string $crmCustomerId): array
    {
        return $this->contactsForCustomerQuery($crmCustomerId, portalOnly: true);
    }

    /**
     * All active CRM contacts for in-person acceptance signing (no portal login required).
     *
     * @return list<array{id: string, label: string}>
     */
    public function activeContactsForCustomer(string $crmCustomerId): array
    {
        return $this->contactsForCustomerQuery($crmCustomerId, portalOnly: false);
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    private function contactsForCustomerQuery(string $crmCustomerId, bool $portalOnly): array
    {
        if (trim($crmCustomerId) === '') {
            return [];
        }

        $query = CustomerContact::query()
            ->where('crm_customer_id', $crmCustomerId)
            ->where('active', 1)
            ->orderBy('first_name')
            ->orderBy('last_name');

        if ($portalOnly) {
            $query->where('can_login', 1);
        }

        return $query
            ->get()
            ->map(fn (CustomerContact $contact) => [
                'id' => (string) $contact->id,
                'label' => $this->contactDisplayName($contact),
            ])
            ->values()
            ->all();
    }

    private function resolvePortalUser(CustomerContact $contact, string $crmCustomerId): ?User
    {
        return User::query()
            ->where('is_client', 1)
            ->where('active', 1)
            ->where('client_id', $crmCustomerId)
            ->where(function ($query) use ($contact): void {
                $query->where('crm_contact_id', $contact->id)
                    ->orWhere('crmcontact_id', $contact->id);

                if ($contact->email) {
                    $query->orWhere('email', $contact->email);
                }
            })
            ->first();
    }

    private function contactDisplayName(CustomerContact $contact): string
    {
        $name = trim(implode(' ', array_filter([
            $contact->first_name,
            $contact->middle_name,
            $contact->last_name,
        ])));

        return $name !== '' ? $name : (string) ($contact->email ?? 'Customer contact');
    }

    private function sessionKey(?string $acceptanceFormId): string
    {
        return 'customer_acceptance_sign_failures.' . ($acceptanceFormId ?? 'global');
    }

    private function guardFailedAttempts(?string $acceptanceFormId): void
    {
        $failures = (int) session($this->sessionKey($acceptanceFormId), 0);

        if ($failures >= self::MAX_FAILED_ATTEMPTS) {
            throw ValidationException::withMessages([
                'password' => ['Too many failed attempts. Close this dialog and try again later.'],
            ]);
        }
    }

    private function recordFailedAttempt(?string $acceptanceFormId): void
    {
        $key = $this->sessionKey($acceptanceFormId);
        session([$key => (int) session($key, 0) + 1]);
    }

    private function clearFailedAttempts(?string $acceptanceFormId): void
    {
        session()->forget($this->sessionKey($acceptanceFormId));
    }
}
