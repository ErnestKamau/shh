<?php

namespace App\Services;

/**
 * PageLayoutRegistry
 *
 * Maps known route names to their page layout metadata:
 *  - slots:   named anchor positions where a page_section form can be injected.
 *             Each slot carries an optional `selector` — a CSS selector used by the
 *             layout JS to auto-locate the injection point without any per-page HTML
 *             changes. If a `data-sf-slot` attribute is present in the page DOM it
 *             takes priority; the `selector` is the automatic fallback.
 *  - buttons: named trigger points (elements with a matching data-sf-trigger attribute)
 *             that can launch a button_trigger form.
 *
 * The breadcrumb component always emits `data-sf-slot="after_breadcrumb"`, so that
 * slot works on every page without any developer intervention.
 */
class PageLayoutRegistry
{
    protected static array $manifest = [

        // ─── Lab Dashboard ───────────────────────────────────────────────────────────
        'dashboard-lab' => [
            'label'   => 'Lab Dashboard',
            'slots'   => [
                // PHP-rendered — no selector needed
                ['id' => 'before_page_content', 'label' => 'Before main content (top of page)', 'selector' => null],
                // Emitted by <x-bread-crumb> on every page; selector is the universal fallback
                ['id' => 'after_breadcrumb',    'label' => 'After breadcrumb navigation',       'selector' => '.breadcrumb-container'],
                // Dashboard-specific: after the pipeline statistics row
                ['id' => 'after_stats_row',     'label' => 'After statistics cards',            'selector' => 'main .row.mb-4:first-of-type'],
                // PHP-rendered — no selector needed
                ['id' => 'after_page_content',  'label' => 'After main content (bottom of page)', 'selector' => null],
            ],
            'buttons' => [
                ['trigger_id' => 'dashboard-new-action', 'label' => 'New Action button (top-right toolbar)'],
            ],
        ],

        // ─── Sample Workflow ─────────────────────────────────────────────────────────
        'sample-workflow' => [
            'label'   => 'Sample Workflow',
            'slots'   => [
                ['id' => 'before_page_content',    'label' => 'Before main content (top of page)',  'selector' => null],
                ['id' => 'after_breadcrumb',       'label' => 'After breadcrumb navigation',        'selector' => '.breadcrumb-container'],
                // Before the Livewire workflow board — insert after the breadcrumb (same position)
                ['id' => 'before_workflow_table',  'label' => 'Before workflow board',              'selector' => '.breadcrumb-container'],
                // After the Livewire workflow board root element
                ['id' => 'after_workflow_table',   'label' => 'After workflow board',               'selector' => 'main [wire\:id]'],
                ['id' => 'after_page_content',     'label' => 'After main content (bottom of page)', 'selector' => null],
            ],
            'buttons' => [
                ['trigger_id' => 'workflow-receive-sample', 'label' => 'Receive Sample button'],
                ['trigger_id' => 'workflow-add-sample',     'label' => 'Add Sample button'],
                ['trigger_id' => 'workflow-verify',         'label' => 'Verify button'],
                ['trigger_id' => 'workflow-approve',        'label' => 'Approve button'],
            ],
        ],

        // ─── Billing – Invoices ──────────────────────────────────────────────────────
        'billing.invoices' => [
            'label'   => 'Sales Orders',
            'slots'   => [
                ['id' => 'before_page_content',  'label' => 'Before main content',     'selector' => null],
                ['id' => 'after_breadcrumb',     'label' => 'After breadcrumb navigation', 'selector' => '.breadcrumb-container'],
                // Before the first table/card in the page body
                ['id' => 'before_invoice_table', 'label' => 'Before orders table',     'selector' => 'main .table-responsive:first-of-type, main .card:first-of-type'],
                ['id' => 'after_page_content',   'label' => 'After main content',      'selector' => null],
            ],
            'buttons' => [
                ['trigger_id' => 'invoice-create-btn', 'label' => 'Create New Order button'],
            ],
        ],

        // ─── Quotations ──────────────────────────────────────────────────────────────
        'quotation-index' => [
            'label'   => 'Quotations',
            'slots'   => [
                ['id' => 'before_page_content', 'label' => 'Before main content',      'selector' => null],
                ['id' => 'after_breadcrumb',    'label' => 'After breadcrumb navigation', 'selector' => '.breadcrumb-container'],
                ['id' => 'before_quotes_table', 'label' => 'Before quotes table',      'selector' => 'main .table-responsive:first-of-type, main .card:first-of-type'],
                ['id' => 'after_page_content',  'label' => 'After main content',       'selector' => null],
            ],
            'buttons' => [
                ['trigger_id' => 'quotation-create-btn', 'label' => 'Create Quotation button'],
            ],
        ],

        // ─── QC Workflow ─────────────────────────────────────────────────────────────
        'qcWorkflowIndex' => [
            'label'   => 'QC Workflow',
            'slots'   => [
                ['id' => 'before_page_content', 'label' => 'Before main content',    'selector' => null],
                ['id' => 'after_breadcrumb',    'label' => 'After breadcrumb navigation', 'selector' => '.breadcrumb-container'],
                ['id' => 'before_qc_table',     'label' => 'Before QC table',        'selector' => 'main .table-responsive:first-of-type, main .card:first-of-type'],
                ['id' => 'after_page_content',  'label' => 'After main content',     'selector' => null],
            ],
            'buttons' => [
                ['trigger_id' => 'qc-record-btn', 'label' => 'Record QC button'],
            ],
        ],

        // ─── Submission Forms List ────────────────────────────────────────────────────
        'submission-forms.index' => [
            'label'   => 'Submission Forms List',
            'slots'   => [
                ['id' => 'before_page_content', 'label' => 'Before main content',    'selector' => null],
                ['id' => 'after_breadcrumb',    'label' => 'After breadcrumb navigation', 'selector' => '.breadcrumb-container'],
                ['id' => 'after_page_content',  'label' => 'After main content',     'selector' => null],
            ],
            'buttons' => [],
        ],
    ];

    // ─── Public API ────────────────────────────────────────────────────────────────

    /**
     * Return the slot list for a given route, or a generic default set.
     *
     * @return array<int, array{id: string, label: string}>
     */
    public static function getSlotsForRoute(string $routeName): array
    {
        return static::$manifest[$routeName]['slots'] ?? [
            ['id' => 'before_page_content', 'label' => 'Before main content (top of page)'],
            ['id' => 'after_page_content',  'label' => 'After main content (bottom of page)'],
        ];
    }

    /**
     * Return the trigger-button list for a given route.
     *
     * @return array<int, array{trigger_id: string, label: string}>
     */
    public static function getButtonsForRoute(string $routeName): array
    {
        return static::$manifest[$routeName]['buttons'] ?? [];
    }

    /**
     * Return the CSS selector for a specific slot, or null for PHP-rendered slots.
     * Used by the layout JS to auto-locate injection points without per-page HTML changes.
     */
    public static function getSelectorForSlot(string $routeName, string $slotId): ?string
    {
        foreach (static::getSlotsForRoute($routeName) as $slot) {
            if ($slot['id'] === $slotId) {
                return $slot['selector'] ?? null;
            }
        }
        return null;
    }

    /**
     * Return combined layout metadata for an array of route names.
     *
     * @param string[] $routeNames
     * @return array<string, array{label: string, slots: array, buttons: array}>
     */
    public static function getLayoutForRoutes(array $routeNames): array
    {
        $result = [];
        foreach ($routeNames as $route) {
            $result[$route] = [
                'label'   => static::$manifest[$route]['label'] ?? $route,
                'slots'   => static::getSlotsForRoute($route),
                'buttons' => static::getButtonsForRoute($route),
            ];
        }
        return $result;
    }

    /**
     * Return the full manifest (used in admin UIs).
     */
    public static function getManifest(): array
    {
        return static::$manifest;
    }
}
