<?php

namespace App\Livewire\Equipment;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Equipments\EquipmentDisposal;
use App\Models\Equipments\Equipment;
use App\Models\Equipments\EquipmentDisposalFile;
use App\Services\Equipment\DisposalWorkflowService;
use App\Services\Equipment\DisposalAuditService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\File;
use Carbon\Carbon;

class DisposalRequestForm extends Component
{
    use WithFileUploads;

    public $equipmentId = null;
    public $disposalId = null;
    public $equipment = null;
    public $disposal = null;
    public $showModal = false;

    // Form data
    public $form = [
        'equipment_id' => null,
        'justification' => '',
        'proposed_method' => '',
        'risk_level' => '',
        'regulatory_category' => '',
    ];

    // File uploads
    public $evidenceFiles = [];
    public $uploadedFiles = [];

    // Equipment metadata (autofilled)
    public $equipmentMetadata = [];

    // Messages
    public $message = '';
    public $messageType = 'success';

    protected $workflowService;
    protected $auditService;

    protected $listeners = ['open-disposal-form' => 'openModal'];

    protected function rules(): array
    {
        return [
            'form.equipment_id' => 'required|integer|exists:equipment,id',
            'form.justification' => 'required|string|min:10',
            'form.proposed_method' => 'required|in:scrap,donation,auction,recycling,destruction',
            'form.risk_level' => 'required|in:Low,Medium,High,Critical',
            'form.regulatory_category' => 'required|string|max:255',
            'evidenceFiles.*' => 'nullable|file|max:10240', // 10MB max per file
        ];
    }

    protected $messages = [
        'form.justification.required' => 'Justification is required.',
        'form.justification.min' => 'Justification must be at least 10 characters.',
        'form.proposed_method.required' => 'Proposed disposal method is required.',
        'form.risk_level.required' => 'Risk assessment is required.',
        'form.regulatory_category.required' => 'Regulatory category is required.',
    ];

    public function boot(DisposalWorkflowService $workflowService, DisposalAuditService $auditService)
    {
        $this->workflowService = $workflowService;
        $this->auditService = $auditService;
    }

    public function mount(?int $equipmentId = null, ?int $disposalId = null): void
    {
        if ($disposalId) {
            $this->loadDisposal($disposalId);
        } elseif ($equipmentId) {
            $this->equipmentId = $equipmentId;
            $this->loadEquipment();
        }
    }

    public function loadEquipment(): void
    {
        if (!$this->equipmentId) {
            return;
        }

        $this->equipment = Equipment::with([
            'maintainance_Calibration_logs' => function($query) {
                $query->orderBy('date', 'desc')->limit(5);
            }
        ])->find($this->equipmentId);

        if (!$this->equipment) {
            $this->message = 'Equipment not found.';
            $this->messageType = 'danger';
            return;
        }

        // Check if equipment can be disposed
        if (!$this->equipment->canBeDisposed()) {
            $this->message = 'This equipment already has an active disposal request.';
            $this->messageType = 'warning';
            return;
        }

        // Autofill form with equipment metadata
        $this->form['equipment_id'] = $this->equipment->id;
        $this->loadEquipmentMetadata();
    }

    public function loadEquipmentMetadata(): void
    {
        if (!$this->equipment) {
            return;
        }

        $lastLogs = $this->equipment->last_logs();
        $calibrationStatus = $lastLogs['calibration'] ? 
            'Last calibrated: ' . Carbon::parse($lastLogs['calibration']->date)->format('Y-m-d') : 
            'Not calibrated';
        
        $maintenanceStatus = $lastLogs['maintainance'] ? 
            'Last maintained: ' . Carbon::parse($lastLogs['maintainance']->date)->format('Y-m-d') : 
            'Not maintained';

        $this->equipmentMetadata = [
            'id' => $this->equipment->id,
            'equipment_number' => $this->equipment->equipment_number,
            'name' => $this->equipment->name,
            'serial_number' => $this->equipment->serial_number,
            'model' => $this->equipment->model,
            'make' => $this->equipment->make,
            'calibration_status' => $calibrationStatus,
            'maintenance_status' => $maintenanceStatus,
            'condition' => $this->equipment->condition,
            'assigned_department' => $this->equipment->assigned_department,
            'date_placed_in_service' => $this->equipment->date_purchased,
        ];
    }

    public function loadDisposal(int $disposalId): void
    {
        $this->disposal = EquipmentDisposal::with(['equipment', 'files'])->find($disposalId);

        if (!$this->disposal) {
            $this->message = 'Disposal request not found.';
            $this->messageType = 'danger';
            return;
        }

        if (!$this->disposal->canBeEdited()) {
            $this->message = 'This disposal request cannot be edited.';
            $this->messageType = 'warning';
            return;
        }

        $this->disposalId = $disposalId;
        $this->equipmentId = $this->disposal->equipment_id;
        $this->equipment = $this->disposal->equipment;

        // Load form data
        $this->form = [
            'equipment_id' => $this->disposal->equipment_id,
            'justification' => $this->disposal->justification,
            'proposed_method' => $this->disposal->proposed_method,
            'risk_level' => $this->disposal->risk_level,
            'regulatory_category' => $this->disposal->regulatory_category,
        ];

        $this->uploadedFiles = $this->disposal->files;
        $this->loadEquipmentMetadata();
    }

    public function openModal(?int $equipmentId = null): void
    {
        $this->resetForm();
        if ($equipmentId) {
            $this->equipmentId = $equipmentId;
            $this->loadEquipment();
        }
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->form = [
            'equipment_id' => null,
            'justification' => '',
            'proposed_method' => '',
            'risk_level' => '',
            'regulatory_category' => '',
        ];
        $this->evidenceFiles = [];
        $this->uploadedFiles = [];
        $this->equipmentMetadata = [];
        $this->equipmentId = null;
        $this->disposalId = null;
        $this->equipment = null;
        $this->disposal = null;
        $this->message = '';
        $this->messageType = 'success';
    }

    public function removeFile($fileId): void
    {
        if ($this->disposalId) {
            $file = EquipmentDisposalFile::find($fileId);
            if ($file && $file->disposal_id == $this->disposalId) {
                // Delete physical file
                if (Storage::disk('public')->exists(str_replace('/storage/', '', $file->file_path))) {
                    Storage::disk('public')->delete(str_replace('/storage/', '', $file->file_path));
                }
                
                // Log deletion
                $this->auditService->logFileDeletion($this->disposal, $file->file_name);
                
                // Delete record
                $file->delete();
                
                // Reload files
                $this->uploadedFiles = $this->disposal->fresh()->files;
                
                $this->message = 'File removed successfully.';
                $this->messageType = 'success';
            }
        }
    }

    public function save(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $oldValues = null;
            $action = 'created';

            if ($this->disposalId && $this->disposal) {
                // Update existing disposal
                $oldValues = $this->disposal->toArray();
                $action = 'updated';

                $this->disposal->justification = $this->form['justification'];
                $this->disposal->proposed_method = $this->form['proposed_method'];
                $this->disposal->risk_level = $this->form['risk_level'];
                $this->disposal->regulatory_category = $this->form['regulatory_category'];
                $this->disposal->save();

                $newValues = $this->disposal->toArray();
                $this->auditService->logUpdate($this->disposal, $oldValues, $newValues);
            } else {
                // Create new disposal
                $this->disposal = EquipmentDisposal::create([
                    'equipment_id' => $this->form['equipment_id'],
                    'justification' => $this->form['justification'],
                    'proposed_method' => $this->form['proposed_method'],
                    'risk_level' => $this->form['risk_level'],
                    'regulatory_category' => $this->form['regulatory_category'],
                    'requested_by' => auth()->id(),
                    'status' => 'draft',
                    'company_id' => getUserCompany(),
                ]);

                $this->disposalId = $this->disposal->id;
                $this->auditService->logCreation($this->disposal);
            }

            // Handle file uploads
            if (!empty($this->evidenceFiles)) {
                foreach ($this->evidenceFiles as $file) {
                    if ($file) {
                        $path = $file->store('equipment-disposals/' . $this->disposal->id, 'public');
                        $relativePath = '/storage/' . $path;

                        EquipmentDisposalFile::create([
                            'disposal_id' => $this->disposal->id,
                            'file_path' => $relativePath,
                            'file_name' => $file->getClientOriginalName(),
                            'file_type' => $this->determineFileType($file),
                            'mime_type' => $file->getMimeType(),
                            'file_size' => $file->getSize(),
                            'uploaded_by' => auth()->id(),
                        ]);

                        $this->auditService->logFileUpload(
                            $this->disposal,
                            $file->getClientOriginalName(),
                            $this->determineFileType($file)
                        );
                    }
                }
            }

            DB::commit();

            $this->message = $action === 'created' ? 
                'Disposal request created successfully!' : 
                'Disposal request updated successfully!';
            $this->messageType = 'success';

            // Reload disposal with files
            $this->disposal = $this->disposal->fresh(['files']);
            $this->uploadedFiles = $this->disposal->files;
            $this->evidenceFiles = [];

            // Emit event to refresh parent component
            $this->dispatch('disposal-saved', ['disposal_id' => $this->disposal->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->message = 'Error saving disposal request: ' . $e->getMessage();
            $this->messageType = 'danger';
        }
    }

    public function submit(): void
    {
        $this->save();

        if ($this->messageType === 'success' && $this->disposal) {
            // Initialize workflow
            $result = $this->workflowService->initializeWorkflow($this->disposal);

            if ($result['success']) {
                $this->message = 'Disposal request submitted successfully! Approval workflow initiated.';
                $this->dispatch('disposal-submitted', ['disposal_id' => $this->disposal->id]);
            } else {
                $this->message = 'Disposal saved but workflow initialization failed: ' . $result['message'];
                $this->messageType = 'warning';
            }
        }
    }

    protected function determineFileType($file): string
    {
        $mimeType = $file->getMimeType();
        
        if (str_starts_with($mimeType, 'image/')) {
            return 'photo';
        }
        
        return 'document';
    }

    public function dismissMessage(): void
    {
        $this->message = '';
        $this->messageType = 'success';
    }

    public function render()
    {
        $equipmentList = Equipment::where('company_id', getUserCompany())
            ->where('is_disposal', 0)
            ->where('active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'equipment_number']);

        return view('livewire.equipment.disposal-request-form', [
            'equipmentList' => $equipmentList,
        ]);
    }
}

