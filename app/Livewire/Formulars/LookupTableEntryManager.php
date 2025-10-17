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

class LookupTableEntryManager extends Component
{
    use WithPagination, WithFileUploads;

    public $lookupTable;
    public $search = '';
    public $perPage = 15;
    public $perPageOptions = [10, 15, 25, 50, 100];

    // Entry creation/editing
    public $showCreateModal = false;
    public $showEditModal = false;
    public $showImportModal = false;
    public $editingEntry = null;

    // Form fields
    public $entryKeys = [];
    public $entryValue = '';
    
    // Import/Export
    public $importFile;
    public $importPreview = [];
    public $importErrors = [];

    // Messages
    public $message = '';
    public $messageType = '';

    public function mount(LookupTable $lookupTable)
    {
        $this->lookupTable = $lookupTable;
        $this->initializeEntryKeys();
    }

    public function render()
    {
        $query = $this->lookupTable->entries();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('value', 'like', '%' . $this->search . '%')
                  ->orWhere('keys', 'like', '%' . $this->search . '%');
            });
        }

        $entries = $query->orderBy('created_at', 'desc')->paginate($this->perPage);

        return view('livewire.formulars.lookup-table-entry-manager', [
            'entries' => $entries,
        ]);
    }

    public function showCreateEntryModal()
    {
        $this->resetForm();
        $this->initializeEntryKeys();
        $this->showCreateModal = true;
    }

    public function showEditEntryModal(LookupTableEntry $entry)
    {
        $this->editingEntry = $entry;
        $this->entryKeys = is_array($entry->keys) ? $entry->keys : json_decode($entry->keys, true);
        $this->entryValue = $entry->value;
        $this->showEditModal = true;
    }

    public function createEntry()
    {
        $this->validate([
            'entryKeys' => 'required|array',
            'entryKeys.*' => 'required',
            'entryValue' => 'required|string',
        ]);

        try {
            $lookupService = app(LookupService::class);
            
            // Validate all keys are provided
            foreach ($this->lookupTable->key_columns as $keyColumn) {
                if (!isset($this->entryKeys[$keyColumn]) || $this->entryKeys[$keyColumn] === '') {
                    $this->setMessage("Please provide value for key: {$keyColumn}", 'error');
                    return;
                }
            }

            $lookupService->setValue($this->lookupTable->id, $this->entryKeys, $this->entryValue);

            $this->showCreateModal = false;
            $this->resetForm();
            $this->setMessage('Entry created successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error creating entry: ' . $e->getMessage(), 'error');
        }
    }

    public function updateEntry()
    {
        $this->validate([
            'entryKeys' => 'required|array',
            'entryKeys.*' => 'required',
            'entryValue' => 'required|string',
        ]);

        try {
            $lookupService = app(LookupService::class);
            
            // Delete old entry
            if ($this->editingEntry) {
                $oldKeys = is_array($this->editingEntry->keys) ? $this->editingEntry->keys : json_decode($this->editingEntry->keys, true);
                $lookupService->deleteValue($this->lookupTable->id, $oldKeys);
            }

            // Create new entry with updated keys
            $lookupService->setValue($this->lookupTable->id, $this->entryKeys, $this->entryValue);

            $this->showEditModal = false;
            $this->resetForm();
            $this->setMessage('Entry updated successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error updating entry: ' . $e->getMessage(), 'error');
        }
    }

    public function deleteEntry(LookupTableEntry $entry)
    {
        try {
            $lookupService = app(LookupService::class);
            $keys = is_array($entry->keys) ? $entry->keys : json_decode($entry->keys, true);
            $lookupService->deleteValue($this->lookupTable->id, $keys);
            
            $this->setMessage('Entry deleted successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error deleting entry: ' . $e->getMessage(), 'error');
        }
    }

    public function openImportModal()
    {
        $this->importFile = null;
        $this->importPreview = [];
        $this->importErrors = [];
        $this->showImportModal = true;
    }

    public function downloadTemplate()
    {
        try {
            // Create header row with column names
            $headers = array_merge($this->lookupTable->key_columns, [$this->lookupTable->value_column]);
            
            // Create sample data row
            $sampleData = [];
            foreach ($headers as $header) {
                $sampleData[$header] = 'Sample ' . $header;
            }
            
            $data = [$sampleData];
            
            $filename = 'template_' . str_replace(' ', '_', $this->lookupTable->name) . '_' . now()->format('Y-m-d') . '.xlsx';

            return Excel::download(new class($data, $headers) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithHeadings {
                protected $data;
                protected $headers;

                public function __construct($data, $headers)
                {
                    $this->data = $data;
                    $this->headers = $headers;
                }

                public function array(): array
                {
                    return $this->data;
                }
                
                public function headings(): array
                {
                    return $this->headers;
                }
            }, $filename);
        } catch (\Exception $e) {
            $this->setMessage('Error generating template: ' . $e->getMessage(), 'error');
        }
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
            $result = $lookupService->validateTableStructure($this->lookupTable->id, $data);
            
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
            $result = $lookupService->importData($this->lookupTable->id, $data);

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

    public function clearSearch()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function dismissMessage()
    {
        $this->message = '';
        $this->messageType = '';
    }

    protected function initializeEntryKeys()
    {
        $this->entryKeys = [];
        foreach ($this->lookupTable->key_columns as $keyColumn) {
            $this->entryKeys[$keyColumn] = '';
        }
    }

    protected function resetForm()
    {
        $this->initializeEntryKeys();
        $this->entryValue = '';
        $this->editingEntry = null;
    }

    protected function setMessage(string $message, string $type)
    {
        $this->message = $message;
        $this->messageType = $type;
    }
}

