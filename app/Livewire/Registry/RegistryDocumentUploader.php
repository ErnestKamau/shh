<?php

namespace App\Livewire\Registry;

use App\Actions\Registry\UploadRegistryDocumentAction;
use App\Models\Registry\RegistryRequest;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class RegistryDocumentUploader extends Component
{
    use WithFileUploads;

    public string $requestId;

    public $file = null;

    public int $fileInputKey = 0;

    public function mount(string $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function updatedFile(): void
    {
        $this->resetValidation('file');

        if ($this->file === null) {
            return;
        }

        $this->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,docx,xlsx,jpg,jpeg,png'],
        ]);
    }

    public function saveDocument(UploadRegistryDocumentAction $action): void
    {
        $this->validate([
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,docx,xlsx,jpg,jpeg,png'],
        ]);

        if (! $this->file instanceof TemporaryUploadedFile) {
            $this->addError('file', 'Please wait for the file to finish uploading, then try again.');

            return;
        }

        $request = RegistryRequest::query()->forCompany()->findOrFail($this->requestId);
        $action->execute($request, $this->file);

        $this->file = null;
        $this->fileInputKey++;
        $this->resetValidation('file');
        $this->dispatch('document-uploaded');
        session()->flash('success', 'Document uploaded.');
    }

    public function clearFile(): void
    {
        $this->file = null;
        $this->fileInputKey++;
        $this->resetValidation('file');
    }

    public function render()
    {
        $documents = RegistryRequest::query()
            ->findOrFail($this->requestId)
            ->documents()
            ->orderByDesc('version')
            ->get();

        return view('livewire.registry.registry-document-uploader', compact('documents'));
    }
}
