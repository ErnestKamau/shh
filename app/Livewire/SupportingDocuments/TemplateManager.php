<?php

namespace App\Livewire\SupportingDocuments;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\SupportingDocumentTemplate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TemplateManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public int $perPage = 25;
    public array $perPageOptions = [10, 25, 50, 100];

    public bool $showCreateModal = false;
    public ?string $newDocumentCode = null;
    public string $newTitle = '';
    public ?string $newSubtitle = null;
    public ?string $newDescription = null;

    public ?string $message = null;
    public string $messageType = 'success';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function getTemplatesProperty()
    {
        $query = SupportingDocumentTemplate::query()
            ->orderByDesc('updated_at');

        if (! empty($this->search)) {
            $s = $this->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                    ->orWhere('document_code', 'like', "%{$s}%");
            });
        }

        if ($this->statusFilter === 'published') {
            $query->where('is_published', true);
        }
        if ($this->statusFilter === 'draft') {
            $query->where('is_published', false);
        }
        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        }
        if ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->paginate($this->perPage);
    }

    public function openCreateModal(): void
    {
        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    private function resetCreateForm(): void
    {
        $this->newDocumentCode = null;
        $this->newTitle = '';
        $this->newSubtitle = null;
        $this->newDescription = null;
    }

    public function createTemplate()
    {
        $validated = $this->validate([
            'newDocumentCode' => 'nullable|string|max:100',
            'newTitle' => 'required|string|max:255',
            'newSubtitle' => 'nullable|string|max:255',
            'newDescription' => 'nullable|string|max:5000',
        ]);

        $user = Auth::user();

        $template = SupportingDocumentTemplate::create([
            'document_code' => $validated['newDocumentCode'],
            'title' => $validated['newTitle'],
            'subtitle' => $validated['newSubtitle'],
            'description' => $validated['newDescription'],
            'version' => 1,
            'is_published' => false,
            'is_active' => true,
            'company_id' => $user->company_id ?? null,
            'created_by' => $user->id,
        ]);

        $this->message = 'Template created.';
        $this->messageType = 'success';
        $this->closeCreateModal();

        return redirect()->route('supporting-documents.templates.edit', ['template' => $template->id]);
    }

    public function toggleActive(int $templateId): void
    {
        $template = SupportingDocumentTemplate::findOrFail($templateId);
        $template->update(['is_active' => ! $template->is_active]);
        $this->message = $template->is_active ? 'Template activated.' : 'Template deactivated.';
        $this->messageType = 'success';
    }

    public function publish(int $templateId): void
    {
        DB::transaction(function () use ($templateId) {
            $template = SupportingDocumentTemplate::lockForUpdate()->findOrFail($templateId);

            if (! $template->is_published) {
                $template->version = (int) $template->version + 1;
            }

            $template->is_published = true;
            $template->save();
        });

        $this->message = 'Template published.';
        $this->messageType = 'success';
    }

    public function unpublish(int $templateId): void
    {
        $template = SupportingDocumentTemplate::findOrFail($templateId);
        $template->update(['is_published' => false]);
        $this->message = 'Template unpublished.';
        $this->messageType = 'success';
    }

    public function deleteTemplate(int $templateId): void
    {
        $template = SupportingDocumentTemplate::findOrFail($templateId);
        $template->delete();
        $this->message = 'Template deleted.';
        $this->messageType = 'success';
    }

    public function dismissMessage(): void
    {
        $this->message = null;
    }

    public function render()
    {
        return view('livewire.supporting-documents.template-manager', [
            'templates' => $this->templates,
        ]);
    }
}
