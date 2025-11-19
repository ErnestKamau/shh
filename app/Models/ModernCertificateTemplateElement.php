<?php

namespace App\Models;

use App\CertificateTemplateElement;

class ModernCertificateTemplateElement
{
    protected CertificateTemplateElement $element;

    public function __construct(CertificateTemplateElement $element)
    {
        $this->element = $element;
    }

    /**
     * Get the underlying element model.
     */
    public function getElement(): CertificateTemplateElement
    {
        return $this->element;
    }

    /**
     * Get CSS configuration.
     */
    public function getCssConfig(): array
    {
        return $this->element->css_config ?? [];
    }

    /**
     * Set CSS configuration.
     */
    public function setCssConfig(array $config): void
    {
        $this->element->css_config = $config;
        $this->element->save();
    }

    /**
     * Get specific CSS property.
     */
    public function getCssProperty(string $key, $default = null)
    {
        $config = $this->getCssConfig();
        return $config[$key] ?? $default;
    }

    /**
     * Set specific CSS property.
     */
    public function setCssProperty(string $key, $value): void
    {
        $config = $this->getCssConfig();
        $config[$key] = $value;
        $this->setCssConfig($config);
    }

    /**
     * Get margin configuration.
     */
    public function getMargin(): array
    {
        return $this->getCssProperty('margin', [
            'top' => '0',
            'right' => '0',
            'bottom' => '0',
            'left' => '0'
        ]);
    }

    /**
     * Get padding configuration.
     */
    public function getPadding(): array
    {
        return $this->getCssProperty('padding', [
            'top' => '0',
            'right' => '0',
            'bottom' => '0',
            'left' => '0'
        ]);
    }

    /**
     * Get width/height configuration.
     */
    public function getDimensions(): array
    {
        return $this->getCssProperty('dimensions', [
            'width' => 'auto',
            'height' => 'auto'
        ]);
    }

    /**
     * Get alignment.
     */
    public function getAlignment(): string
    {
        return $this->getCssProperty('alignment', 'left');
    }

    /**
     * Get border configuration.
     */
    public function getBorder(): array
    {
        return $this->getCssProperty('border', [
            'width' => '0',
            'style' => 'solid',
            'color' => '#000000'
        ]);
    }

    /**
     * Get background color.
     */
    public function getBackgroundColor(): ?string
    {
        return $this->getCssProperty('background_color');
    }

    /**
     * Get text color.
     */
    public function getTextColor(): ?string
    {
        return $this->getCssProperty('text_color');
    }

    /**
     * Get font settings.
     */
    public function getFontSettings(): array
    {
        return $this->getCssProperty('font', [
            'family' => 'inherit',
            'size' => '14px',
            'weight' => 'normal',
            'style' => 'normal',
            'line_height' => '1.5'
        ]);
    }

    /**
     * Get custom CSS class.
     */
    public function getCustomClass(): ?string
    {
        return $this->getCssProperty('custom_class');
    }

    /**
     * Get custom CSS rules.
     */
    public function getCustomCss(): ?string
    {
        return $this->getCssProperty('custom_css');
    }

    /**
     * Generate inline CSS from configuration.
     */
    public function generateInlineCss(): string
    {
        $css = [];
        
        // Margin
        $margin = $this->getMargin();
        if (!empty($margin)) {
            $css[] = sprintf('margin: %s %s %s %s;', 
                $margin['top'] ?? '0',
                $margin['right'] ?? '0',
                $margin['bottom'] ?? '0',
                $margin['left'] ?? '0'
            );
        }
        
        // Padding
        $padding = $this->getPadding();
        if (!empty($padding)) {
            $css[] = sprintf('padding: %s %s %s %s;', 
                $padding['top'] ?? '0',
                $padding['right'] ?? '0',
                $padding['bottom'] ?? '0',
                $padding['left'] ?? '0'
            );
        }
        
        // Dimensions
        $dimensions = $this->getDimensions();
        if (!empty($dimensions['width'])) {
            $css[] = sprintf('width: %s;', $dimensions['width']);
        }
        if (!empty($dimensions['height'])) {
            $css[] = sprintf('height: %s;', $dimensions['height']);
        }
        
        // Alignment
        $alignment = $this->getAlignment();
        if ($alignment) {
            $css[] = sprintf('text-align: %s;', $alignment);
        }
        
        // Border
        $border = $this->getBorder();
        if (!empty($border['width']) && $border['width'] !== '0') {
            $css[] = sprintf('border: %s %s %s;', 
                $border['width'],
                $border['style'] ?? 'solid',
                $border['color'] ?? '#000000'
            );
        }
        
        // Background color
        $bgColor = $this->getBackgroundColor();
        if ($bgColor) {
            $css[] = sprintf('background-color: %s;', $bgColor);
        }
        
        // Text color
        $textColor = $this->getTextColor();
        if ($textColor) {
            $css[] = sprintf('color: %s;', $textColor);
        }
        
        // Font settings
        $font = $this->getFontSettings();
        if (!empty($font['family'])) {
            $css[] = sprintf('font-family: %s;', $font['family']);
        }
        if (!empty($font['size'])) {
            $css[] = sprintf('font-size: %s;', $font['size']);
        }
        if (!empty($font['weight'])) {
            $css[] = sprintf('font-weight: %s;', $font['weight']);
        }
        if (!empty($font['style'])) {
            $css[] = sprintf('font-style: %s;', $font['style']);
        }
        if (!empty($font['line_height'])) {
            $css[] = sprintf('line-height: %s;', $font['line_height']);
        }
        
        return implode(' ', $css);
    }

    /**
     * Get data configuration.
     */
    public function getDataConfig(): array
    {
        return $this->element->data_config ?? [];
    }

    /**
     * Set data configuration.
     */
    public function setDataConfig(array $config): void
    {
        $this->element->data_config = $config;
        $this->element->save();
    }

    /**
     * Get data type (static, dynamic_model, dynamic_derived).
     */
    public function getDataType(): string
    {
        return $this->getDataConfig()['type'] ?? 'static';
    }

    /**
     * Check if data is static.
     */
    public function isStaticData(): bool
    {
        return $this->getDataType() === 'static';
    }

    /**
     * Check if data is dynamic model.
     */
    public function isDynamicModelData(): bool
    {
        return $this->getDataType() === 'dynamic_model';
    }

    /**
     * Check if data is dynamic derived.
     */
    public function isDynamicDerivedData(): bool
    {
        return $this->getDataType() === 'dynamic_derived';
    }

    /**
     * Get static data value.
     */
    public function getStaticData(): ?string
    {
        if ($this->isStaticData()) {
            return $this->getDataConfig()['value'] ?? null;
        }
        return null;
    }

    /**
     * Get dynamic model configuration.
     */
    public function getDynamicModelConfig(): ?array
    {
        if ($this->isDynamicModelData()) {
            return $this->getDataConfig()['model_config'] ?? null;
        }
        return null;
    }

    /**
     * Get dynamic derived configuration (query builder).
     */
    public function getDynamicDerivedConfig(): ?array
    {
        if ($this->isDynamicDerivedData()) {
            return $this->getDataConfig()['query_config'] ?? null;
        }
        return null;
    }

    /**
     * Get parent cell ID.
     */
    public function getParentCellId(): ?string
    {
        return $this->element->parent_cell_id;
    }

    /**
     * Set parent cell ID.
     */
    public function setParentCellId(?string $cellId): void
    {
        $this->element->parent_cell_id = $cellId;
        $this->element->save();
    }
}




