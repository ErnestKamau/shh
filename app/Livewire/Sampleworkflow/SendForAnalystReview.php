<?php

namespace App\Livewire\Sampleworkflow;

use App\Lab;
use App\Models\SubmissionFormInstance;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class SendForAnalystReview extends Component
{
    /** @var array<int, string> */
    public array $selectedFormInstanceIds = [];

    /** @var array<int, array{id: string, label: string, customer: string}> */
    public array $selectedFormSummaries = [];

    public string $comment = '';

    public string $receivingLabId = '';

    /**
     * @param  array<int, string>  $instanceIds
     * @param  array<int, array{id: string, label: string, customer: string}>  $summaries
     */
    public function handleAnalystReviewModalOpen(array $instanceIds, array $summaries): void
    {
        $this->selectedFormInstanceIds = array_values(array_filter($instanceIds));
        $this->selectedFormSummaries = $summaries;
        $this->comment = '';
        $this->receivingLabId = '';
        $this->resetValidation();

        $user = Auth::user();
        if ($user instanceof User && $user->lab_id) {
            $this->receivingLabId = (string) $user->lab_id;
        }

        $this->dispatch('show-analyst-review-modal');
    }

    protected function getListeners(): array
    {
        return [
            'analyst-review-modal-open' => 'handleAnalystReviewModalOpen',
        ];
    }

    public function confirmSendForAnalystReview(): void
    {
        if ($this->selectedFormInstanceIds === []) {
            $this->addError('selection', 'Select at least one request to send for analyst review.');

            return;
        }

        $this->validate([
            'receivingLabId' => ['required', 'string', Rule::exists('labs', 'id')],
            'comment' => ['nullable', 'string', 'max:2000'],
        ], [
            'receivingLabId.required' => 'Select the lab that will receive these samples.',
            'receivingLabId.exists' => 'Select a valid lab.',
        ]);

        $user = Auth::user();
        if (! $user instanceof User) {
            $this->addError('selection', 'You must be signed in to send requests for analyst review.');

            return;
        }

        $processed = 0;
        $skipped = 0;
        $notes = trim($this->comment) !== '' ? trim($this->comment) : null;

        DB::transaction(function () use ($user, $notes, &$processed, &$skipped) {
            $instances = SubmissionFormInstance::query()
                ->with(['attachmentInstances'])
                ->whereIn('id', $this->selectedFormInstanceIds)
                ->get();

            foreach ($instances as $instance) {
                if (! in_array($instance->status, ['submitted', 'received'], true)) {
                    $skipped++;

                    continue;
                }

                if (! $instance->markAsInReview($user, $notes)) {
                    $skipped++;

                    continue;
                }

                $instance->update(['receiving_lab_id' => $this->receivingLabId]);

                $processed++;
            }
        });

        if ($processed === 0) {
            $this->addError('selection', 'No eligible requests were sent for analyst review. They may already be in another status.');

            return;
        }

        $message = $processed === 1
            ? '1 request sent for analyst review.'
            : "{$processed} requests sent for analyst review.";

        if ($skipped > 0) {
            $message .= " ({$skipped} skipped.)";
        }

        session()->flash('success', $message);
        $this->dispatch('analyst-review-completed');
    }

    public function render()
    {
        $labs = Lab::query()
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'code', 'name']);

        return view('livewire.sampleworkflow.send-for-analyst-review', [
            'labs' => $labs,
        ]);
    }
}
