<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\Exports\CRM\CustomerRegistryTabExport;
use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\CrmCustomerAttachment;
use App\Models\CRM\CRMCustomer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\WithFileUploads;

class CustomerAttachmentsTab extends BaseCrmComponent
{
    use WithFileUploads;

    public CRMCustomer $customer;

    public int $perPage = 10;

    public $attachmentFile;

    public string $title = '';

    public string $type = '';

    public string $description = '';

    public string $customType = '';

    public ?string $editingAttachmentId = null;

    public ?string $attachmentIdToDelete = null;

    public string $typeSearch = '';

    public bool $showTypeDropdown = false;

    public array $attachmentTypes = [
        'Agreement',
        'Contract',
        'Annual PO',
        'Supporting Document',
    ];

    public array $availableAttachmentTypes = [];

    public function mount(CRMCustomer $customer): void
    {
        $this->initialize();
        $this->customer = $customer;
        $this->loadAvailableAttachmentTypes();
    }

    public function loadAvailableAttachmentTypes(): void
    {
        $dbTypes = CrmCustomerAttachment::where('crm_customer_id', $this->customer->id)
            ->whereNotNull('type')
            ->where('is_delete', '!=', 1)
            ->distinct()
            ->pluck('type')
            ->toArray();

        $this->availableAttachmentTypes = array_values(array_unique(array_merge($this->attachmentTypes, $dbTypes)));
        sort($this->availableAttachmentTypes);
        $this->dispatch('customer-attachment-types-updated');
    }

    public function getSelectedTypeProperty(): ?string
    {
        return $this->type !== '' ? $this->type : null;
    }

    public function getFilteredTypeOptionsProperty()
    {
        $search = strtolower(trim($this->typeSearch));
        $options = array_values(array_unique(array_merge($this->availableAttachmentTypes, ['Other'])));

        return collect($options)
            ->filter(function ($option) use ($search) {
                if ($option === $this->type) {
                    return false;
                }

                if ($search === '') {
                    return true;
                }

                return str_contains(strtolower($option), $search);
            })
            ->values();
    }

    public function selectType(string $value): void
    {
        $this->type = $value;
        $this->typeSearch = '';
        $this->showTypeDropdown = false;

        if ($value !== 'Other') {
            $this->customType = '';
        }
    }

    public function clearType(): void
    {
        $this->type = '';
        $this->customType = '';
        $this->typeSearch = '';
        $this->showTypeDropdown = false;
    }

    public function getAttachmentsProperty()
    {
        return CrmCustomerAttachment::where('crm_customer_id', $this->customer->id)
            ->where('is_delete', '!=', 1)
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function exportToExcel()
    {
        $this->checkPermission('crm.customers.view');

        return (new CustomerRegistryTabExport($this->customer->id, 'documents'))
            ->download('customer_documents_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function openAttachmentModal(): void
    {
        $this->reset([
            'title', 'type', 'customType', 'description', 'attachmentFile',
            'editingAttachmentId', 'typeSearch', 'showTypeDropdown',
        ]);
        $this->dispatch('show-customer-attachment-modal');
    }

    public function editAttachment(string $id): void
    {
        $this->checkPermission('crm.customers.edit');

        $attachment = CrmCustomerAttachment::where('crm_customer_id', $this->customer->id)
            ->where('is_delete', '!=', 1)
            ->find($id);

        if (!$attachment) {
            return;
        }

        $this->editingAttachmentId = $attachment->id;
        $this->title = $attachment->title ?? '';

        if (in_array($attachment->type, $this->attachmentTypes, true)) {
            $this->type = $attachment->type;
            $this->customType = '';
        } else {
            $this->type = 'Other';
            $this->customType = $attachment->type;
        }

        $this->description = $attachment->description ?? '';
        $this->typeSearch = '';
        $this->showTypeDropdown = false;

        $this->dispatch('show-customer-attachment-modal');
    }

    public function uploadAttachment(): void
    {
        $this->checkPermission('crm.customers.edit');

        $this->validate([
            'attachmentFile' => 'required|file|max:5120',
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'customType' => 'required_if:type,Other|nullable|string|max:255',
        ]);

        $attachment = new CrmCustomerAttachment();
        $attachment->title = $this->title;
        $attachment->type = ($this->type === 'Other') ? $this->customType : $this->type;
        $attachment->description = $this->description;
        $attachment->crm_customer_id = $this->customer->id;
        $attachment->posted_by = Auth::user()->name;

        if ($this->attachmentFile) {
            $path = $this->attachmentFile->store('crm-customers', 'public');
            $attachment->file_path = '/storage/' . $path;
            $attachment->file_size = $this->attachmentFile->getSize();
            $attachment->file_type = $this->resolveFileType($this->attachmentFile->getClientOriginalExtension());
        }

        $attachment->save();

        $this->reset([
            'title', 'type', 'customType', 'description', 'attachmentFile',
            'editingAttachmentId', 'typeSearch', 'showTypeDropdown',
        ]);
        $this->loadAvailableAttachmentTypes();

        $this->showSuccess('Attachment added successfully');
        $this->dispatch('close-customer-attachment-modal');
        $this->dispatch('customer-attachment-added');
    }

    public function updateAttachment(): void
    {
        $this->checkPermission('crm.customers.edit');

        $this->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|string',
            'customType' => 'required_if:type,Other|nullable|string|max:255',
            'attachmentFile' => 'nullable|file|max:5120',
        ]);

        $attachment = CrmCustomerAttachment::where('crm_customer_id', $this->customer->id)
            ->where('is_delete', '!=', 1)
            ->find($this->editingAttachmentId);

        if (!$attachment) {
            return;
        }

        $attachment->title = $this->title;
        $attachment->type = ($this->type === 'Other') ? $this->customType : $this->type;
        $attachment->description = $this->description;

        if ($this->attachmentFile) {
            $this->deleteStoredFile($attachment->file_path);

            $path = $this->attachmentFile->store('crm-customers', 'public');
            $attachment->file_path = '/storage/' . $path;
            $attachment->file_size = $this->attachmentFile->getSize();
            $attachment->file_type = $this->resolveFileType($this->attachmentFile->getClientOriginalExtension());
        }

        $attachment->save();

        $this->reset([
            'title', 'type', 'customType', 'description', 'attachmentFile',
            'editingAttachmentId', 'typeSearch', 'showTypeDropdown',
        ]);
        $this->loadAvailableAttachmentTypes();

        $this->showSuccess('Attachment updated successfully');
        $this->dispatch('close-customer-attachment-modal');
        $this->dispatch('customer-attachment-added');
    }

    public function confirmDelete(string $id): void
    {
        $this->checkPermission('crm.customers.edit');
        $this->attachmentIdToDelete = $id;
        $this->dispatch('show-customer-delete-confirmation');
    }

    public function deleteAttachment(): void
    {
        $this->checkPermission('crm.customers.edit');

        $attachment = CrmCustomerAttachment::where('crm_customer_id', $this->customer->id)
            ->where('is_delete', '!=', 1)
            ->find($this->attachmentIdToDelete);

        if ($attachment) {
            $this->deleteStoredFile($attachment->file_path);
            $attachment->is_delete = 1;
            $attachment->save();

            $this->showSuccess('Attachment deleted successfully');
            $this->dispatch('close-customer-delete-confirmation');
            $this->dispatch('customer-attachment-deleted');
            $this->attachmentIdToDelete = null;
        }
    }

    #[On('customer-attachment-added')]
    #[On('customer-attachment-deleted')]
    public function refreshAttachments(): void
    {
        $this->loadAvailableAttachmentTypes();
    }

    protected function deleteStoredFile(?string $filePath): void
    {
        if (!$filePath) {
            return;
        }

        $relativePath = str_replace('/storage/', '', $filePath);

        if (Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->delete($relativePath);
        }
    }

    protected function resolveFileType(?string $extension): string
    {
        $extension = strtolower(trim((string) $extension));

        return match ($extension) {
            'pdf' => 'pdf',
            'docx' => 'docx',
            'png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp' => 'screenshot',
            '' => 'document',
            default => 'other',
        };
    }

    public function render()
    {
        return view('livewire.crm.customer.tabs.customer-attachments-tab', [
            'attachments' => $this->attachments,
        ]);
    }
}
