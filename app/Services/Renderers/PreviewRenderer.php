<?php

namespace App\Services\Renderers;

use App\CertificateTemplate;
use App\Services\Renderers\HtmlRenderer;

class PreviewRenderer
{
    protected HtmlRenderer $htmlRenderer;

    public function __construct()
    {
        $this->htmlRenderer = app(HtmlRenderer::class);
    }

    /**
     * Generate preview HTML with placeholder data for dynamic fields.
     */
    public function generatePreview(CertificateTemplate $template, ?array $layout = null): string
    {
        $html = $this->htmlRenderer->generateHtml($template, $layout);
        
        // Replace dynamic data placeholders with preview placeholders
        $html = preg_replace_callback(
            '/data-dynamic="([^"]+)"/',
            function ($matches) {
                return 'data-dynamic="' . $matches[1] . '" data-preview="true"';
            },
            $html
        );
        
        // Add preview wrapper
        $previewHtml = '<div class="template-preview" data-template-id="' . $template->id . '">';
        $previewHtml .= $html;
        $previewHtml .= '</div>';
        
        return $previewHtml;
    }
}

