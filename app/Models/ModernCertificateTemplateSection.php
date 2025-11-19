<?php

namespace App\Models;

use App\CertificateTemplateSection;
use Illuminate\Support\Str;

class ModernCertificateTemplateSection
{
    protected CertificateTemplateSection $section;

    public function __construct(CertificateTemplateSection $section)
    {
        $this->section = $section;
    }

    /**
     * Get the underlying section model.
     */
    public function getSection(): CertificateTemplateSection
    {
        return $this->section;
    }

    /**
     * Get the layout structure.
     */
    public function getLayoutStructure(): array
    {
        return $this->section->layout_structure ?? ['rows' => []];
    }

    /**
     * Set the layout structure.
     */
    public function setLayoutStructure(array $structure): void
    {
        $this->section->layout_structure = $structure;
        $this->section->save();
    }

    /**
     * Check if section has rows/columns (should appear in preview).
     */
    public function hasLayout(): bool
    {
        $layout = $this->getLayoutStructure();
        return !empty($layout['rows']) && is_array($layout['rows']);
    }

    /**
     * Add a row to the layout.
     */
    public function addRow(array $rowData = []): string
    {
        $layout = $this->getLayoutStructure();
        $rowId = $rowData['id'] ?? 'row-' . Str::random(8);
        
        $row = array_merge([
            'id' => $rowId,
            'columns' => []
        ], $rowData);
        
        if (!isset($layout['rows'])) {
            $layout['rows'] = [];
        }
        
        $layout['rows'][] = $row;
        $this->setLayoutStructure($layout);
        
        return $rowId;
    }

    /**
     * Add a column to a row.
     */
    public function addColumn(string $rowId, array $columnData = []): string
    {
        $layout = $this->getLayoutStructure();
        $columnId = $columnData['id'] ?? 'col-' . Str::random(8);
        
        $column = array_merge([
            'id' => $columnId,
            'width' => '50%',
            'cells' => []
        ], $columnData);
        
        foreach ($layout['rows'] as &$row) {
            if ($row['id'] === $rowId) {
                $row['columns'][] = $column;
                break;
            }
        }
        
        $this->setLayoutStructure($layout);
        
        return $columnId;
    }

    /**
     * Add a cell to a column.
     */
    public function addCell(string $columnId, array $cellData = []): string
    {
        $layout = $this->getLayoutStructure();
        $cellId = $cellData['id'] ?? 'cell-' . Str::random(8);
        
        $cell = array_merge([
            'id' => $cellId,
            'elements' => [],
            'sub_sections' => []
        ], $cellData);
        
        foreach ($layout['rows'] as &$row) {
            foreach ($row['columns'] as &$column) {
                if ($column['id'] === $columnId) {
                    $column['cells'][] = $cell;
                    break 2;
                }
            }
        }
        
        $this->setLayoutStructure($layout);
        
        return $cellId;
    }

    /**
     * Get a cell by ID.
     */
    public function getCell(string $cellId): ?array
    {
        $layout = $this->getLayoutStructure();
        
        foreach ($layout['rows'] ?? [] as $row) {
            foreach ($row['columns'] ?? [] as $column) {
                foreach ($column['cells'] ?? [] as $cell) {
                    if ($cell['id'] === $cellId) {
                        return $cell;
                    }
                }
            }
        }
        
        return null;
    }

    /**
     * Update a cell.
     */
    public function updateCell(string $cellId, array $cellData): bool
    {
        $layout = $this->getLayoutStructure();
        $updated = false;
        
        foreach ($layout['rows'] as &$row) {
            foreach ($row['columns'] as &$column) {
                foreach ($column['cells'] as &$cell) {
                    if ($cell['id'] === $cellId) {
                        $cell = array_merge($cell, $cellData);
                        $updated = true;
                        break 3;
                    }
                }
            }
        }
        
        if ($updated) {
            $this->setLayoutStructure($layout);
        }
        
        return $updated;
    }

    /**
     * Remove a row.
     */
    public function removeRow(string $rowId): bool
    {
        $layout = $this->getLayoutStructure();
        $removed = false;
        
        foreach ($layout['rows'] as $key => $row) {
            if ($row['id'] === $rowId) {
                unset($layout['rows'][$key]);
                $layout['rows'] = array_values($layout['rows']); // Re-index
                $removed = true;
                break;
            }
        }
        
        if ($removed) {
            $this->setLayoutStructure($layout);
        }
        
        return $removed;
    }

    /**
     * Remove a column.
     */
    public function removeColumn(string $columnId): bool
    {
        $layout = $this->getLayoutStructure();
        $removed = false;
        
        foreach ($layout['rows'] as &$row) {
            foreach ($row['columns'] as $key => $column) {
                if ($column['id'] === $columnId) {
                    unset($row['columns'][$key]);
                    $row['columns'] = array_values($row['columns']); // Re-index
                    $removed = true;
                    break 2;
                }
            }
        }
        
        if ($removed) {
            $this->setLayoutStructure($layout);
        }
        
        return $removed;
    }

    /**
     * Remove a cell.
     */
    public function removeCell(string $cellId): bool
    {
        $layout = $this->getLayoutStructure();
        $removed = false;
        
        foreach ($layout['rows'] as &$row) {
            foreach ($row['columns'] as &$column) {
                foreach ($column['cells'] as $key => $cell) {
                    if ($cell['id'] === $cellId) {
                        unset($column['cells'][$key]);
                        $column['cells'] = array_values($column['cells']); // Re-index
                        $removed = true;
                        break 3;
                    }
                }
            }
        }
        
        if ($removed) {
            $this->setLayoutStructure($layout);
        }
        
        return $removed;
    }

    /**
     * Add an element to a cell.
     */
    public function addElementToCell(string $cellId, string $elementId): bool
    {
        $cell = $this->getCell($cellId);
        if (!$cell) {
            return false;
        }
        
        if (!in_array($elementId, $cell['elements'] ?? [])) {
            $cell['elements'][] = $elementId;
            return $this->updateCell($cellId, $cell);
        }
        
        return true;
    }

    /**
     * Remove an element from a cell.
     */
    public function removeElementFromCell(string $cellId, string $elementId): bool
    {
        $cell = $this->getCell($cellId);
        if (!$cell) {
            return false;
        }
        
        $elements = $cell['elements'] ?? [];
        $key = array_search($elementId, $elements);
        
        if ($key !== false) {
            unset($elements[$key]);
            $cell['elements'] = array_values($elements);
            return $this->updateCell($cellId, $cell);
        }
        
        return false;
    }

    /**
     * Add a sub-section to a cell.
     */
    public function addSubSectionToCell(string $cellId, string $subSectionId): bool
    {
        $cell = $this->getCell($cellId);
        if (!$cell) {
            return false;
        }
        
        if (!in_array($subSectionId, $cell['sub_sections'] ?? [])) {
            $cell['sub_sections'][] = $subSectionId;
            return $this->updateCell($cellId, $cell);
        }
        
        return true;
    }

    /**
     * Remove a sub-section from a cell.
     */
    public function removeSubSectionFromCell(string $cellId, string $subSectionId): bool
    {
        $cell = $this->getCell($cellId);
        if (!$cell) {
            return false;
        }
        
        $subSections = $cell['sub_sections'] ?? [];
        $key = array_search($subSectionId, $subSections);
        
        if ($key !== false) {
            unset($subSections[$key]);
            $cell['sub_sections'] = array_values($subSections);
            return $this->updateCell($cellId, $cell);
        }
        
        return false;
    }

    /**
     * Get CSS configuration.
     */
    public function getCssConfig(): array
    {
        return $this->section->css_config ?? [];
    }

    /**
     * Set CSS configuration.
     */
    public function setCssConfig(array $config): void
    {
        $this->section->css_config = $config;
        $this->section->save();
    }

    /**
     * Get data configuration.
     */
    public function getDataConfig(): array
    {
        return $this->section->data_config ?? [];
    }

    /**
     * Set data configuration.
     */
    public function setDataConfig(array $config): void
    {
        $this->section->data_config = $config;
        $this->section->save();
    }
}




