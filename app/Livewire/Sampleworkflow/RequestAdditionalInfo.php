<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\SubmissionFormInstance;
use App\Services\SubmissionForm\SubmissionFormAdditionalInfoService;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RequestAdditionalInfo extends Component
{
    /** @var array<int, string> */
    public array $selectedFormInstanceIds = [];

    /** @var array<int, array{id: string, label: string, customer: string}> */
    public array $selectedFormSummaries = [];

    public string $remarks = '';

    public bool $notifyCustomer = true;

    /**
     * @param  array<int, string>  $instanceIds
     * @param  array<int, array{id: string, label: string, customer: string}>  $summaries
     */
    public function handleRequestAdditionalInfoModalOpen(array $instanceIds, array $summaries): void
    {
        $this->selectedFormInstanceIds = array_values(array_filter($instanceIds));
        $this->selectedFormSummaries = $summaries;
        $this->remarks = '';
        $this->notifyCustomer = true;
        $this->resetValidation();

        $this->dispatch('show-request-additional-info-modal');
    }

    protected function getListeners(): array
    {
        return [
            'request-additional-info-modal-open' => 'handleRequestAdditionalInfoModalOpen',
        ];
    }

    public function confirmRequestAdditionalInfo(): void
    {
        if ($this->selectedFormInstanceIds === []) {
            $this->addError('selection', 'Select at least one received request.');

            return;
        }

        $this->validate([
            'remarks' => ['required', 'string', 'min:10', 'max:2000'],
            'notifyCustomer' => ['boolean'],
        ], [
            'remarks.required' => 'Enter a message describing what additional information is needed.',
            'remarks.min' => 'The message must be at least 10 characters.',
        ]);

        $user = Auth::user();
        if (! $user instanceof User) {
            $this->addError('selection', 'You must be signed in to request additional information.');

            return;
        }

        $processed = 0;
        $skipped = 0;
        $emailsSent = 0;
        $emailsMissing = 0;
        $infoService = app(SubmissionFormAdditionalInfoService::class);

        DB::transaction(function () use ($user, $infoService, &$processed, &$skipped, &$emailsSent, &$emailsMissing) {
            $instances = SubmissionFormInstance::query()
                ->with(['crmCustomer', 'submittedBy', 'values.element'])
                ->whereIn('id', $this->selectedFormInstanceIds)
                ->get();

            foreach ($instances as $instance) {
                if (! in_array($instance->status, ['received', 'in_review'], true)) {
                    $skipped++;

                    continue;
                }

                if (! $instance->markAsInAdditionalInfo($user, $this->remarks, false)) {
                    $skipped++;

                    continue;
                }

                $processed++;

                if ($this->notifyCustomer) {
                    if ($infoService->notifyCustomer($instance->fresh(['crmCustomer', 'submittedBy', 'values.element']), $this->remarks, $user)) {
                        $emailsSent++;
                    } else {
                        $emailsMissing++;
                    }
                }
            }
        });

        if ($processed === 0) {
            $this->addError('selection', 'No eligible requests were updated. They may already be in another status.');

            return;
        }

        $message = $processed === 1
            ? '1 request moved to Request Additional Info.'
            : "{$processed} requests moved to Request Additional Info.";

        if ($skipped > 0) {
            $message .= " ({$skipped} skipped.)";
        }

        if ($this->notifyCustomer && $emailsMissing > 0) {
            $message .= $emailsMissing === 1
                ? ' 1 request had no customer email on file.'
                : " {$emailsMissing} requests had no customer email on file.";
        }

        if ($this->notifyCustomer && $emailsSent > 0) {
            $message .= $emailsSent === 1
                ? ' Notification email sent.'
                : " Notification emails sent to {$emailsSent} customer(s).";
        }

        session()->flash('success', $message);
        $this->dispatch('request-additional-info-completed');
    }

    public function render()
    {
        return view('livewire.sampleworkflow.request-additional-info');
    }
}
