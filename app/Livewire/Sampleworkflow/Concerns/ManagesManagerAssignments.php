<?php

namespace App\Livewire\Sampleworkflow\Concerns;

use App\Lab;
use App\Models\Sampleworkflow\AnalysisAcceptanceForm;
use App\User;
use Illuminate\Support\Facades\DB;

trait ManagesManagerAssignments
{
    /** @var array<int, string> */
    public array $assignedAnalystIds = [];

    public string $leadAnalystId = '';

    public string $technicalSignatoryId = '';

    /** @var list<array{id: string, name: string}> */
    public array $analystOptions = [];

    /** @var list<array{id: string, name: string}> */
    public array $signatoryOptions = [];

    public string $assignedAnalystSearch = '';

    public bool $showAssignedAnalystDropdown = false;

    public string $leadAnalystSearch = '';

    public bool $showLeadAnalystDropdown = false;

    public string $signatorySearch = '';

    public bool $showSignatoryDropdown = false;

    public function updatedAssignedAnalystIds(): void
    {
        $this->assignedAnalystIds = array_values(array_unique(array_filter($this->assignedAnalystIds)));

        if ($this->leadAnalystId !== '' && ! in_array($this->leadAnalystId, $this->assignedAnalystIds, true)) {
            $this->leadAnalystId = '';
            $this->resetValidation(['leadAnalystId']);
        }
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function getLeadAnalystOptionsProperty(): array
    {
        if ($this->assignedAnalystIds === []) {
            return [];
        }

        $assigned = array_flip($this->assignedAnalystIds);

        return array_values(array_filter(
            $this->analystOptions,
            fn (array $option) => isset($assigned[$option['id']])
        ));
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function getFilteredAnalystPickerOptionsProperty(): array
    {
        $search = strtolower(trim($this->assignedAnalystSearch));

        return array_values(array_filter(
            $this->analystOptions,
            fn (array $option) => $search === '' || str_contains(strtolower($option['name']), $search)
        ));
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function getFilteredLeadAnalystPickerOptionsProperty(): array
    {
        $search = strtolower(trim($this->leadAnalystSearch));

        return array_values(array_filter(
            $this->leadAnalystOptions,
            fn (array $option) => $search === '' || str_contains(strtolower($option['name']), $search)
        ));
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    public function getFilteredSignatoryPickerOptionsProperty(): array
    {
        $search = strtolower(trim($this->signatorySearch));

        return array_values(array_filter(
            $this->signatoryOptions,
            fn (array $option) => $search === '' || str_contains(strtolower($option['name']), $search)
        ));
    }

    public function openAssignedAnalystDropdown(): void
    {
        $this->showAssignedAnalystDropdown = true;
    }

    public function closeAssignedAnalystDropdown(): void
    {
        $this->showAssignedAnalystDropdown = false;
    }

    public function toggleAssignedAnalyst(string $userId): void
    {
        if (in_array($userId, $this->assignedAnalystIds, true)) {
            $this->assignedAnalystIds = array_values(array_filter(
                $this->assignedAnalystIds,
                fn (string $id) => $id !== $userId
            ));

            if ($this->leadAnalystId === $userId) {
                $this->leadAnalystId = '';
            }
        } else {
            $this->assignedAnalystIds[] = $userId;
            $this->assignedAnalystIds = array_values(array_unique($this->assignedAnalystIds));
        }

        $this->assignedAnalystSearch = '';
        $this->showAssignedAnalystDropdown = false;
    }

    public function removeAssignedAnalyst(string $userId): void
    {
        $this->toggleAssignedAnalyst($userId);
    }

    public function openLeadAnalystDropdown(): void
    {
        if ($this->assignedAnalystIds === []) {
            return;
        }

        $this->showLeadAnalystDropdown = true;
    }

    public function closeLeadAnalystDropdown(): void
    {
        $this->showLeadAnalystDropdown = false;
    }

    public function selectLeadAnalyst(string $userId): void
    {
        if (! in_array($userId, $this->assignedAnalystIds, true)) {
            return;
        }

        $this->leadAnalystId = $userId;
        $this->leadAnalystSearch = '';
        $this->showLeadAnalystDropdown = false;
    }

    public function clearLeadAnalyst(): void
    {
        $this->leadAnalystId = '';
        $this->leadAnalystSearch = '';
        $this->showLeadAnalystDropdown = false;
    }

    public function openSignatoryDropdown(): void
    {
        $this->showSignatoryDropdown = true;
    }

    public function closeSignatoryDropdown(): void
    {
        $this->showSignatoryDropdown = false;
    }

    public function selectTechnicalSignatory(string $userId): void
    {
        $this->technicalSignatoryId = $userId;
        $this->signatorySearch = '';
        $this->showSignatoryDropdown = false;
    }

    public function clearTechnicalSignatory(): void
    {
        $this->technicalSignatoryId = '';
        $this->signatorySearch = '';
        $this->showSignatoryDropdown = false;
    }

    public function managerAssignmentUserName(string $userId, string $pool = 'analyst'): ?string
    {
        $options = $pool === 'signatory' ? $this->signatoryOptions : $this->analystOptions;

        foreach ($options as $option) {
            if ($option['id'] === $userId) {
                return $option['name'];
            }
        }

        if ($pool === 'analyst') {
            foreach ($this->leadAnalystOptions as $option) {
                if ($option['id'] === $userId) {
                    return $option['name'];
                }
            }
        }

        return null;
    }

    protected function initializeManagerAssignmentFields(?AnalysisAcceptanceForm $form = null): void
    {
        $this->assignedAnalystIds = [];
        $this->leadAnalystId = '';
        $this->technicalSignatoryId = '';
        $this->analystOptions = $this->loadAnalystOptionsForAssignment();
        $this->signatoryOptions = $this->loadSignatoryOptionsForAssignment();

        if ($form !== null) {
            $this->hydrateManagerAssignmentsFromForm($form);
        }
    }

    protected function resetManagerAssignmentFields(): void
    {
        $this->assignedAnalystIds = [];
        $this->leadAnalystId = '';
        $this->technicalSignatoryId = '';
        $this->analystOptions = [];
        $this->signatoryOptions = [];
        $this->assignedAnalystSearch = '';
        $this->showAssignedAnalystDropdown = false;
        $this->leadAnalystSearch = '';
        $this->showLeadAnalystDropdown = false;
        $this->signatorySearch = '';
        $this->showSignatoryDropdown = false;
    }

    /**
     * @return array<string, array<int, string|\Illuminate\Validation\Rules\In>>
     */
    protected function managerAssignmentValidationRules(): array
    {
        $this->assignedAnalystIds = array_values(array_unique(array_filter($this->assignedAnalystIds)));

        return [
            'assignedAnalystIds' => ['required', 'array', 'min:1'],
            'assignedAnalystIds.*' => ['uuid', 'exists:users,id'],
            'leadAnalystId' => [
                'required',
                'uuid',
                'exists:users,id',
                \Illuminate\Validation\Rule::in($this->assignedAnalystIds),
            ],
            'technicalSignatoryId' => ['required', 'uuid', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function managerAssignmentValidationMessages(): array
    {
        return [
            'assignedAnalystIds.required' => 'Select at least one analyst for this batch.',
            'assignedAnalystIds.min' => 'Select at least one analyst for this batch.',
            'leadAnalystId.required' => 'Select the lead analyst from the assigned analysts.',
            'leadAnalystId.in' => 'Lead analyst must be one of the analysts you assigned above.',
            'technicalSignatoryId.required' => 'Select the technical signatory.',
        ];
    }

    private function hydrateManagerAssignmentsFromForm(AnalysisAcceptanceForm $form): void
    {
        $payload = is_array($form->manager_assignment_payload) ? $form->manager_assignment_payload : [];
        $savedAssigned = array_values(array_filter((array) ($payload['assigned_analyst_ids'] ?? [])));
        $savedLead = (string) ($payload['lead_analyst_id'] ?? '');
        $savedSignatory = (string) ($payload['technical_signatory_id'] ?? '');

        $suggested = $this->resolveSuggestedAnalystIds($form);

        $this->assignedAnalystIds = $savedAssigned !== []
            ? $savedAssigned
            : $suggested;

        $this->leadAnalystId = '';
        if ($savedLead !== '' && in_array($savedLead, $this->assignedAnalystIds, true)) {
            $this->leadAnalystId = $savedLead;
        }

        $this->technicalSignatoryId = $savedSignatory;
    }

    /**
     * @return list<string>
     */
    private function resolveSuggestedAnalystIds(AnalysisAcceptanceForm $form): array
    {
        $form->loadMissing(['lines.analysisType']);

        $ids = [];
        foreach ($form->lines->where('is_approved', true) as $line) {
            $labId = $line->analysisType?->lab_id;
            if ($labId === null || (string) $labId === '') {
                continue;
            }

            $lab = Lab::query()->find((string) $labId);
            if ($lab === null || ! is_array($lab->analyst_ids)) {
                continue;
            }

            foreach ($lab->analyst_ids as $analystId) {
                if ($analystId !== null && (string) $analystId !== '') {
                    $ids[] = (string) $analystId;
                }
            }
        }

        $ids = array_values(array_unique($ids));

        if ($ids === []) {
            return [];
        }

        return User::query()
            ->where('active', 1)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();
    }

    /**
     * @return list<array{id: string, name: string}>
     */
    private function loadAnalystOptionsForAssignment(): array
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
            return $this->loadActiveLabUserOptionsForAssignment();
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
    private function loadSignatoryOptionsForAssignment(): array
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
            return $this->loadActiveLabUserOptionsForAssignment();
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
    private function loadActiveLabUserOptionsForAssignment(): array
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
}
