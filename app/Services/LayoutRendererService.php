<?php

namespace App\Services;

use App\CertificateTemplate;
use App\CertificateTemplateSection;
use App\CertificateTemplateElement;
use App\Models\ModernCertificateTemplateSection;
use App\Models\ModernCertificateTemplateElement;
use App\Services\Renderers\HtmlRenderer;
use App\Services\Renderers\PdfRenderer;
use App\Services\Renderers\PreviewRenderer;

class LayoutRendererService
{
    protected HtmlRenderer $htmlRenderer;
    protected PdfRenderer $pdfRenderer;
    protected PreviewRenderer $previewRenderer;

    public function __construct(
        HtmlRenderer $htmlRenderer,
        PdfRenderer $pdfRenderer,
        PreviewRenderer $previewRenderer
    ) {
        $this->htmlRenderer = $htmlRenderer;
        $this->pdfRenderer = $pdfRenderer;
        $this->previewRenderer = $previewRenderer;
    }

    /**
     * Render section with rows/columns/cells.
     */
    public function renderSection(CertificateTemplateSection $section, array $context = []): string
    {
        $modernSection = new ModernCertificateTemplateSection($section);
        
        // Check if section has layout (rows/columns)
        if (!$modernSection->hasLayout()) {
            return ''; // Don't render sections without layout
        }

        return $this->htmlRenderer->renderSection($section, $context);
    }

    /**
     * Render element with CSS and data.
     */
    public function renderElement(CertificateTemplateElement $element, array $context = []): string
    {
        return $this->htmlRenderer->renderElement($element, $context);
    }

    /**
     * Resolve static/dynamic data for elements.
     */
    public function resolveData(CertificateTemplateElement $element, array $context = []): ?string
    {
        // Delegate to HtmlRenderer which has the resolve logic
        return $this->htmlRenderer->resolveElementData($element);
    }

    /**
     * Apply CSS configurations.
     */
    public function applyCss(CertificateTemplateElement $element): string
    {
        $modernElement = new ModernCertificateTemplateElement($element);
        return $modernElement->generateInlineCss();
    }

    /**
     * Generate final HTML output.
     */
    public function generateHtml(CertificateTemplate $template, ?array $layout = null): string
    {
        return $this->htmlRenderer->generateHtml($template, $layout);
    }

    /**
     * Generate PDF from HTML.
     */
    public function generatePdf(string $html): string
    {
        return $this->pdfRenderer->generatePdf($html);
    }

    /**
     * Generate preview HTML.
     */
    public function generatePreview(CertificateTemplate $template, ?array $layout = null): string
    {
        return $this->previewRenderer->generatePreview($template, $layout);
    }
}

