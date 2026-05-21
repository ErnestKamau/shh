<?php

namespace App\Livewire\Registry;

use App\Actions\Registry\UploadRegistryDocumentAction;
use App\Models\Registry\RegistryRequest;
use Livewire\Component;
use Livewire\WithFileUploads;

class RegistryDocumentUploader extends Component
{
    use WithFileUploads;

    public string $requestId;

    public $file;

    public function mount(string $requestId): void
    {
        $this->requestId = $requestId;
    }

    public function upload(UploadRegistryDocumentAction $action): void
    {
        $this->validate(['file' => 'required|file|max:10240|mimes:pdf,docx,xlsx,jpg,jpeg,png']);

        $request = RegistryRequest::query()->forCompany()->findOrFail($this->requestId);
        $action->execute($request, $this->file);

        $this->reset('file');
        $this->dispatch('document-uploaded');
        session()->flash('success', 'Document uploaded.');
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
