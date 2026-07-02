<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\SubmissionFormInstance;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class SendForAnalystReview extends Component
{
    /** @var array<int, string> */
    public array $selectedFormInstanceIds = [];

    /** @var array<int, array{id: string, label: string, customer: string}> */
    public array $selectedFormSummaries = [];

    public string $comment = '';

    /**
     * @param  array<int, string>  $instanceIds
     * @param  array<int, array{id: string, label: string, customer: string}>  $summaries
     */
    public function handleAnalystReviewModalOpen(array $instanceIds, array $summaries): void
    {
        $this->selectedFormInstanceIds = array_values(array_filter($instanceIds));
        $this->selectedFormSummaries = $summaries;
        $this->comment = '';
        $this->resetValidation();

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
            $this->addError('selection', 'Select at least one request to send for review.');

            return;
        }

        $this->validate([
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $user = Auth::user();
        if (! $user instanceof User) {
            $this->addError('selection', 'You must be signed in to send requests for review.');

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

                $processed++;
            }
        });

        if ($processed === 0) {
            $this->addError('selection', 'No eligible requests were sent for review. They may already be in another status.');

            return;
        }

        $message = $processed === 1
            ? '1 request sent for review.'
            : "{$processed} requests sent for review.";

        if ($skipped > 0) {
            $message .= " ({$skipped} skipped.)";
        }

        session()->flash('success', $message);
        $this->dispatch('analyst-review-completed');
    }

    public function render()
    {
        return view('livewire.sampleworkflow.send-for-analyst-review');
    }
}
