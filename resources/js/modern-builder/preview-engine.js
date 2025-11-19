// Preview Engine for Modern Builder
class PreviewEngine {
    constructor() {
        this.templateId = window.ModernBuilder?.templateId || null;
    }

    async update() {
        if (!this.templateId) return;

        try {
            const layout = window.ModernBuilder?.collectLayout();
            const response = await fetch(`/certificate-templates/${this.templateId}/preview`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.ModernBuilder?.csrfToken || ''
                },
                body: JSON.stringify({ layout })
            });
            const data = await response.json();
            
            if (data.success) {
                this.displayPreview(data.html);
            }
        } catch (error) {
            console.error('Preview update error:', error);
        }
    }

    displayPreview(html) {
        // Update preview iframe or div
        const previewContainer = document.getElementById('preview-container');
        if (previewContainer) {
            previewContainer.innerHTML = html;
            this.applyPreviewStyles();
        }
    }

    applyPreviewStyles() {
        // Apply any additional preview-specific styles
        const previewContainer = document.getElementById('preview-container');
        if (previewContainer) {
            previewContainer.querySelectorAll('[data-dynamic]').forEach(el => {
                if (el.dataset.preview === 'true') {
                    el.classList.add('preview-placeholder');
                    el.textContent = '[Dynamic Data Placeholder]';
                }
            });
        }
    }

    openPreviewWindow() {
        // Open preview in new window
        window.ModernBuilder?.previewLayout();
    }
}

window.PreviewEngine = PreviewEngine;


