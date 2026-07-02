<?php

namespace App\Livewire\Sampleworkflow;

use App\Lab;
use App\Models\SubmissionFormInstance;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

    public bool $isQcBatch = false;

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
        $this->isQcBatch = false;
        $this->resetValidation();

        if (
            $this->selectedFormInstanceIds !== []
            && Schema::hasColumn('submission_form_instances', 'is_qc_batch')
        ) {
            $counts = DB::table('submission_form_instances')
                ->whereIn('id', $this->selectedFormInstanceIds)
                ->selectRaw('COUNT(*) as total_count')
                ->selectRaw('SUM(CASE WHEN is_qc_batch THEN 1 ELSE 0 END) as qc_count')
                ->first();

            $totalCount = (int) ($counts->total_count ?? 0);
            $qcCount = (int) ($counts->qc_count ?? 0);

            $this->isQcBatch = $totalCount > 0 && $qcCount === $totalCount;
        }

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
            'isQcBatch' => ['boolean'],
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
        $shouldStoreQcBatch = Schema::hasColumn('submission_form_instances', 'is_qc_batch');

        DB::transaction(function () use ($user, $notes, $shouldStoreQcBatch, &$processed, &$skipped) {
            $instances = SubmissionFormInstance::query()
            ->with(['attachmentInstances', 'batches'])
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

                $instanceAttributes = [
                    'receiving_lab_id' => $this->receivingLabId,
                ];

                if ($shouldStoreQcBatch) {
                    $instanceAttributes['is_qc_batch'] = $this->isQcBatch;
                }

                $instance->update($instanceAttributes);

                if ($instance->batches->isNotEmpty()) {
                    foreach ($instance->batches as $batch) {
                        $batch->update([
                            'is_qc_batch' => $this->isQcBatch,
                            'begin_proccess' => $this->isQcBatch ? 1 : 0,
                        ]);
                    }
                }

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
