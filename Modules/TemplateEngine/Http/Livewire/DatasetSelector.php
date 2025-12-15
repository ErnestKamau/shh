<?php

namespace Modules\TemplateEngine\Http\Livewire;

use Livewire\Component;
use Modules\TemplateEngine\Services\DatabaseMetadataService;

class DatasetSelector extends Component
{
    public $tables = [];
    public $columns = [];
    
    public $selectedTable;
    public $selectedValueColumn;
    public $selectedLabelColumn;
    
    public function mount($binding = [])
    {
        $service = app(DatabaseMetadataService::class);
        $this->tables = $service->getTables();
        
        if (!empty($binding)) {
            $this->selectedTable = $binding['table_name'] ?? null;
            $this->selectedValueColumn = $binding['column_value'] ?? null;
            $this->selectedLabelColumn = $binding['column_label'] ?? null;
            
            if ($this->selectedTable) {
                $this->columns = $service->getColumns($this->selectedTable);
            }
        }
    }

    public function updatedSelectedTable($value)
    {
        $service = app(DatabaseMetadataService::class);
        $this->columns = $service->getColumns($value);
        $this->selectedValueColumn = null;
        $this->selectedLabelColumn = null;
        $this->emitData();
    }
    
    public function updatedSelectedValueColumn() { $this->emitData(); }
    public function updatedSelectedLabelColumn() { $this->emitData(); }

    public function emitData()
    {
        if ($this->selectedTable && $this->selectedValueColumn && $this->selectedLabelColumn) {
            $this->dispatch('datasetSelected', [
                'table_name' => $this->selectedTable,
                'column_value' => $this->selectedValueColumn,
                'column_label' => $this->selectedLabelColumn,
                // filters can be added here
            ]);
        }
    }

    public function render()
    {
        return view('template-engine::livewire.dataset-selector');
    }
}
