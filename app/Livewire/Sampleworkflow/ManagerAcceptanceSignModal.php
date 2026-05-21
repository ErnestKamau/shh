<?php

namespace App\Livewire\Sampleworkflow;

use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\Services\Sampleworkflow\AcceptanceFormService;
use App\Services\Sampleworkflow\SampleReceiptNotificationService;
use App\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\On;
use Livewire\Component;

class ManagerAcceptanceSignModal extends Component
{
    public bool $showModal = false;

    public ?string $acceptanceFormId = null;

    public ?AnalysisAcceptanceForm $acceptanceForm = null;

    public string $managerSignerName = '';

    public string $managerSignature = '';

    public ?string $managerSignedAt = null;

    public string $leadAnalystId = '';

    public string $technicalSignatoryId = '';

    /** @var list<array{id: string, name: string}> */
    public array $analystOptions = [];

    /** @var list<array{id: string, name: string}> */
    public array $signatoryOptions = [];

    /** @var array<string, mixed> */
    public array $receiptNotificationForm = [];

    #[On('open-manager-acceptance-sign')]
    public function openModal(string $acceptanceFormId): void
    {
        $this->authorizeLabAccess();

        $form = AnalysisAcceptanceForm::query()
            ->with([
                'lines.sampleType',
                'lines.analysisType',
                'customer',
                'submissionFormInstance',
                'sampleHeader',
            ])
            ->find($acceptanceFormId);

        if (!$form || $form->status !== AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN) {
            $this->dispatch('notify', type: 'error', message: 'This request is not awaiting laboratory manager approval.');

            return;
        }

        if (!$form->sample_header_id) {
            $this->dispatch('notify', type: 'error', message: 'Sample batch has not been created yet. Wait for customer signature to complete.');

            return;
        }

        $this->resetModal();
        $this->acceptanceFormId = $form->id;
        $this->acceptanceForm = $form;
        $this->managerSignerName = (string) (Auth::user()->name ?? '');
        $this->managerSignedAt = now()->format('Y-m-d');
        $this->leadAnalystId = (string) ($form->sampleHeader?->specialist_analyst_id ?? '');
        $this->technicalSignatoryId = (string) ($form->sampleHeader?->approve_user_id ?? '');
        $this->analystOptions = $this->loadAnalystOptions();
        $this->signatoryOptions = $this->loadSignatoryOptions();
        $this->receiptNotificationForm = app(SampleReceiptNotificationService::class)
            ->resolveFormStateForAcceptanceForm($form);
        $this->showModal = true;
        $this->dispatch('manager-acceptance-sign-opened');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetModal();
    }

    public function submitManagerSign(AcceptanceFormService $acceptanceFormService): void
    {
        $this->authorizeLabAccess();

        $form = $this->loadAcceptanceForm();

        if ($form->status !== AnalysisAcceptanceForm::STATUS_AWAITING_LAB_MANAGER_SIGN) {
            $this->dispatch('notify', type: 'error', message: 'This acceptance form is no longer awaiting manager approval.');
            $this->closeModal();

            return;
        }

        $this->validate([
            'leadAnalystId' => ['required', 'uuid', 'exists:users,id'],
            'technicalSignatoryId' => ['required', 'uuid', 'exists:users,id'],
            'managerSignerName' => ['required', 'string', 'max:255'],
            'managerSignature' => ['required', 'string'],
            'managerSignedAt' => ['nullable', 'date'],
        ], [
            'leadAnalystId.required' => 'Please assign a lead analyst.',
            'technicalSignatoryId.required' => 'Please assign a technical signatory.',
        ]);

        $receiptService = app(SampleReceiptNotificationService::class);
        $receiptService->mergePayloadIntoAcceptanceForm($form, $this->receiptNotificationForm);

        $batchHeader = $form->sampleHeader;
        if ($batchHeader === null && $form->sample_header_id) {
            $batchHeader = \App\SampleHeader::query()->find((string) $form->sample_header_id);
        }
        if ($batchHeader !== null) {
            $receiptService->persistForBatchLinkedAcceptance($form, $batchHeader, $this->receiptNotificationForm);
            $receiptWarn = '';
            $receiptService->tryFinalizePdfAttachment(
                $batchHeader,
                $this->receiptNotificationForm,
                Auth::id(),
                function (string $msg, array $errors) use (&$receiptWarn): void {
                    $receiptWarn = $msg;
                }
            );
            if ($receiptWarn !== '') {
                session()->flash('warning', 'Acceptance approved. Complete Sample Receipt Notification (GCLA 01) in Lab Acceptance tab (Part E)—required fields or signatures are missing.');
            }
        }

        $completedForm = $acceptanceFormService->recordManagerSignature(
            $form,
            $this->managerSignerName,
            $this->managerSignature,
            $this->managerSignedAt,
            $this->leadAnalystId,
            $this->technicalSignatoryId,
        );

        $batchId = (string) ($completedForm->sample_header_id ?? '');
        $redirectUrl = $batchId !== ''
            ? route('view-batch-details', [
                'batch' => $batchId,
                'client' => 0,
                'portal' => 0,
                'status' => 'Samples In Lab',
            ]) . '#laboratory-acceptance-part-5'
            : route('sample-workflow', ['status' => 'Samples In Lab']);

        session()->flash('success', 'Acceptance approved. Batch moved to Samples In Lab.');
        $this->closeModal();
        $this->dispatch('acceptance-form-completed', redirectUrl: $redirectUrl);
    }

    public function render(): View
    {
        return view('livewire.sampleworkflow.manager-acceptance-sign-modal');
    }

    private function loadAcceptanceForm(): AnalysisAcceptanceForm
    {
        if (!$this->acceptanceFormId) {
            throw ValidationException::withMessages([
                'managerSignerName' => ['Acceptance form not loaded.'],
            ]);
        }

        $form = AnalysisAcceptanceForm::query()
            ->with(['lines.sampleType', 'lines.analysisType', 'customer', 'sampleHeader'])
            ->find($this->acceptanceFormId);

        if (!$form) {
            throw ValidationException::withMessages([
                'managerSignerName' => ['Acceptance form not found.'],
            ]);
        }

        $this->acceptanceForm = $form;

        return $form;
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    private function loadAnalystOptions(): array
    {
        $analystRoleNames = ['laboratory analyst', 'analyst'];
        $driver = DB::connection()->getDriverName();
        $userIdColumn = $driver === 'pgsql' ? DB::raw('id::text') : 'id';

        $analystUserIds = DB::table('spatie_model_has_roles as smr')
            ->join('spatie_roles as sr', 'sr.id', '=', 'smr.role_id')
            ->where('smr.model_type', User::class)
            ->where('sr.guard_name', 'web')
            ->where(function ($query) use ($analystRoleNames) {
                foreach ($analystRoleNames as $roleName) {
                    $query->orWhereRaw('LOWER(sr.name) = ?', [$roleName]);
                }
            })
            ->pluck('smr.model_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($analystUserIds === []) {
            return $this->loadActiveLabUserOptions();
        }

        return User::query()
            ->where('active', 1)
            ->where('is_support_staff', 0)
            ->whereIn($userIdColumn, $analystUserIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user) => ['id' => (string) $user->id, 'name' => (string) $user->name])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    private function loadSignatoryOptions(): array
    {
        $signatoryRoleHints = ['signatory', 'technical', 'manager', 'approver'];
        $driver = DB::connection()->getDriverName();
        $userIdColumn = $driver === 'pgsql' ? DB::raw('id::text') : 'id';

        $signatoryUserIds = DB::table('spatie_model_has_roles as smr')
            ->join('spatie_roles as sr', 'sr.id', '=', 'smr.role_id')
            ->where('smr.model_type', User::class)
            ->where('sr.guard_name', 'web')
            ->where(function ($query) use ($signatoryRoleHints) {
                foreach ($signatoryRoleHints as $hint) {
                    $query->orWhereRaw('LOWER(sr.name) LIKE ?', ['%' . $hint . '%']);
                }
            })
            ->pluck('smr.model_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($signatoryUserIds === []) {
            return $this->loadActiveLabUserOptions();
        }

        return User::query()
            ->where('active', 1)
            ->where('is_client', 0)
            ->where('is_support_staff', 0)
            ->whereIn($userIdColumn, $signatoryUserIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user) => ['id' => (string) $user->id, 'name' => (string) $user->name])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    private function loadActiveLabUserOptions(): array
    {
        return User::query()
            ->where('active', 1)
            ->where('is_client', 0)
            ->where('is_support_staff', 0)
            ->whereNull('supplier_id')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (User $user) => ['id' => (string) $user->id, 'name' => (string) $user->name])
            ->values()
            ->all();
    }

    private function authorizeLabAccess(): void
    {
        $user = Auth::user();

        if (!$user) {
            abort(403);
        }

        if (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) {
            return;
        }

        if ($user->can('Laboratory.components.RFT Form.View') || $user->can('Laboratory.permission')) {
            return;
        }

        abort(403, 'You are not authorized to perform this action.');
    }

    private function resetModal(): void
    {
        $this->acceptanceFormId = null;
        $this->acceptanceForm = null;
        $this->managerSignerName = '';
        $this->managerSignature = '';
        $this->managerSignedAt = null;
        $this->leadAnalystId = '';
        $this->technicalSignatoryId = '';
        $this->analystOptions = [];
        $this->signatoryOptions = [];
        $this->receiptNotificationForm = SampleReceiptNotificationService::emptyForm();
    }
}
