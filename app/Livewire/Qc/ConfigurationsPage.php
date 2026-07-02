<?php

namespace App\Livewire\Qc;

use App\Models\QcModule\Configurations\Approvers;
use App\Models\QcModule\Configurations\QcSchemes;
use App\Models\QcModule\Configurations\QcTypes;
use App\Standards;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ConfigurationsPage extends Component
{
    public string $activeTab = 'standards';

    public string $search = '';

    public ?string $editingQcTypeId = null;
    public string $qcTypeName = '';
    public string $qcTypeCode = '';
    public bool $qcTypeHasStandards = true;
    public bool $qcTypeHasConfiguredSamples = false;
    public bool $qcTypeUseExistingSample = false;
    public bool $qcTypeIsActive = true;

    public ?string $editingSchemeId = null;
    public string $schemeName = '';
    public string $schemeCode = '';
    public bool $schemeIsActive = true;

    public ?string $editingStandardId = null;
    public string $standardName = '';
    public string $standardCode = '';
    public string $standardQcTypeId = '';
    /** @var array<int, string> */
    public array $standardSchemeIds = [];
    public bool $standardIsActive = true;

    public ?string $editingApproverId = null;
    public string $approverPersonnelId = '';

    protected function rules(): array
    {
        return [
            'qcTypeName' => ['required', 'string', 'max:100'],
            'qcTypeCode' => ['required', 'string', 'max:50'],
            'schemeName' => ['required', 'string', 'max:100'],
            'schemeCode' => ['required', 'string', 'max:50'],
            'standardName' => ['required', 'string', 'max:255'],
            'standardCode' => ['required', 'string', 'max:100'],
            'standardQcTypeId' => ['required', 'string', Rule::exists('qc_types', 'id')],
            'standardSchemeIds' => ['array'],
            'standardSchemeIds.*' => ['string', Rule::exists('qc_scheme', 'id')],
            'approverPersonnelId' => ['required', 'string', Rule::exists('users', 'id')],
        ];
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function resetQcTypeForm(): void
    {
        $this->editingQcTypeId = null;
        $this->qcTypeName = '';
        $this->qcTypeCode = '';
        $this->qcTypeHasStandards = true;
        $this->qcTypeHasConfiguredSamples = false;
        $this->qcTypeUseExistingSample = false;
        $this->qcTypeIsActive = true;
    }

    public function editQcType(string $id): void
    {
        $record = QcTypes::query()->findOrFail($id);

        $this->editingQcTypeId = (string) $record->id;
        $this->qcTypeName = (string) $record->name;
        $this->qcTypeCode = (string) $record->code;
        $this->qcTypeHasStandards = (bool) $record->has_standards;
        $this->qcTypeHasConfiguredSamples = (bool) $record->has_configured_samples;
        $this->qcTypeUseExistingSample = (bool) $record->use_existing_sample;
        $this->qcTypeIsActive = (bool) $record->is_active;
        $this->activeTab = 'types';
    }

    public function saveQcType(): void
    {
        $this->validateOnly('qcTypeName');
        $this->validateOnly('qcTypeCode');

        $record = $this->editingQcTypeId
            ? QcTypes::query()->findOrFail($this->editingQcTypeId)
            : new QcTypes();

        $record->name = $this->qcTypeName;
        $record->code = $this->qcTypeCode;
        $record->has_standards = $this->qcTypeHasStandards ? 1 : 0;
        $record->has_configured_samples = $this->qcTypeHasConfiguredSamples ? 1 : 0;
        $record->use_existing_sample = $this->qcTypeUseExistingSample ? 1 : 0;
        $record->is_active = $this->qcTypeIsActive ? 1 : 0;

        if (! $record->exists && Auth::id()) {
            $record->created_by = (string) Auth::id();
        }

        $record->save();

        session()->flash('success', 'QC type saved successfully.');
        $this->resetQcTypeForm();
    }

    public function deactivateQcType(string $id): void
    {
        $record = QcTypes::query()->findOrFail($id);
        $record->is_active = 0;
        $record->save();

        session()->flash('success', 'QC type deactivated successfully.');
    }

    public function resetSchemeForm(): void
    {
        $this->editingSchemeId = null;
        $this->schemeName = '';
        $this->schemeCode = '';
        $this->schemeIsActive = true;
    }

    public function editScheme(string $id): void
    {
        $record = QcSchemes::query()->findOrFail($id);

        $this->editingSchemeId = (string) $record->id;
        $this->schemeName = (string) $record->name;
        $this->schemeCode = (string) $record->code;
        $this->schemeIsActive = (bool) $record->is_active;
        $this->activeTab = 'schemes';
    }

    public function saveScheme(): void
    {
        $this->validateOnly('schemeName');
        $this->validateOnly('schemeCode');

        $record = $this->editingSchemeId
            ? QcSchemes::query()->findOrFail($this->editingSchemeId)
            : new QcSchemes();

        $record->name = $this->schemeName;
        $record->code = $this->schemeCode;
        $record->is_active = $this->schemeIsActive ? 1 : 0;
        $record->save();

        session()->flash('success', 'QC scheme saved successfully.');
        $this->resetSchemeForm();
    }

    public function deleteScheme(string $id): void
    {
        DB::table('qc_scheme')->where('id', $id)->delete();
        session()->flash('success', 'QC scheme deleted successfully.');
    }

    public function resetStandardForm(): void
    {
        $this->editingStandardId = null;
        $this->standardName = '';
        $this->standardCode = '';
        $this->standardQcTypeId = '';
        $this->standardSchemeIds = [];
        $this->standardIsActive = true;
    }

    public function editStandard(string $id): void
    {
        $record = Standards::query()->findOrFail($id);

        $this->editingStandardId = (string) $record->id;
        $this->standardName = (string) $record->name;
        $this->standardCode = (string) $record->code;
        $this->standardQcTypeId = (string) ($record->qc_type_id ?? '');
        $this->standardSchemeIds = array_values(array_filter(explode(',', (string) ($record->qc_scheme_ids ?? ''))));
        $this->standardIsActive = (bool) $record->status;
        $this->activeTab = 'standards';
    }

    public function saveStandard(): void
    {
        $this->validateOnly('standardName');
        $this->validateOnly('standardCode');
        $this->validateOnly('standardQcTypeId');
        $this->validateOnly('standardSchemeIds');

        $record = $this->editingStandardId
            ? Standards::query()->findOrFail($this->editingStandardId)
            : new Standards();

        $record->name = $this->standardName;
        $record->code = $this->standardCode;
        $record->is_qc_standard = 1;
        $record->qc_type_id = $this->standardQcTypeId;
        $record->qc_scheme_ids = implode(',', $this->standardSchemeIds);
        $record->status = $this->standardIsActive ? 1 : 0;
        $record->edited_by = Auth::id() ? (string) Auth::id() : null;
        $record->save();

        session()->flash('success', 'QC standard saved successfully.');
        $this->resetStandardForm();
    }

    public function deactivateStandard(string $id): void
    {
        $record = Standards::query()->findOrFail($id);
        $record->status = 0;
        $record->save();

        session()->flash('success', 'QC standard deactivated successfully.');
    }

    public function resetApproverForm(): void
    {
        $this->editingApproverId = null;
        $this->approverPersonnelId = '';
    }

    public function editApprover(string $id): void
    {
        $record = Approvers::query()->findOrFail($id);

        $this->editingApproverId = (string) $record->id;
        $this->approverPersonnelId = (string) $record->personnel_id;
        $this->activeTab = 'approvals';
    }

    public function saveApprover(): void
    {
        $this->validateOnly('approverPersonnelId');

        $duplicateQuery = Approvers::query()->where('personnel_id', $this->approverPersonnelId);
        if ($this->editingApproverId) {
            $duplicateQuery->where('id', '!=', $this->editingApproverId);
        }

        if ($duplicateQuery->exists()) {
            $this->addError('approverPersonnelId', 'This user is already configured as a QC approver.');

            return;
        }

        $record = $this->editingApproverId
            ? Approvers::query()->findOrFail($this->editingApproverId)
            : new Approvers();

        $record->personnel_id = $this->approverPersonnelId;
        if (! $record->exists && Auth::id()) {
            $record->created_by = (string) Auth::id();
        }
        $record->save();

        session()->flash('success', 'QC approver saved successfully.');
        $this->resetApproverForm();
    }

    public function deleteApprover(string $id): void
    {
        DB::table('qc_approvers_config')->where('id', $id)->delete();
        session()->flash('success', 'QC approver deleted successfully.');
    }

    public function render()
    {
        $searchText = trim($this->search);

        $qcTypes = QcTypes::query()
            ->when($searchText !== '', function ($query) use ($searchText) {
                $query->where(function ($inner) use ($searchText) {
                    $inner->where('name', 'like', '%' . $searchText . '%')
                        ->orWhere('code', 'like', '%' . $searchText . '%');
                });
            })
            ->orderBy('name')
            ->get();

        $qcSchemes = QcSchemes::query()
            ->when($searchText !== '', function ($query) use ($searchText) {
                $query->where(function ($inner) use ($searchText) {
                    $inner->where('name', 'like', '%' . $searchText . '%')
                        ->orWhere('code', 'like', '%' . $searchText . '%');
                });
            })
            ->orderBy('name')
            ->get();

        $standards = Standards::query()
            ->where('is_qc_standard', 1)
            ->when($searchText !== '', function ($query) use ($searchText) {
                $query->where(function ($inner) use ($searchText) {
                    $inner->where('name', 'like', '%' . $searchText . '%')
                        ->orWhere('code', 'like', '%' . $searchText . '%');
                });
            })
            ->orderByDesc('updated_at')
            ->get();

        $approvals = Approvers::query()->orderByDesc('created_at')->get();
        $staffs = User::query()
            ->where('active', 1)
            ->where('is_support_staff', 0)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('livewire.qc.configurations-page', [
            'qcTypes' => $qcTypes,
            'qcSchemes' => $qcSchemes,
            'standards' => $standards,
            'approvals' => $approvals,
            'staffs' => $staffs,
        ]);
    }
}
