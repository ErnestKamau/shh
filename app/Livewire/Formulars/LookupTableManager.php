<?php

namespace App\Livewire\Formulars;

use App\Models\Formulars\LookupTable;
use App\Models\Formulars\LookupTableEntry;
use App\Services\Formulars\LookupService;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class LookupTableManager extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $statusFilter = '';
    public $perPage = 10;
    public $perPageOptions = [10, 25, 50, 100];

    // Table creation/editing
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showImportModal = false;
    public $editingTable = null;

    // Form fields
    public $tableName = '';
    public $tableDescription = '';
    public $keyColumns = [];
    public $valueColumn = '';
    public $keyLabel = '';
    public $valueLabel = '';
    public $isActive = true;

    // Import/Export
    public $importFile;
    public $importPreview = [];
    public $importErrors = [];

    // Messages
    public $message = '';
    public $messageType = '';

    protected $rules = [
        'tableName' => 'required|string|max:255',
        'tableDescription' => 'nullable|string',
        'keyColumns' => 'required|array|min:1',
        'valueColumn' => 'required|string|max:255',
        'keyLabel' => 'nullable|string|max:255',
        'valueLabel' => 'nullable|string|max:255',
        'isActive' => 'boolean',
    ];

    public function mount()
    {
        $this->perPage = 10;
    }

    public function render()
    {
        $query = LookupTable::withCount('entries');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter) {
            $query->where('is_active', $this->statusFilter === 'active');
        }

        $tables = $query->orderBy('name')->paginate($this->perPage);

        return view('livewire.formulars.lookup-table-manager', [
            'tables' => $tables,
        ]);
    }

    public function showCreateTableModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function showEditTableModal(LookupTable $table)
    {
        $this->editingTable = $table;
        $this->tableName = $table->name;
        $this->tableDescription = $table->description;
        $this->keyColumns = $table->key_columns;
        $this->valueColumn = $table->value_column;
        $this->keyLabel = $table->key_label ?? '';
        $this->valueLabel = $table->value_label ?? '';
        $this->isActive = $table->is_active;
        $this->showEditModal = true;
    }

    public function showImportModal(LookupTable $table)
    {
        $this->editingTable = $table;
        $this->importFile = null;
        $this->importPreview = [];
        $this->importErrors = [];
        $this->showImportModal = true;
    }

    public function createTable()
    {
        $this->validate();

        try {
            LookupTable::create([
                'name' => $this->tableName,
                'description' => $this->tableDescription,
                'key_columns' => $this->keyColumns,
                'value_column' => $this->valueColumn,
                'key_label' => $this->keyLabel,
                'value_label' => $this->valueLabel,
                'is_active' => $this->isActive,
            ]);

            $this->showCreateModal = false;
            $this->resetForm();
            $this->setMessage('Lookup table created successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error creating lookup table: ' . $e->getMessage(), 'error');
        }
    }

    public function updateTable()
    {
        $this->rules['tableName'] = 'required|string|max:255|unique:lookup_tables,name,' . $this->editingTable->id;
        $this->validate();

        try {
            $this->editingTable->update([
                'name' => $this->tableName,
                'description' => $this->tableDescription,
                'key_columns' => $this->keyColumns,
                'value_column' => $this->valueColumn,
                'key_label' => $this->keyLabel,
                'value_label' => $this->valueLabel,
                'is_active' => $this->isActive,
            ]);

            $this->showEditModal = false;
            $this->resetForm();
            $this->setMessage('Lookup table updated successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error updating lookup table: ' . $e->getMessage(), 'error');
        }
    }

    public function deleteTable(LookupTable $table)
    {
        try {
            $table->delete();
            $this->setMessage('Lookup table deleted successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error deleting lookup table: ' . $e->getMessage(), 'error');
        }
    }

    public function toggleTableStatus(LookupTable $table)
    {
        try {
            $table->update(['is_active' => !$table->is_active]);
            $status = $table->is_active ? 'activated' : 'deactivated';
            $this->setMessage("Lookup table {$status} successfully!", 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error updating table status: ' . $e->getMessage(), 'error');
        }
    }

    public function addKeyColumn()
    {
        $this->keyColumns[] = '';
    }

    public function removeKeyColumn($index)
    {
        unset($this->keyColumns[$index]);
        $this->keyColumns = array_values($this->keyColumns);
    }

    public function previewImport()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $data = Excel::toArray(new class implements ToArray, WithHeadingRow {
                public function array(array $array): array
                {
                    return $array;
                }
            }, $this->importFile)[0];

            $this->importPreview = array_slice($data, 0, 10); // Show first 10 rows
            $this->importErrors = [];

            // Validate structure
            $lookupService = app(LookupService::class);
            $result = $lookupService->validateTableStructure($this->editingTable->id, $data);
            
            if (!$result['valid']) {
                $this->importErrors = $result['errors'];
            }
        } catch (\Exception $e) {
            $this->setMessage('Error reading import file: ' . $e->getMessage(), 'error');
        }
    }

    public function importData()
    {
        if (!$this->importFile) {
            $this->setMessage('Please select a file to import', 'error');
            return;
        }

        try {
            $data = Excel::toArray(new class implements ToArray, WithHeadingRow {
                public function array(array $array): array
                {
                    return $array;
                }
            }, $this->importFile)[0];

            $lookupService = app(LookupService::class);
            $result = $lookupService->importData($this->editingTable->id, $data);

            $this->showImportModal = false;
            $this->importFile = null;
            $this->importPreview = [];
            $this->importErrors = [];

            $message = "Import completed! {$result['imported']} rows imported successfully.";
            if (!empty($result['errors'])) {
                $message .= " " . count($result['errors']) . " errors occurred.";
            }
            
            $this->setMessage($message, $result['errors'] ? 'warning' : 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error importing data: ' . $e->getMessage(), 'error');
        }
    }

    public function exportTable(LookupTable $table)
    {
        try {
            $lookupService = app(LookupService::class);
            $data = $lookupService->exportData($table->id);

            $filename = 'lookup_table_' . $table->name . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            return Excel::download(new class($data) implements \Maatwebsite\Excel\Concerns\FromArray {
                protected $data;

                public function __construct($data)
                {
                    $this->data = $data;
                }

                public function array(): array
                {
                    return $this->data;
                }
            }, $filename);
        } catch (\Exception $e) {
            $this->setMessage('Error exporting table: ' . $e->getMessage(), 'error');
        }
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    protected function resetForm()
    {
        $this->tableName = '';
        $this->tableDescription = '';
        $this->keyColumns = [''];
        $this->valueColumn = '';
        $this->keyLabel = '';
        $this->valueLabel = '';
        $this->isActive = true;
        $this->editingTable = null;
    }

    protected function setMessage(string $message, string $type)
    {
        $this->message = $message;
        $this->messageType = $type;
    }
}