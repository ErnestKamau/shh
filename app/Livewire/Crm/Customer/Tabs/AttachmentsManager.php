<?php

namespace App\Livewire\Crm\Customer\Tabs;

use App\Livewire\Crm\BaseCrmComponent;
use App\Models\CRM\CRMCustomer;
use App\Models\CRM\CustomerCertification;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\UploadedFile;
use Livewire\WithFileUploads;

class AttachmentsManager extends BaseCrmComponent
{
    use WithFileUploads;

    public CRMCustomer $customer;

    public int $perPage = 15;

    protected $paginationTheme = 'bootstrap';

    public bool $showCreateModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public $certificate;

    public string $certificationDate = '';

    public string $expireDate = '';

    public string $certificationBody = '';

    public int $status = 0;

    public string $modalError = '';

    public function getIsActiveProperty(): bool
    {
        return $this->status === 0;
    }

    public function setIsActiveProperty(bool $value): void
    {
        $this->status = $value ? 0 : 1;
    }

    public function mount(CRMCustomer $customer): void
    {
        $this->customer = $customer;
    }

    public function close(): void
    {
        $this->showCreateModal = false;
    }

    public function openCreateModal(): void
    {
        $this->reset([
            'name', 'certificate', 'certificationDate', 'expireDate',
            'certificationBody', 'status', 'editingId', 'modalError',
        ]);
        $this->status = 0;
        $this->showCreateModal = true;
    }

    public function editAttachment(int $id): void
    {
        $item = CustomerCertification::findOrFail($id);
        $this->editingId = $id;
        $this->name = $item->name ?? '';
        $this->certificationDate = $item->certification_date ? date('Y-m-d', strtotime($item->certification_date)) : '';
        $this->expireDate = $item->expire_date ? date('Y-m-d', strtotime($item->expire_date)) : '';
        $this->certificationBody = $item->certification_body ?? '';
        $this->status = (int) $item->status;
        $this->modalError = '';
        $this->showCreateModal = true;
    }

    public function createAttachment(): void
    {
        $this->modalError = '';
        $this->validate([
            'name' => 'required|string|max:255',
            'certificationDate' => 'required|date',
            'expireDate' => 'required|date',
            'certificationBody' => 'required|string|max:255',
            'certificate' => 'required|file|max:10240',
        ], [], ['certificationDate' => 'certificate date', 'expireDate' => 'expire date', 'certificationBody' => 'certification body']);

        $path = $this->certificate->store('certificate', 'public');
        $fname = '/storage/certificate/'.urlencode(basename($path));

        $cert = new CustomerCertification;
        $cert->name = $this->name;
        $cert->customer_id = $this->customer->id;
        $cert->certification_date = $this->certificationDate;
        $cert->expire_date = $this->expireDate;
        $cert->certification_body = $this->certificationBody;
        $cert->certificate = $fname;
        $cert->status = 0;
        $cert->save();

        $this->showCreateModal = false;
        $this->showSuccess('Attachment added successfully!');
    }

    public function updateAttachment(): void
    {
        $this->modalError = '';
        $rules = [
            'name' => 'required|string|max:255',
            'certificationDate' => 'required|date',
            'expireDate' => 'required|date',
            'certificationBody' => 'required|string|max:255',
        ];
        if ($this->certificate) {
            $rules['certificate'] = 'file|max:10240';
        }
        $this->validate($rules);

        $cert = CustomerCertification::findOrFail($this->editingId);
        $cert->name = $this->name;
        $cert->certification_date = $this->certificationDate;
        $cert->expire_date = $this->expireDate;
        $cert->certification_body = $this->certificationBody;
        $cert->edited = auth()->user()->name;
        $cert->status = $this->status;

        if ($this->certificate instanceof UploadedFile) {
            $path = $this->certificate->store('certificate', 'public');
            $cert->certificate = '/storage/certificate/'.urlencode(basename($path));
        }

        $cert->save();

        $this->showCreateModal = false;
        $this->reset(['editingId']);
        $this->showSuccess('Edited customer attachment successfully!');
    }

    public function saveAttachment(): void
    {
        if ($this->editingId) {
            $this->updateAttachment();
        } else {
            $this->createAttachment();
        }
    }

    public function deleteAttachment(int $id): void
    {
        $cert = CustomerCertification::findOrFail($id);
        $cert->edited = auth()->user()->name;
        $cert->status = 1;
        $cert->save();

        $this->showSuccess('Attachment deleted successfully');
    }

    public function getCertificationsProperty(): LengthAwarePaginator
    {
        return CustomerCertification::where('customer_id', $this->customer->id)
            ->where('status', 0)
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);
    }

    public function placeholder(): string
    {
        return '<div class="d-flex justify-content-center align-items-center p-5"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>';
    }

    public function render(): View
    {
        return view('livewire.crm.customer.tabs.attachments-manager');
    }
}
