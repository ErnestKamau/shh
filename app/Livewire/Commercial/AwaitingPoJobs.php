<?php

namespace App\Livewire\Commercial;

use App\AnalysisType;
use App\Http\Requests\Commercial\ApplyHeldJobPurchaseOrderRequest;
use App\Http\Requests\Commercial\CancelHeldJobRequest;
use App\Livewire\Concerns\ValidatesWithFormRequest;
use App\Models\Commercial\CustomerPurchaseOrder;
use App\SampleAnalysisTypeRelation;
use App\SampleHeader;
use App\Services\Commercial\JobPurchaseOrderCoverageService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Jobs held as Awaiting PO: apply a PO to release samples to the lab, or cancel the held job.
 */
class AwaitingPoJobs extends Component
{
    use ValidatesWithFormRequest;

    private const LIST_LIMIT = 200;

    public string $search = '';

    public bool $showApplyModal = false;

    public bool $showCancelModal = false;

    public ?string $selectedJobId = null;

    public string $selectedPurchaseOrderId = '';

    public string $cancelReason = '';

    public string $errorMessage = '';

    public string $successMessage = '';

    /**
     * @return Collection<int, SampleHeader>
     */
    #[Computed]
    public function jobs(): Collection
    {
        $search = trim($this->search);

        return app(JobPurchaseOrderCoverageService::class)
            ->heldJobsQuery()
            ->with(['customer:id,name', 'splitFrom:id,batch_code'])
            ->withCount('samples')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('batch_code', 'ilike', '%'.$search.'%')
                        ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'ilike', '%'.$search.'%'));
                });
            })
            ->orderByRaw('COALESCE(po_held_at, created_at) asc')
            ->limit(self::LIST_LIMIT)
            ->get();
    }

    /**
     * @return array<string, string> job id => analysis names
     */
    #[Computed]
    public function analysisNamesByJob(): array
    {
        $jobIds = $this->jobs->pluck('id')->map(fn ($id): string => (string) $id)->all();
        if ($jobIds === []) {
            return [];
        }

        $relations = SampleAnalysisTypeRelation::query()
            ->whereIn('batch_id', $jobIds)
            ->get(['batch_id', 'analysis_type_id']);

        $names = AnalysisType::query()
            ->whereIn('id', $relations->pluck('analysis_type_id')->filter()->unique()->all())
            ->pluck('name', 'id');

        return $relations
            ->groupBy(fn ($relation): string => (string) $relation->batch_id)
            ->map(fn ($rows): string => $rows->pluck('analysis_type_id')
                ->unique()
                ->map(fn ($id): ?string => $names[(string) $id] ?? null)
                ->filter()
                ->implode(', '))
            ->all();
    }

    #[Computed]
    public function selectedJob(): ?SampleHeader
    {
        if ($this->selectedJobId === null) {
            return null;
        }

        return SampleHeader::query()
            ->with(['customer:id,name', 'splitFrom:id,batch_code'])
            ->withCount('samples')
            ->find($this->selectedJobId);
    }

    /**
     * @return Collection<int, CustomerPurchaseOrder>
     */
    #[Computed]
    public function drawableOrders(): Collection
    {
        $job = $this->selectedJob;

        return $job !== null
            ? app(JobPurchaseOrderCoverageService::class)->drawableOrdersFor($job)
            : new Collection;
    }

    /**
     * @return array{covered: int, held: int}|null
     */
    #[Computed]
    public function applyPreview(): ?array
    {
        $job = $this->selectedJob;
        $po = $this->drawableOrders->firstWhere('id', $this->selectedPurchaseOrderId);

        if ($job === null || $po === null) {
            return null;
        }

        return app(JobPurchaseOrderCoverageService::class)->previewApply($job, $po);
    }

    #[Computed]
    public function canApply(): bool
    {
        return Gate::allows(CustomerPurchaseOrder::PERMISSION_APPLY_TO_HELD_JOB);
    }

    #[Computed]
    public function canCancel(): bool
    {
        return Gate::allows(CustomerPurchaseOrder::PERMISSION_CANCEL_HELD_JOB);
    }

    #[Computed]
    public function canCreatePurchaseOrders(): bool
    {
        return Gate::allows(CustomerPurchaseOrder::PERMISSION_CREATE);
    }

    public function heldForDays(SampleHeader $job): int
    {
        $heldAt = $job->po_held_at ?? $job->created_at;

        return $heldAt !== null ? max(0, (int) floor($heldAt->diffInDays(now()))) : 0;
    }

    public function openApplyModal(string $jobId): void
    {
        $this->resetMessages();
        Gate::authorize(CustomerPurchaseOrder::PERMISSION_APPLY_TO_HELD_JOB);

        $this->selectedJobId = $jobId;
        unset($this->selectedJob, $this->drawableOrders, $this->applyPreview);

        $withBalance = $this->drawableOrders->filter(fn (CustomerPurchaseOrder $po): bool => (int) $po->remaining_total > 0);
        $this->selectedPurchaseOrderId = $withBalance->count() === 1 ? (string) $withBalance->first()->id : '';
        $this->showApplyModal = true;
    }

    public function closeApplyModal(): void
    {
        $this->showApplyModal = false;
        $this->selectedJobId = null;
        $this->selectedPurchaseOrderId = '';
        $this->resetErrorBag();
    }

    public function applyPurchaseOrder(): void
    {
        $this->errorMessage = '';
        Gate::authorize(CustomerPurchaseOrder::PERMISSION_APPLY_TO_HELD_JOB);

        $this->validateWithFormRequest(ApplyHeldJobPurchaseOrderRequest::class, [
            'sample_header_id' => $this->selectedJobId,
            'customer_purchase_order_id' => $this->selectedPurchaseOrderId,
        ]);

        $job = $this->selectedJob;
        if ($job === null) {
            $this->closeApplyModal();

            return;
        }

        try {
            $outcome = app(JobPurchaseOrderCoverageService::class)
                ->applyPurchaseOrder($job, $this->selectedPurchaseOrderId, (string) Auth::id());
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            $this->errorMessage = 'The PO could not be applied: '.$exception->getMessage();

            return;
        }

        $message = sprintf(
            'Job %s released to the lab: %d sample(s) on PO %s.',
            $job->batch_code,
            $outcome->coveredSamples,
            $outcome->purchaseOrderNumber,
        );

        if ($outcome->wasSplit()) {
            $message .= sprintf(
                ' %d sample(s) the PO could not cover moved to job %s, still awaiting a PO.',
                $outcome->heldSamples,
                SampleHeader::query()->whereKey($outcome->heldSampleHeaderId)->value('batch_code'),
            );
        }

        $this->closeApplyModal();
        unset($this->jobs, $this->analysisNamesByJob);
        $this->successMessage = $message;
    }

    public function openCancelModal(string $jobId): void
    {
        $this->resetMessages();
        Gate::authorize(CustomerPurchaseOrder::PERMISSION_CANCEL_HELD_JOB);

        $this->selectedJobId = $jobId;
        $this->cancelReason = '';
        unset($this->selectedJob);
        $this->showCancelModal = true;
    }

    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
        $this->selectedJobId = null;
        $this->cancelReason = '';
        $this->resetErrorBag();
    }

    public function cancelHeldJob(): void
    {
        $this->errorMessage = '';
        Gate::authorize(CustomerPurchaseOrder::PERMISSION_CANCEL_HELD_JOB);

        $this->validateWithFormRequest(CancelHeldJobRequest::class, [
            'sample_header_id' => $this->selectedJobId,
            'reason' => $this->cancelReason,
        ]);

        $job = $this->selectedJob;
        if ($job === null) {
            $this->closeCancelModal();

            return;
        }

        try {
            app(JobPurchaseOrderCoverageService::class)->cancelHeldJob($job, $this->cancelReason, (string) Auth::id());
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);
            $this->errorMessage = 'The held job could not be cancelled: '.$exception->getMessage();

            return;
        }

        $this->closeCancelModal();
        unset($this->jobs, $this->analysisNamesByJob);
        $this->successMessage = sprintf(
            'Job %s cancelled.%s',
            $job->batch_code,
            $job->splitFrom !== null ? ' Job '.$job->splitFrom->batch_code.'\'s test report no longer waits for it.' : '',
        );
    }

    public function render(): View
    {
        return view('livewire.commercial.awaiting-po-jobs');
    }

    private function resetMessages(): void
    {
        $this->errorMessage = '';
        $this->successMessage = '';
        $this->resetErrorBag();
    }
}
