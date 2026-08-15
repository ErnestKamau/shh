<?php

namespace App\Livewire\Crm\Contact;

use App\Mail\ContactWelcomeMail;
use App\Models\CRM\CustomerContact;
use App\Models\CRM\CRMCustomer;
use App\Livewire\Crm\BaseCrmComponent;
use App\Services\CRM\ContactSignatureService;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class ContactForm extends BaseCrmComponent
{
    use WithFileUploads;

    public $contactId = null;
    public $customerId;
    public $first_name = '';
    public $second_name = '';
    public $third_name = '';
    public $job_occupation = '';
    public $unit_name = [];
    public $email = '';
    public $telephone = '';
    public $mobile = '';
    public $receive_price_list = false;
    public $receive_invoice = false;
    public $receive_report = false;
    public $receive_feedback = false;
    public $is_main_customer_contact = false;
    public $active = true;
    public $units = [];
    public $unitSearch = '';
    public $showUnitDropdown = false;

    public $can_login = false;

    public $signatureUpload = null;

    public string $signatureData = '';

    public ?string $currentSignature = null;

    public bool $clearExistingSignature = false;

    public function mount($customerId, $contactId = null)
    {
        $this->initialize();
        $this->customerId = $customerId;
        $this->units = CRMCustomer::find($customerId)->units ?? [];

        if ($contactId) {
            $contact = CustomerContact::find($contactId);
            if ($contact) {
                $this->contactId = $contact->id;
                $this->first_name = $contact->first_name;
                $this->second_name = $contact->middle_name;
                $this->third_name = $contact->last_name;
                $this->job_occupation = $contact->job_occupation;
                $this->unit_name = $this->normalizeStoredUnitValues($contact->unit_name);
                $this->email = $contact->email;
                $this->telephone = $contact->telephone;
                $this->mobile = $contact->mobile;
                $this->receive_price_list = (bool) ($contact->receive_price_list ?? 0);
                $this->receive_invoice = (bool) ($contact->receive_invoice ?? 0);
                $this->receive_report = (bool) ($contact->receive_report ?? 0);
                $this->receive_feedback = (bool) ($contact->receive_feedback ?? 0);
                $this->is_main_customer_contact = (bool) ($contact->is_main_customer_contact ?? 0);
                $this->active = (bool) ($contact->active ?? 0);
                $this->can_login = (bool) ($contact->can_login ?? 0);
                $signatureService = app(ContactSignatureService::class);
                $this->currentSignature = $signatureService->publicUrl($contact->signature);
                // Prefill pad data so reopen/edit keeps the saved signature visible and saveable.
                $this->signatureData = $signatureService->toDataUri($contact->signature);
                $this->clearExistingSignature = false;
            }
        }
    }

    public function getFilteredUnitsProperty()
    {
        $search = trim(strtolower($this->unitSearch));

        return collect($this->units)
            ->when($search !== '', function ($units) use ($search) {
                return $units->filter(function ($unit) use ($search) {
                    return str_contains(strtolower($unit->name), $search);
                });
            })
            ->take(80)
            ->values();
    }

    public function getSelectedUnitsProperty()
    {
        $selectedIds = $this->normalizedUnitIds();

        return collect($this->units)
            ->filter(fn ($unit) => in_array((string) $unit->id, $selectedIds, true))
            ->values();
    }

    public function toggleUnitSelection(string $unitId): void
    {
        $unitId = (string) $unitId;
        $selected = $this->normalizedUnitIds();

        if (in_array($unitId, $selected, true)) {
            $selected = array_values(array_filter($selected, fn ($id) => $id !== $unitId));
        } else {
            $selected[] = $unitId;
        }

        $this->unit_name = $selected;
        $this->unitSearch = '';
        $this->showUnitDropdown = false;
    }

    public function removeUnitSelection(string $unitId): void
    {
        $unitId = (string) $unitId;

        $this->unit_name = array_values(array_filter(
            $this->normalizedUnitIds(),
            fn ($id) => $id !== $unitId
        ));
    }

    public function isUnitSelected($unitId): bool
    {
        return in_array((string) $unitId, $this->normalizedUnitIds(), true);
    }

    protected function normalizedUnitIds(): array
    {
        return collect($this->unit_name)
            ->map(fn ($id) => trim((string) $id))
            ->filter(fn ($id) => $id !== '')
            ->values()
            ->all();
    }

    protected function normalizeStoredUnitValues(?string $stored): array
    {
        $values = array_values(array_filter(
            array_map('trim', explode(',', $stored ?? '')),
            fn ($value) => $value !== ''
        ));

        return collect($values)
            ->map(function (string $value) {
                if (Str::isUuid($value)) {
                    return $value;
                }

                $matchedUnit = collect($this->units)->first(
                    fn ($unit) => strcasecmp((string) $unit->name, $value) === 0
                );

                return $matchedUnit ? (string) $matchedUnit->id : null;
            })
            ->filter()
            ->values()
            ->all();
    }

    protected function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'telephone' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:255',
            'unit_name' => 'nullable|array',
            'job_occupation' => 'nullable|string|max:255',
            'signatureUpload' => 'nullable|file|mimes:png,jpg,jpeg,gif,webp,pdf|max:5120',
            'signatureData' => 'nullable|string',
        ];
    }

    public function clearContactSignature(): void
    {
        $this->signatureUpload = null;
        $this->signatureData = '';
        $this->clearExistingSignature = true;
        $this->currentSignature = null;
    }

    public function save()
    {
        $this->telephone = trim($this->telephone ?? '');
        $this->validate();

        $emailNormalized = strtolower(trim($this->email));

        $duplicateQuery = CustomerContact::where('crm_customer_id', $this->customerId)
            ->whereRaw('LOWER(TRIM(email)) = ?', [$emailNormalized]);
        if ($this->contactId) {
            $duplicateQuery->where('id', '!=', $this->contactId);
        }
        if ($duplicateQuery->exists()) {
            $this->showError('Email already exists in the system');

            return;
        }

        $wasEditing = (bool) $this->contactId;
        $previousContactEmail = null;
        $portalCredentialsEmailed = false;

        if ($this->can_login) {
            $existingUser = User::whereRaw('LOWER(TRIM(email)) = ?', [$emailNormalized])->first();
            if ($existingUser && ! $wasEditing && (int) $existingUser->is_client !== 1) {
                $this->showError('There is already a user with the given email!');

                return;
            }
        }

        try {
            DB::beginTransaction();

            if (! $this->contactId) {
                $this->checkPermission('crm.components.contacts.add');
                $contact = new CustomerContact();
            } else {
                $this->checkPermission('crm.components.contacts.edit');
                $contact = CustomerContact::find($this->contactId);
                $previousContactEmail = $contact?->email;
            }

            if (! $contact) {
                DB::rollBack();
                $this->showError('Contact not found.');

                return;
            }

            $contact->first_name = $this->first_name;
            $contact->middle_name = $this->second_name;
            $contact->last_name = $this->third_name;
            $contact->job_occupation = $this->job_occupation;
            $contact->unit_name = implode(',', array_filter($this->unit_name ?? []));
            $contact->email = $this->email;
            $contact->telephone = $this->telephone;
            $contact->mobile = $this->mobile;
            $contact->company_id = $this->getUserCompany();
            $contact->crm_customer_id = $this->customerId;

            $contact->receive_price_list = $this->receive_price_list ? 1 : 0;
            $contact->receive_invoice = $this->receive_invoice ? 1 : 0;
            $contact->receive_report = $this->receive_report ? 1 : 0;
            $contact->receive_feedback = $this->receive_feedback ? 1 : 0;
            $contact->is_main_customer_contact = $this->is_main_customer_contact ? 1 : 0;
            $contact->active = $this->active ? 1 : 0;
            $contact->can_login = $this->can_login ? 1 : 0;

            $signatureService = app(ContactSignatureService::class);
            $appliedSignature = $signatureService->applyToContact(
                $contact,
                $this->signatureUpload,
                trim((string) $this->signatureData),
            );

            if (! $appliedSignature && $this->clearExistingSignature) {
                $signatureService->clearSignature($contact);
            }

            $contact->save();

            if ($this->is_main_customer_contact) {
                CustomerContact::query()
                    ->where('crm_customer_id', $this->customerId)
                    ->where('id', '!=', $contact->id)
                    ->update(['is_main_customer_contact' => false]);
            }

            // Refresh preview state from the persisted path.
            $this->currentSignature = $signatureService->publicUrl($contact->signature);
            if ($appliedSignature) {
                $this->signatureData = $signatureService->toDataUri($contact->signature);
                $this->signatureUpload = null;
                $this->clearExistingSignature = false;
            }

            if (! $this->can_login) {
                User::deactivatePortalUsersForCustomerContact(
                    $contact,
                    (string) $this->customerId,
                    $previousContactEmail
                );
            }

            if ($this->can_login) {
                $user = User::whereRaw('LOWER(TRIM(email)) = ?', [$emailNormalized])->first() ?? new User();
                $isNewPortalUser = ! $user->exists;

                $user->name = trim(implode(' ', array_filter([
                    trim((string) $this->first_name),
                    trim((string) $this->second_name),
                    trim((string) $this->third_name),
                ], fn (string $part): bool => $part !== '')));
                $user->first_name = (string) $this->first_name;
                $user->middle_name = (string) $this->second_name;
                $user->last_name = (string) $this->third_name;

                $plainPassword = null;
                if ($isNewPortalUser) {
                    $plainPassword = $this->generatePortalPassword();
                    $user->password = bcrypt($plainPassword);
                    $user->password_changed_at = null;
                }

                $user->email = $this->email;
                $user->company_id = $this->getUserCompany();
                $user->is_client = 1;
                $user->client_id = (string) $this->customerId;
                $user->crm_contact_id = $contact->id;
                $user->crmcontact_id = $contact->id;
                $user->active = 1;

                $user->save();

                if ($plainPassword) {
                    $recipientName = trim($user->name);
                    $recipientEmail = $user->email;
                    $portalCredentialsEmailed = true;

                    dispatch(function () use ($recipientName, $recipientEmail, $plainPassword) {
                        Mail::to($recipientEmail)->send(
                            new ContactWelcomeMail($recipientName, $recipientEmail, $plainPassword)
                        );
                    })->afterResponse();
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('ContactForm: failed saving contact with portal access', [
                'customer_id' => $this->customerId,
                'email' => $this->email,
                'can_login' => $this->can_login,
                'error' => $e->getMessage(),
            ]);
            $this->showError('Could not save contact'.($this->can_login ? ' with portal access: ' : ': ').$e->getMessage());

            return;
        }

        $successMessage = $wasEditing ? 'Contact edited successfully!' : 'Contact added successfully!';
        if ($portalCredentialsEmailed) {
            $successMessage .= ' Portal credentials have been emailed to the contact.';
        }

        $this->dispatch('contact-saved', message: $successMessage);
    }

    protected function generatePortalPassword(): string
    {
        $appName = preg_replace('/\s+/', '', (string) config('app.name')) ?: 'Portal';

        return $this->first_name . $appName . date('Y');
    }

    public function delete()
    {
        if ($this->contactId) {
            $this->checkPermission('crm.components.contacts.delete');
            $contact = CustomerContact::find($this->contactId);
            $contact->delete();

            $this->showSuccess('Contact deleted successfully');
            $this->dispatch('contact-deleted', message: 'Contact deleted successfully');
        }
    }

    public function close()
    {
        $this->dispatch('contact-form-closed');
    }

    public function render()
    {
        return view('livewire.crm.contact.contact-form');
    }
}
