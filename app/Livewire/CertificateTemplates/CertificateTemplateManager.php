<?php

namespace App\Livewire\CertificateTemplates;

use Livewire\Component;
use Livewire\WithPagination;
use App\CertificateTemplate;
use App\Models\SubmissionForm;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class CertificateTemplateManager extends Component
{
    use WithPagination;

    // Search and Filter
    public $search = '';
    public $submissionFormFilter = '';
    public $statusFilter = '';

    // UI State
    public $loading = false;
    public $message = '';
    public $messageType = '';
    public $perPage = 25;
    public $perPageOptions = [10, 25, 50, 100];

    // Delete confirmation
    public $showDeleteModal = false;
    public $templateToDelete = null;
    public $templateToDeleteName = '';

    // Supporting Data
    public $submissionForms = [];

    public function mount()
    {
        $this->loadSubmissionForms();
        
        // Get submission_form_id from query string if present
        if (request()->has('submission_form_id')) {
            $this->submissionFormFilter = request()->get('submission_form_id');
        }
    }

    public function loadSubmissionForms()
    {
        $this->submissionForms = SubmissionForm::where('is_published', true)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getTemplatesProperty()
    {
        $query = CertificateTemplate::with(['creator', 'sections.elementHolders', 'submissionForm'])
            ->withCount(['sections', 'reports']);

        if ($this->search) {
            $query->where(function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->submissionFormFilter) {
            $query->where('submission_form_id', $this->submissionFormFilter);
        }

        if ($this->statusFilter) {
            if ($this->statusFilter === 'published') {
                $query->where('is_published', true);
            } elseif ($this->statusFilter === 'draft') {
                $query->where('is_published', false);
            } elseif ($this->statusFilter === 'active') {
                $query->where('is_active', true);
            } elseif ($this->statusFilter === 'inactive') {
                $query->where('is_active', false);
            }
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSubmissionFormFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function togglePublished($templateId)
    {
        try {
            $template = CertificateTemplate::findOrFail($templateId);
            $template->update([
                'is_published' => !$template->is_published
            ]);

            $this->message = $template->is_published 
                ? 'Template published successfully.' 
                : 'Template unpublished successfully.';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'An error occurred while updating the template status.';
            $this->messageType = 'danger';
        }
    }

    public function toggleActive($templateId)
    {
        try {
            $template = CertificateTemplate::findOrFail($templateId);
            $template->update([
                'is_active' => !$template->is_active
            ]);

            $this->message = $template->is_active 
                ? 'Template activated successfully.' 
                : 'Template deactivated successfully.';
            $this->messageType = 'success';
        } catch (\Exception $e) {
            $this->message = 'An error occurred while updating the template status.';
            $this->messageType = 'danger';
        }
    }

    public function openDeleteModal($templateId)
    {
        $template = CertificateTemplate::findOrFail($templateId);
        $this->templateToDelete = $templateId;
        $this->templateToDeleteName = $template->name;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->templateToDelete = null;
        $this->templateToDeleteName = '';
    }

    public function deleteTemplate()
    {
        if (!$this->templateToDelete) {
            return;
        }

        try {
            DB::transaction(function () {
                $template = CertificateTemplate::findOrFail($this->templateToDelete);

                // Delete all report files before deleting the template
                $reports = $template->reports()->get();
                foreach ($reports as $report) {
                    if ($report->file_path) {
                        if (Storage::disk('local')->exists($report->file_path)) {
                            Storage::disk('local')->delete($report->file_path);
                        }
                    }
                }

                // Delete images from header_settings and footer_settings
                if ($template->header_settings) {
                    $headerSettings = $template->header_settings;
                    if (isset($headerSettings['logo']) && !empty($headerSettings['logo'])) {
                        $logoPath = str_replace(asset('storage/'), '', $headerSettings['logo']);
                        if (Storage::disk('public')->exists($logoPath)) {
                            Storage::disk('public')->delete($logoPath);
                        }
                    }
                }

                if ($template->footer_settings) {
                    $footerSettings = $template->footer_settings;
                    if (isset($footerSettings['logo']) && !empty($footerSettings['logo'])) {
                        $logoPath = str_replace(asset('storage/'), '', $footerSettings['logo']);
                        if (Storage::disk('public')->exists($logoPath)) {
                            Storage::disk('public')->delete($logoPath);
                        }
                    }
                }

                // Delete all sections (this will cascade delete elements and holders via database constraints)
                $sections = $template->sections()->with(['elementHolders.elements', 'elements'])->get();
                foreach ($sections as $section) {
                    // Delete elements in holders first
                    foreach ($section->elementHolders as $holder) {
                        foreach ($holder->elements as $element) {
                            // Delete any associated files (e.g., images)
                            if ($element->element_type === 'image' && !empty($element->properties['image_path'])) {
                                $imagePath = str_replace(asset('storage/'), '', $element->properties['image_path']);
                                if (Storage::disk('public')->exists($imagePath)) {
                                    Storage::disk('public')->delete($imagePath);
                                }
                            }
                        }
                    }
                    
                    // Delete direct elements (not in holders)
                    foreach ($section->elements as $element) {
                        // Delete any associated files (e.g., images)
                        if ($element->element_type === 'image' && !empty($element->properties['image_path'])) {
                            $imagePath = str_replace(asset('storage/'), '', $element->properties['image_path']);
                            if (Storage::disk('public')->exists($imagePath)) {
                                Storage::disk('public')->delete($imagePath);
                            }
                        }
                    }
                }

                // Delete the template (this will cascade delete sections, elements, holders, and reports via database constraints)
                $template->delete();
            });

            $this->message = 'Certificate template and all associated data deleted successfully.';
            $this->messageType = 'success';
            $this->closeDeleteModal();
            $this->resetPage();
        } catch (\Exception $e) {
            $this->message = 'Error deleting template: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->submissionFormFilter = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    public function render()
    {
        return view('livewire.certificate-templates.certificate-template-manager', [
            'templates' => $this->templates
        ]);
    }
}
