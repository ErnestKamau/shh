<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\SampleSubmissionRequest;
use App\Models\SupportingDocumentInstance;
use App\Services\SupportingDocumentInstanceFormService;
use App\Support\SupportingDocumentElementValidation;
use App\Support\SupportingDocumentParagraphTemplate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SubmissionSupportingDocumentFill extends Component
{
    public int $submissionRequestId;

    public int $instanceId;

    public bool $readOnly = false;

    /**
     * @var array<int|string, mixed>
     */
    public array $values = [];

    public function mount(int $submissionRequestId, int $instanceId): void
    {
        $this->submissionRequestId = $submissionRequestId;
        $this->instanceId = $instanceId;

        $instance = $this->loadInstanceOrAbort();
        $this->readOnly = $instance->status === 'submitted';
        $this->hydrateValuesFromInstance($instance);
    }

    public function saveDraft(SupportingDocumentInstanceFormService $formService): void
    {
        if ($this->readOnly) {
            return;
        }

        $instance = $this->loadInstanceOrAbort();
        $instance->loadMissing(['template.sections.elements']);
        $elements = $instance->template->sections->flatMap(fn ($section) => $section->elements);

        DB::transaction(function () use ($formService, $instance, $elements): void {
            $formService->syncValuesFromInput($instance, $elements, $this->values);
        });

        session()->flash('success', 'Supporting document saved as draft.');
        $this->redirect(route('sample-submission-requests.show', ['request' => $this->submissionRequestId, 'details' => 1]), navigate: true);
    }

    public function submitDocument(SupportingDocumentInstanceFormService $formService): void
    {
        if ($this->readOnly) {
            return;
        }

        $instance = $this->loadInstanceOrAbort();
        $instance->loadMissing(['template.sections.elements']);
        $elements = $instance->template->sections->flatMap(fn ($section) => $section->elements);

        $this->validate($formService->buildSubmitRules($elements));

        DB::transaction(function () use ($formService, $instance, $elements): void {
            $formService->syncValuesFromInput($instance, $elements, $this->values);
            $instance->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);
        });

        session()->flash('success', 'Supporting document submitted.');
        $this->redirect(route('sample-submission-requests.show', ['request' => $this->submissionRequestId, 'details' => 1]), navigate: true);
    }

    /**
     * @return list<array{type: 'text'|'placeholder', content?: string, name?: string}>
     */
    public function paragraphSegments(?string $body): array
    {
        return SupportingDocumentParagraphTemplate::segments($body);
    }

    public function render(): View
    {
        $submissionRequest = SampleSubmissionRequest::query()->findOrFail($this->submissionRequestId);
        $instance = $this->loadInstanceOrAbort();

        return view('livewire.sampleworkflow.submission-supporting-document-fill', [
            'submissionRequest' => $submissionRequest,
            'instance' => $instance,
        ]);
    }

    private function loadInstanceOrAbort(): SupportingDocumentInstance
    {
        return SupportingDocumentInstance::query()
            ->whereKey($this->instanceId)
            ->where('sample_submission_request_id', $this->submissionRequestId)
            ->with(['template.sections.elements', 'values'])
            ->firstOrFail();
    }

    private function hydrateValuesFromInstance(SupportingDocumentInstance $instance): void
    {
        $this->values = [];
        $instance->loadMissing(['template.sections.elements']);
        $elements = $instance->template->sections->flatMap(fn ($section) => $section->elements)->keyBy('id');

        foreach ($instance->values as $row) {
            $eid = (int) $row->supporting_document_element_id;
            $element = $elements->get($eid);
            if (! $element) {
                continue;
            }

            if ($element->element_type === 'paragraph_template') {
                $decoded = json_decode((string) $row->value, true);
                $this->values[$eid] = is_array($decoded) ? $decoded : [];
            } else {
                $this->values[$eid] = (string) $row->value;
            }
        }

        foreach ($elements as $element) {
            if ($element->element_type === 'paragraph_template') {
                if (! isset($this->values[$element->id]) || ! is_array($this->values[$element->id])) {
                    $this->values[$element->id] = [];
                }

                foreach (SupportingDocumentElementValidation::paragraphPlaceholderNames($element->default_value) as $ph) {
                    if (! array_key_exists($ph, $this->values[$element->id])) {
                        $this->values[$element->id][$ph] = '';
                    }
                }
            }

            if (in_array($element->element_type, ['text', 'textarea', 'number', 'date'], true)) {
                if (! array_key_exists($element->id, $this->values)) {
                    $this->values[$element->id] = '';
                }
            }
        }
    }
}
