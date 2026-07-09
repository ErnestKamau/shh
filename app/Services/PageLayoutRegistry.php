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
                ['id' => 'before_workflow_table',  'label' => 'Before workflow board',              'selector' => '.breadcrumb-container'],
                ['id' => 'after_workflow_table',   'label' => 'After workflow board',               'selector' => 'main [wire\:id]'],
                ['id' => 'after_page_content',     'label' => 'After main content (bottom of page)', 'selector' => null],
            ],
            'buttons' => [
                ['trigger_id' => 'workflow-receive-sample', 'label' => 'Receive Sample button'],
                ['trigger_id' => 'workflow-move-to-intray', 'label' => 'Move to tray button'],
                ['trigger_id' => 'workflow-add-sample',     'label' => 'Add Sample button'],
                ['trigger_id' => 'workflow-verify',         'label' => 'Verify button'],
                ['trigger_id' => 'workflow-approve',        'label' => 'Approve button'],
                ['trigger_id' => 'workflow-action-interlab-transfer',      'label' => 'Actions: Initiate Inter Lab Transfer(s)'],
                ['trigger_id' => 'workflow-action-cancel-batch',           'label' => 'Actions: Cancel Batch'],
                ['trigger_id' => 'workflow-action-move-to-lab',            'label' => 'Actions: Move to Lab'],
                ['trigger_id' => 'workflow-action-print-labels',           'label' => 'Actions: Print Labels'],
                ['trigger_id' => 'workflow-action-request-review',         'label' => 'Actions: Request Review'],
                ['trigger_id' => 'workflow-action-generate-sales-order',   'label' => 'Actions: Generate Sales Order'],
                ['trigger_id' => 'workflow-action-approve-for-analysis',   'label' => 'Actions: Approve For Analysis'],
                ['trigger_id' => 'workflow-action-payment-reminder',       'label' => 'Actions: Payment Reminder'],
                ['trigger_id' => 'workflow-action-generate-customer-focus','label' => 'Actions: Generate Customer Focus'],
                ['trigger_id' => 'workflow-action-send-schedule-analysis', 'label' => 'Actions: Send Schedule of Analysis'],
                ['trigger_id' => 'workflow-action-clone-batches',          'label' => 'Actions: Clone Batch(es)'],
                ['trigger_id' => 'workflow-action-email-reports',          'label' => 'Actions: Email Report(s)'],
                ['trigger_id' => 'workflow-action-generate-draft-invoice', 'label' => 'Actions: Generate Draft Invoice'],
                ['trigger_id' => 'workflow-action-approve-request',        'label' => 'Actions: Approve Request'],
                ['trigger_id' => 'workflow-action-reject-request',         'label' => 'Actions: Reject Request'],
                ['trigger_id' => 'workflow-action-mark-complete',          'label' => 'Actions: Mark Complete'],
                ['trigger_id' => 'workflow-action-return-to-approval',     'label' => 'Actions: Return to Approval'],
            ],
        ],

        'sample-workflow.kpis' => [
            'label'   => 'Workflow KPIs',
            'slots'   => [
                ['id' => 'before_page_content', 'label' => 'Before main content (top of page)', 'selector' => null],
                ['id' => 'after_breadcrumb',    'label' => 'After breadcrumb navigation',       'selector' => '.breadcrumb-container'],
                ['id' => 'after_page_content',  'label' => 'After main content (bottom of page)', 'selector' => null],
            ],
            'buttons' => [],
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

        'billing.invoices.show' => [
            'label'   => 'Draft Invoice Details',
            'slots'   => [
                ['id' => 'before_page_content',  'label' => 'Before main content',     'selector' => null],
                ['id' => 'after_breadcrumb',     'label' => 'After breadcrumb navigation', 'selector' => '.breadcrumb-container'],
                ['id' => 'after_page_content',   'label' => 'After main content',      'selector' => null],
            ],
            'buttons' => [],
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
        $manifestKey = static::normalizeRouteKey($routeName);

        return static::$manifest[$manifestKey]['slots'] ?? [
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
        $manifestKey = static::normalizeRouteKey($routeName);

        return static::$manifest[$manifestKey]['buttons'] ?? [];
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
            $manifestKey = static::normalizeRouteKey($route);
            $contextLabel = static::extractRouteContextLabel($route);
            $baseLabel = static::$manifest[$manifestKey]['label'] ?? $manifestKey;

            $result[$route] = [
                'label'   => $contextLabel !== '' ? ($baseLabel . ' [' . $contextLabel . ']') : $baseLabel,
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

    private static function normalizeRouteKey(string $routeName): string
    {
        if (strpos($routeName, '@status=') !== false) {
            $routeName = explode('@status=', $routeName, 2)[0];
        }

        if ($routeName === 'sample-workflow-stage') {
            return 'sample-workflow';
        }

        return $routeName;
    }

    private static function extractRouteContextLabel(string $routeName): string
    {
        if (strpos($routeName, '@status=') === false) {
            return '';
        }

        return trim((string) explode('@status=', $routeName, 2)[1]);
    }
}
