<?php

namespace Modules\TemplateEngine\Services;

class ContainerStyleService
{
    /**
     * Whitelist of allowed CSS properties for security
     */
    protected const ALLOWED_CSS_PROPERTIES = [
        'margin-top', 'margin-right', 'margin-bottom', 'margin-left',
        'padding-top', 'padding-right', 'padding-bottom', 'padding-left',
        'background-color', 'color',
        'border-width', 'border-style', 'border-color', 'border-radius',
        'font-family', 'font-size', 'font-weight',
        'width', 'height',
        'box-shadow',
        'display',
    ];

    /**
     * Dangerous CSS patterns to remove
     */
    protected const DANGEROUS_PATTERNS = [
        '/expression\s*\(/i',
        '/javascript\s*:/i',
        '/@import/i',
        '/url\s*\(\s*["\']?javascript:/i',
        '/behavior\s*:/i',
        '/binding\s*:/i',
        '/-moz-binding/i',
    ];

    /**
     * Build inline style string from CSS array
     *
     * @param array $css
     * @return string
     */
    public function buildInlineStyles(array $css): string
    {
        if (empty($css)) {
            return '';
        }

        $styles = [];

        // Map our CSS array keys to actual CSS properties
        $propertyMap = [
            'margin_top' => 'margin-top',
            'margin_right' => 'margin-right',
            'margin_bottom' => 'margin-bottom',
            'margin_left' => 'margin-left',
            'padding_top' => 'padding-top',
            'padding_right' => 'padding-right',
            'padding_bottom' => 'padding-bottom',
            'padding_left' => 'padding-left',
            'background_color' => 'background-color',
            'text_color' => 'color',
            'border_width' => 'border-width',
            'border_style' => 'border-style',
            'border_color' => 'border-color',
            'border_radius' => 'border-radius',
            'font_family' => 'font-family',
            'font_size' => 'font-size',
            'font_weight' => 'font-weight',
            'width' => 'width',
            'height' => 'height',
            'box_shadow' => 'box-shadow',
            'display' => 'display',
        ];

        foreach ($propertyMap as $key => $property) {
            if (isset($css[$key]) && $css[$key] !== '' && $css[$key] !== null) {
                $value = $this->sanitizeCssProperty($property, $css[$key]);
                if ($value !== '') {
                    $styles[] = "{$property}: {$value}";
                }
            }
        }

        // Handle custom CSS (with extra sanitization)
        if (isset($css['custom_css']) && !empty($css['custom_css'])) {
            $customStyles = $this->sanitizeCustomCss($css['custom_css']);
            if ($customStyles !== '') {
                $styles[] = $customStyles;
            }
        }

        return implode('; ', $styles);
    }

    /**
     * Sanitize a CSS property value
     *
     * @param string $property
     * @param string $value
     * @return string
     */
    public function sanitizeCssProperty(string $property, string $value): string
    {
        // Remove dangerous patterns
        $value = preg_replace(self::DANGEROUS_PATTERNS, '', $value);

        // Validate based on property type
        switch ($property) {
            case 'color':
            case 'background-color':
            case 'border-color':
                if (!$this->validateColor($value)) {
                    return '';
                }
                break;

            case 'margin-top':
            case 'margin-right':
            case 'margin-bottom':
            case 'margin-left':
            case 'padding-top':
            case 'padding-right':
            case 'padding-bottom':
            case 'padding-left':
            case 'border-width':
            case 'border-radius':
            case 'font-size':
            case 'width':
            case 'height':
                if (!$this->validateSize($value)) {
                    return '';
                }
                break;

            case 'border-style':
                $allowed = ['solid', 'dashed', 'dotted', 'double', 'groove', 'ridge', 'inset', 'outset', 'none', 'hidden'];
                if (!in_array(strtolower($value), $allowed)) {
                    return '';
                }
                break;

            case 'font-weight':
                $allowed = ['normal', 'bold', 'bolder', 'lighter', '100', '200', '300', '400', '500', '600', '700', '800', '900'];
                if (!in_array(strtolower($value), $allowed)) {
                    return '';
                }
                break;

            case 'display':
                $allowed = ['block', 'inline', 'inline-block', 'flex', 'grid', 'table', 'table-row', 'table-cell', 'none'];
                if (!in_array(strtolower($value), $allowed)) {
                    return '';
                }
                break;
        }

        // Escape any remaining dangerous characters
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        return trim($value);
    }

    /**
     * Sanitize custom CSS input
     *
     * @param string $customCss
     * @return string
     */
    public function sanitizeCustomCss(string $customCss): string
    {
        // Remove dangerous patterns
        $customCss = preg_replace(self::DANGEROUS_PATTERNS, '', $customCss);

        // Only allow whitelisted properties in custom CSS
        $lines = explode(';', $customCss);
        $safeLines = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Extract property name
            if (preg_match('/^([a-z-]+)\s*:/i', $line, $matches)) {
                $property = strtolower(trim($matches[1]));

                // Check if property is in whitelist or is a safe vendor prefix
                if (in_array($property, self::ALLOWED_CSS_PROPERTIES) ||
                    preg_match('/^-[a-z]+-/', $property)) {
                    // Extract value
                    $value = trim(substr($line, strlen($matches[0])));
                    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    $safeLines[] = "{$property}: {$value}";
                }
            }
        }

        return implode('; ', $safeLines);
    }

    /**
     * Generate Bootstrap column class
     *
     * @param int $columns
     * @return string
     */
    public function getColumnClass(int $columns): string
    {
        // Ensure columns is between 1 and 12
        $columns = max(1, min(12, $columns));

        // Calculate Bootstrap column width
        $colWidth = (int)(12 / $columns);

        return "col-md-{$colWidth}";
    }

    /**
     * Validate color format
     *
     * @param string $color
     * @return bool
     */
    public function validateColor(string $color): bool
    {
        $color = trim($color);

        // Hex color (#fff, #ffffff)
        if (preg_match('/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', $color)) {
            return true;
        }

        // RGB/RGBA
        if (preg_match('/^rgba?\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*(,\s*[\d.]+\s*)?\)$/i', $color)) {
            return true;
        }

        // Named colors (basic set)
        $namedColors = [
            'black', 'white', 'red', 'green', 'blue', 'yellow', 'cyan', 'magenta',
            'silver', 'gray', 'maroon', 'olive', 'lime', 'aqua', 'teal', 'navy',
            'fuchsia', 'purple', 'transparent', 'inherit', 'initial', 'unset'
        ];
        if (in_array(strtolower($color), $namedColors)) {
            return true;
        }

        return false;
    }

    /**
     * Validate size value
     *
     * @param string $size
     * @return bool
     */
    public function validateSize(string $size): bool
    {
        $size = trim($size);

        // Empty is valid (means no size)
        if ($size === '') {
            return true;
        }

        // Percentage
        if (preg_match('/^\d+(\.\d+)?%$/', $size)) {
            return true;
        }

        // Pixels
        if (preg_match('/^\d+(\.\d+)?px$/', $size)) {
            return true;
        }

        // Em
        if (preg_match('/^\d+(\.\d+)?em$/', $size)) {
            return true;
        }

        // Rem
        if (preg_match('/^\d+(\.\d+)?rem$/', $size)) {
            return true;
        }

        // Viewport units
        if (preg_match('/^\d+(\.\d+)?(vw|vh|vmin|vmax)$/', $size)) {
            return true;
        }

        // Keywords
        $keywords = ['auto', 'inherit', 'initial', 'unset', 'none', '0'];
        if (in_array(strtolower($size), $keywords)) {
            return true;
        }

        return false;
    }
}

