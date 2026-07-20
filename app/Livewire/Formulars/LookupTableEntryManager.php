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
    
    // Range-based fields
    public $rangeLow = '';
    public $rangeHigh = '';
    public $isOpenEnded = false;
    public $valueInterpretation = '';
    
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

    public function showEditEntryModal(string $entryId): void
    {
        $entry = LookupTableEntry::findOrFail($entryId);

        $this->editingEntry = $entry;
        $keys = is_array($entry->keys) ? $entry->keys : json_decode($entry->keys, true);

        if ($this->lookupTable->isRangeBased()) {
            $this->rangeLow = $keys['low'] ?? '';
            $this->rangeHigh = $keys['high'] ?? '';
            $this->isOpenEnded = $keys['high'] === null;
            $this->valueInterpretation = $keys['value_interpretation'] ?? '';
        } else {
            $this->entryKeys = $keys;
        }

        $this->entryValue = $entry->value;
        $this->showEditModal = true;
    }

    public function createEntry()
    {
        try {
            $lookupService = app(LookupService::class);
            
            if ($this->lookupTable->isRangeBased()) {
                // Range-based validation
                $this->validate([
                    'rangeLow' => 'required|numeric',
                    'rangeHigh' => $this->isOpenEnded ? 'nullable' : 'required|numeric|gt:rangeLow',
                    'entryValue' => 'required|string',
                ]);
                
                // Build keys for range
                $keys = [
                    'low' => (float)$this->rangeLow,
                    'high' => $this->isOpenEnded ? null : (float)$this->rangeHigh,
                ];
                
                // Add interpretation if table has it configured
                if ($this->lookupTable->value_interpretation_column && $this->valueInterpretation) {
                    $keys['value_interpretation'] = $this->valueInterpretation;
                }
                
                // Check for overlapping ranges
                if ($this->hasOverlappingRange($keys)) {
                    $this->setMessage('This range overlaps with an existing entry. Please adjust the range.', 'error');
                    return;
                }
                
            } else {
                // Key-value validation
                $this->validate([
                    'entryKeys' => 'required|array',
                    'entryKeys.*' => 'required',
                    'entryValue' => 'required|string',
                ]);
                
                $keys = $this->entryKeys;
                
                // Validate all keys are provided
                foreach ($this->lookupTable->key_columns as $keyColumn) {
                    if (!isset($keys[$keyColumn]) || $keys[$keyColumn] === '') {
                        $this->setMessage("Please provide value for key: {$keyColumn}", 'error');
                        return;
                    }
                }
            }

            $lookupService->setValue($this->lookupTable->id, $keys, $this->entryValue);

            $this->showCreateModal = false;
            $this->resetForm();
            $this->setMessage('Entry created successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error creating entry: ' . $e->getMessage(), 'error');
        }
    }

    public function updateEntry()
    {
        try {
            $lookupService = app(LookupService::class);
            
            if ($this->lookupTable->isRangeBased()) {
                // Range-based validation
                $this->validate([
                    'rangeLow' => 'required|numeric',
                    'rangeHigh' => $this->isOpenEnded ? 'nullable' : 'required|numeric|gt:rangeLow',
                    'entryValue' => 'required|string',
                ]);
                
                // Build keys for range
                $keys = [
                    'low' => (float)$this->rangeLow,
                    'high' => $this->isOpenEnded ? null : (float)$this->rangeHigh,
                ];
                
                // Add interpretation if table has it configured
                if ($this->lookupTable->value_interpretation_column && $this->valueInterpretation) {
                    $keys['value_interpretation'] = $this->valueInterpretation;
                }
                
                // Check for overlapping ranges (excluding current entry)
                if ($this->hasOverlappingRange($keys, $this->editingEntry->id)) {
                    $this->setMessage('This range overlaps with an existing entry. Please adjust the range.', 'error');
                    return;
                }
                
            } else {
                // Key-value validation
                $this->validate([
                    'entryKeys' => 'required|array',
                    'entryKeys.*' => 'required',
                    'entryValue' => 'required|string',
                ]);
                
                $keys = $this->entryKeys;
            }
            
            // Delete old entry
            if ($this->editingEntry) {
                $oldKeys = is_array($this->editingEntry->keys) ? $this->editingEntry->keys : json_decode($this->editingEntry->keys, true);
                $lookupService->deleteValue($this->lookupTable->id, $oldKeys);
            }

            // Create new entry with updated keys
            $lookupService->setValue($this->lookupTable->id, $keys, $this->entryValue);

            $this->showEditModal = false;
            $this->resetForm();
            $this->setMessage('Entry updated successfully!', 'success');
        } catch (\Exception $e) {
            $this->setMessage('Error updating entry: ' . $e->getMessage(), 'error');
        }
    }

    public function deleteEntry(string $entryId): void
    {
        try {
            $entry = LookupTableEntry::findOrFail($entryId);
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
            $headers = [];
            $sampleData = [];
            
            if ($this->lookupTable->isRangeBased()) {
                // Range-based template
                $headers = ['low', 'high', $this->lookupTable->value_column];
                if ($this->lookupTable->value_interpretation_column) {
                    $headers[] = 'value_interpretation';
                }
                
                // Sample data rows with examples
                $data = [
                    ['low' => 0, 'high' => 50, $this->lookupTable->value_column => 'Low', 'value_interpretation' => 'Below Average'],
                    ['low' => 50, 'high' => 75, $this->lookupTable->value_column => 'Medium', 'value_interpretation' => 'Average'],
                    ['low' => 75, 'high' => 90, $this->lookupTable->value_column => 'High', 'value_interpretation' => 'Above Average'],
                    ['low' => 90, 'high' => null, $this->lookupTable->value_column => 'Excellent', 'value_interpretation' => 'Outstanding'],
                ];
            } else {
                // Key-value template
                $headers = array_merge($this->lookupTable->key_columns, [$this->lookupTable->value_column]);
                
                // Sample data row
                $sampleData = [];
                foreach ($headers as $header) {
                    $sampleData[$header] = 'Sample ' . $header;
                }
                $data = [$sampleData];
            }
            
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
        $this->rangeLow = '';
        $this->rangeHigh = '';
        $this->isOpenEnded = false;
        $this->valueInterpretation = '';
        $this->editingEntry = null;
    }

    /**
     * Check if a range overlaps with existing ranges.
     */
    protected function hasOverlappingRange(array $keys, ?string $excludeEntryId = null): bool
    {
        $low = $keys['low'];
        $high = $keys['high'];
        
        $query = LookupTableEntry::where('lookup_table_id', $this->lookupTable->id);
        
        if ($excludeEntryId) {
            $query->where('id', '!=', $excludeEntryId);
        }
        
        $entries = $query->get();
        
        foreach ($entries as $entry) {
            $existingLow = isset($entry->keys['low']) ? (float)$entry->keys['low'] : null;
            $existingHigh = isset($entry->keys['high']) ? (float)$entry->keys['high'] : null;
            
            if ($existingLow === null) {
                continue;
            }
            
            // Check for overlap
            if ($high === null) {
                // New range is open-ended (low to infinity)
                // Overlaps if existing range starts within or after our low
                if ($existingHigh === null) {
                    // Both are open-ended - always overlap
                    return true;
                } elseif ($existingHigh > $low) {
                    // Existing range ends after our low
                    return true;
                }
            } else {
                // New range is bounded
                if ($existingHigh === null) {
                    // Existing range is open-ended
                    // Overlaps if our high is greater than existing low
                    if ($high > $existingLow) {
                        return true;
                    }
                } else {
                    // Both ranges are bounded
                    // Overlaps if ranges intersect
                    if ($low < $existingHigh && $high > $existingLow) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }

    protected function setMessage(string $message, string $type)
    {
        $this->message = $message;
        $this->messageType = $type;
    }
}

