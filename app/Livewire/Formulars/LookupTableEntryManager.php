<?php

namespace App\Livewire\Formulars;

use App\Models\Formulars\LookupTable;
use App\Models\Formulars\LookupTableEntry;
use App\Services\Formulars\LookupService;
use Livewire\Component;
use Livewire\WithPagination;

class LookupTableEntryManager extends Component
{
    use WithPagination;

    public $lookupTable;
    public $search = '';
    public $perPage = 15;
    public $perPageOptions = [10, 15, 25, 50, 100];

    // Entry creation/editing
    public $showCreateModal = false;
    public $showEditModal = false;
    public $editingEntry = null;

    // Form fields
    public $entryKeys = [];
    public $entryValue = '';

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

