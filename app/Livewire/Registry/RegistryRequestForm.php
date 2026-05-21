<?php

namespace App\Livewire\Registry;

use App\Actions\Registry\CreateRegistryRequestAction;
use App\DTOs\Registry\CreateRegistryRequestDTO;
use App\Models\Registry\RegistryRequestCategory;
use Livewire\Component;

class RegistryRequestForm extends Component
{
    public string $request_category_id = '';

    public string $subject = '';

    public string $description = '';

    public string $priority = 'normal';

    public string $direction = 'incoming';

    public string $submitting_party = '';

    public ?string $entity_type = null;

    public ?string $entity_id = null;

    public function mount(?string $categoryId = null): void
    {
        if ($categoryId !== null) {
            $this->request_category_id = $categoryId;
        }
    }

    public function rules(): array
    {
        return [
            'request_category_id' => ['required', 'uuid'],
            'subject' => ['required', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'direction' => ['required', 'in:incoming,outgoing'],
            'submitting_party' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function save(CreateRegistryRequestAction $action): void
    {
        $this->validate();

        $request = $action->execute(new CreateRegistryRequestDTO(
            requestCategoryId: $this->request_category_id,
            subject: $this->subject,
            description: $this->description,
            priority: $this->priority,
            direction: $this->direction,
            submittingParty: $this->submitting_party,
            entityType: $this->entity_type,
            entityId: $this->entity_id,
        ));

        session()->flash('success', 'Request created: ' . $request->reference_no);
        $this->redirectRoute('registry.requests.show', $request->id);
    }

    public function render()
    {
        $categories = RegistryRequestCategory::query()->forCompany()->active()->orderBy('name')->get();

        return view('livewire.registry.registry-request-form', compact('categories'));
    }
}
