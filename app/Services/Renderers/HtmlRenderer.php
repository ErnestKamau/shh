<?php

namespace App\Services\Renderers;

use App\CertificateTemplate;
use App\CertificateTemplateSection;
use App\CertificateTemplateElement;
use App\Models\ModernCertificateTemplateSection;
use App\Models\ModernCertificateTemplateElement;
use App\Services\QueryBuilderService;

class HtmlRenderer
{
    protected QueryBuilderService $queryBuilder;

    public function __construct(QueryBuilderService $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Generate HTML from layout structure.
     */
    public function generateHtml(CertificateTemplate $template, ?array $layout = null): string
    {
        $html = '<div class="certificate-template" data-template-id="' . $template->id . '">';
        
        // Load sections
        $sections = $template->sections()->whereNull('parent_section_id')->orderBy('sort_order')->get();
        
        foreach ($sections as $section) {
            $html .= $this->renderSection($section);
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Render section with rows/columns/cells.
     */
    public function renderSection(CertificateTemplateSection $section, array $context = []): string
    {
        $modernSection = new ModernCertificateTemplateSection($section);
        
        // Don't render sections without layout
        if (!$modernSection->hasLayout()) {
            return '';
        }

        $layout = $modernSection->getLayoutStructure();
        $cssConfig = $modernSection->getCssConfig();
        
        // Build section CSS
        $sectionCss = $this->buildSectionCss($cssConfig);
        $sectionClass = 'section-' . $section->id;
        
        $html = '<section class="template-section ' . $sectionClass . '" data-section-id="' . $section->id . '"';
        if ($sectionCss) {
            $html .= ' style="' . htmlspecialchars($sectionCss) . '"';
        }
        $html .= '>';
        
        // Render rows
        foreach ($layout['rows'] ?? [] as $row) {
            $html .= $this->renderRow($row, $section);
        }
        
        $html .= '</section>';
        
        return $html;
    }

    /**
     * Render row.
     */
    protected function renderRow(array $row, CertificateTemplateSection $section): string
    {
        $html = '<div class="template-row" data-row-id="' . htmlspecialchars($row['id']) . '">';
        
        // Render columns
        foreach ($row['columns'] ?? [] as $column) {
            $html .= $this->renderColumn($column, $section);
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Render column.
     */
    protected function renderColumn(array $column, CertificateTemplateSection $section): string
    {
        $width = $column['width'] ?? '100%';
        $html = '<div class="template-column" data-column-id="' . htmlspecialchars($column['id']) . '" style="width: ' . htmlspecialchars($width) . ';">';
        
        // Render cells
        foreach ($column['cells'] ?? [] as $cell) {
            $html .= $this->renderCell($cell, $section);
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Render cell.
     */
    protected function renderCell(array $cell, CertificateTemplateSection $section): string
    {
        $html = '<div class="template-cell" data-cell-id="' . htmlspecialchars($cell['id']) . '">';
        
        // Render elements
        foreach ($cell['elements'] ?? [] as $elementId) {
            $element = CertificateTemplateElement::find($elementId);
            if ($element && $element->certificate_template_section_id == $section->id) {
                $html .= $this->renderElement($element);
            }
        }
        
        // Render sub-sections
        foreach ($cell['sub_sections'] ?? [] as $subSectionId) {
            $subSection = CertificateTemplateSection::find($subSectionId);
            if ($subSection) {
                $html .= $this->renderSection($subSection);
            }
        }
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Render element with CSS and data.
     */
    public function renderElement(CertificateTemplateElement $element): string
    {
        $modernElement = new ModernCertificateTemplateElement($element);
        $inlineCss = $modernElement->generateInlineCss();
        $customClass = $modernElement->getCustomClass();
        $customCss = $modernElement->getCustomCss();
        
        // Resolve data
        $content = $this->resolveElementData($element) ?? $element->content ?? '';
        
        // Build element attributes
        $attributes = [
            'class' => 'template-element element-' . $element->element_type . ($customClass ? ' ' . $customClass : ''),
            'data-element-id' => $element->id,
            'data-element-type' => $element->element_type
        ];
        
        if ($inlineCss) {
            $attributes['style'] = $inlineCss . ($customCss ? ' ' . $customCss : '');
        } elseif ($customCss) {
            $attributes['style'] = $customCss;
        }
        
        // Add element-specific attributes
        $properties = $element->properties ?? [];
        if ($element->element_type === 'image') {
            $attributes['src'] = htmlspecialchars($content);
            $attributes['alt'] = htmlspecialchars($properties['alt_text'] ?? '');
        }
        
        $tag = $this->getElementTag($element);
        $html = '<' . $tag;
        foreach ($attributes as $key => $value) {
            if ($value !== null && $value !== '') {
                $html .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
            }
        }
        
        // Add input type attribute for form inputs
        if (in_array($element->element_type, ['input_text', 'input_email', 'input_number', 'input_date'])) {
            $inputType = str_replace('input_', '', $element->element_type);
            $attributes['type'] = $inputType;
        }
        
        // Self-closing tags
        if (in_array($tag, ['img', 'hr', 'input'])) {
            $html .= ' />';
        } else {
            $html .= '>';
            $html .= $this->renderElementContent($element, $content);
            $html .= '</' . $tag . '>';
        }
        
        return $html;
    }

    /**
     * Get HTML tag for element type.
     */
    protected function getElementTag(CertificateTemplateElement $element): string
    {
        $elementType = $element->element_type;
        $properties = $element->properties ?? [];
        
        if ($elementType === 'heading') {
            // Support H1-H6 based on properties
            $level = $properties['level'] ?? 1;
            $level = max(1, min(6, (int)$level)); // Clamp between 1-6
            return "h{$level}";
        }
        
        return match($elementType) {
            'paragraph' => 'p',
            'text' => 'span',
            'strong_text' => 'strong',
            'image' => 'img',
            'divider' => 'hr',
            'button' => 'button',
            'list' => 'ul',
            'table' => 'table',
            'icon' => 'i',
            'custom_html' => 'div',
            'input_text', 'input_email', 'input_number', 'input_date' => 'input',
            'textarea' => 'textarea',
            'select' => 'select',
            default => 'div'
        };
    }

    /**
     * Render element content.
     */
    protected function renderElementContent(CertificateTemplateElement $element, string $content): string
    {
        switch ($element->element_type) {
            case 'image':
                $properties = $element->properties ?? [];
                $alt = $properties['alt_text'] ?? '';
                return ''; // Image is self-closing, handled in tag
            case 'divider':
                return ''; // HR is self-closing
            case 'table':
                return $this->renderTable($element, $content);
            case 'list':
                return $this->renderList($element, $content);
            case 'button':
                return htmlspecialchars($content);
            case 'icon':
                $properties = $element->properties ?? [];
                $iconClass = $properties['icon_class'] ?? 'mdi mdi-star';
                return '<i class="' . htmlspecialchars($iconClass) . '"></i>';
            case 'custom_html':
                return $content; // Allow raw HTML for custom HTML blocks
            case 'input_text':
            case 'input_email':
            case 'input_number':
            case 'input_date':
                return ''; // Input is self-closing
            case 'textarea':
                return htmlspecialchars($content);
            case 'select':
                return $this->renderSelect($element, $content);
            default:
                return htmlspecialchars($content);
        }
    }
    
    /**
     * Render select element.
     */
    protected function renderSelect(CertificateTemplateElement $element, string $content): string
    {
        $properties = $element->properties ?? [];
        $options = $properties['options'] ?? [];
        
        $html = '';
        foreach ($options as $option) {
            $value = $option['value'] ?? '';
            $label = $option['label'] ?? $value;
            $selected = ($value === $content) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($value) . '"' . $selected . '>' . htmlspecialchars($label) . '</option>';
        }
        
        return $html;
    }

    /**
     * Render table element.
     */
    protected function renderTable(CertificateTemplateElement $element, string $content): string
    {
        $properties = $element->properties ?? [];
        $rows = $properties['rows'] ?? 2;
        $columns = $properties['columns'] ?? 2;
        
        $html = '<table>';
        if ($properties['header'] ?? true) {
            $html .= '<thead><tr>';
            for ($i = 0; $i < $columns; $i++) {
                $html .= '<th>Header ' . ($i + 1) . '</th>';
            }
            $html .= '</tr></thead>';
        }
        $html .= '<tbody>';
        for ($r = 0; $r < $rows; $r++) {
            $html .= '<tr>';
            for ($c = 0; $c < $columns; $c++) {
                $html .= '<td>Cell ' . ($r + 1) . '-' . ($c + 1) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table>';
        
        return $html;
    }

    /**
     * Render list element.
     */
    protected function renderList(CertificateTemplateElement $element, string $content): string
    {
        $properties = $element->properties ?? [];
        $listType = $properties['list_type'] ?? 'ul';
        $items = explode("\n", $content);
        
        $html = '<' . $listType . '>';
        foreach ($items as $item) {
            if (trim($item)) {
                $html .= '<li>' . htmlspecialchars(trim($item)) . '</li>';
            }
        }
        $html .= '</' . $listType . '>';
        
        return $html;
    }

    /**
     * Build section CSS from configuration.
     */
    protected function buildSectionCss(array $cssConfig): string
    {
        $css = [];
        
        if (isset($cssConfig['margin'])) {
            $m = $cssConfig['margin'];
            $css[] = sprintf('margin: %s %s %s %s;', 
                $m['top'] ?? '0',
                $m['right'] ?? '0',
                $m['bottom'] ?? '0',
                $m['left'] ?? '0'
            );
        }
        
        if (isset($cssConfig['padding'])) {
            $p = $cssConfig['padding'];
            $css[] = sprintf('padding: %s %s %s %s;', 
                $p['top'] ?? '0',
                $p['right'] ?? '0',
                $p['bottom'] ?? '0',
                $p['left'] ?? '0'
            );
        }
        
        if (isset($cssConfig['background_color'])) {
            $css[] = 'background-color: ' . $cssConfig['background_color'] . ';';
        }
        
        if (isset($cssConfig['custom_css'])) {
            $css[] = $cssConfig['custom_css'];
        }
        
        return implode(' ', $css);
    }

    /**
     * Resolve element data (static/dynamic).
     */
    public function resolveElementData(CertificateTemplateElement $element): ?string
    {
        $modernElement = new ModernCertificateTemplateElement($element);
        
        if ($modernElement->isStaticData()) {
            return $modernElement->getStaticData();
        } elseif ($modernElement->isDynamicModelData()) {
            return $this->resolveDynamicModelData($modernElement);
        } elseif ($modernElement->isDynamicDerivedData()) {
            return $this->resolveDynamicDerivedData($modernElement);
        }
        
        return null;
    }

    /**
     * Resolve dynamic model data.
     */
    protected function resolveDynamicModelData(ModernCertificateTemplateElement $element): ?string
    {
        $config = $element->getDynamicModelConfig();
        if (!$config) {
            return null;
        }

        $modelClass = $config['model'] ?? null;
        $field = $config['field'] ?? null;
        $rowId = $config['row_id'] ?? null;

        if (!$modelClass || !$field || !class_exists($modelClass)) {
            return null;
        }

        try {
            $model = $rowId ? $modelClass::find($rowId) : $modelClass::first();
            if ($model && isset($model->$field)) {
                return (string)$model->$field;
            }
        } catch (\Exception $e) {
            \Log::error('Failed to resolve dynamic model data: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Resolve dynamic derived data (query builder).
     */
    protected function resolveDynamicDerivedData(ModernCertificateTemplateElement $element): ?string
    {
        $config = $element->getDynamicDerivedConfig();
        if (!$config) {
            return null;
        }

        try {
            $result = $this->queryBuilder->previewQuery($config);
            
            if ($result['success'] && !empty($result['data'])) {
                $firstRow = $result['data'][0];
                $firstColumn = array_values((array)$firstRow)[0] ?? null;
                return $firstColumn ? (string)$firstColumn : null;
            }
        } catch (\Exception $e) {
            \Log::error('Failed to resolve dynamic derived data: ' . $e->getMessage());
        }

        return null;
    }
}

