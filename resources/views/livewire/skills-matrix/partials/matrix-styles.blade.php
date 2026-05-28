<style>
    #main-container-body .metric-grid,
    #main-container-body .sm-metric-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 20px;
    }
    #main-container-body .metric-card,
    #main-container-body .sm-metric-card {
        background: var(--color-background-primary, #fff);
        border: 0.5px solid var(--color-border-tertiary, #e5e7eb);
        border-radius: 8px;
        padding: 16px 18px;
    }
    #main-container-body .metric-label,
    #main-container-body .sm-metric-label {
        font-size: 11px;
        color: var(--color-text-secondary, #6b7280);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    #main-container-body .metric-value,
    #main-container-body .sm-metric-value {
        font-size: 26px;
        font-weight: 500;
        color: var(--color-text-primary, #111);
    }
    #main-container-body .metric-delta {
        font-size: 11px;
        margin-top: 4px;
    }
    #main-container-body .delta-up { color: #16A34A; }
    #main-container-body .delta-down { color: #DC2626; }
    #main-container-body .metric-bar-bg {
        height: 4px;
        background: var(--color-border-tertiary, #e5e7eb);
        border-radius: 2px;
        margin-top: 10px;
    }
    #main-container-body .metric-bar-fill {
        height: 4px;
        border-radius: 2px;
    }
    #main-container-body .two-col,
    #main-container-body .sm-two-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    #main-container-body .section-gap { margin-bottom: 20px; }
    #main-container-body .card,
    #main-container-body .sm-card {
        background: var(--color-background-primary, #fff);
        border: 0.5px solid var(--color-border-tertiary, #e5e7eb);
        border-radius: 8px;
        padding: 18px;
        margin-bottom: 16px;
    }
    #main-container-body .card-header,
    #main-container-body .sm-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    #main-container-body .card-title,
    #main-container-body .sm-card-title {
        font-size: 13px;
        font-weight: 500;
        color: var(--color-text-primary, #111);
    }
    #main-container-body .card-subtitle {
        font-size: 11px;
        color: var(--color-text-secondary, #6b7280);
        margin-top: 2px;
    }
    #main-container-body .chart-wrap { position: relative; height: 200px; }
    #main-container-body .chart-wrap-sm { position: relative; height: 180px; }
    #main-container-body .legend {
        display: flex;
        gap: 16px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }
    #main-container-body .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 11px;
        color: var(--color-text-secondary, #6b7280);
    }
    #main-container-body .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }
    #main-container-body .skill-heatmap {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }
    #main-container-body .skill-heatmap th {
        padding: 6px 8px;
        text-align: left;
        font-weight: 500;
        font-size: 10px;
        color: var(--color-text-secondary, #6b7280);
        border-bottom: 0.5px solid var(--color-border-tertiary, #e5e7eb);
        white-space: nowrap;
    }
    #main-container-body .skill-heatmap th.text-center { text-align: center; }
    #main-container-body .skill-heatmap td {
        padding: 5px 8px;
        border-bottom: 0.5px solid var(--color-border-tertiary, #e5e7eb);
        vertical-align: middle;
        color: var(--color-text-primary, #111);
    }
    #main-container-body .skill-heatmap td.text-center { text-align: center; }
    #main-container-body .skill-heatmap tr.sm-competency-area-row td {
        background: #f1f5f9;
        border-top: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        padding-top: 10px;
        padding-bottom: 10px;
    }
    #main-container-body .skill-heatmap tr.sm-competency-area-row--alt td,
    #main-container-body .skill-heatmap tr.sm-competency-area-row--alt .area-label {
        background: #eef2ff;
    }
    #main-container-body .skill-heatmap tr.sm-competency-area-row:first-child td {
        border-top: none;
    }
    #main-container-body .skill-heatmap tr.sm-competency-area-row + tr.sm-competency-data-row td {
        border-top-color: transparent;
    }
    #main-container-body .skill-heatmap tr.sm-competency-data-row td {
        background: #fff;
    }
    #main-container-body .skill-heatmap tr.sm-competency-area-row--alt + tr.sm-competency-data-row td {
        background: #fafbff;
    }
    #main-container-body .skill-heatmap .area-label {
        font-size: 12px;
        font-weight: 600;
        color: #475569;
        letter-spacing: 0.02em;
        background: inherit;
    }
    #main-container-body .gap-dot {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        padding: 0 4px;
        border-radius: 50%;
        font-size: 10px;
        font-weight: 500;
    }
    #main-container-body .gap-ok { background: #DCFCE7; color: #14532D; }
    #main-container-body .gap-train { background: #FEE2E2; color: #991B1B; }
    #main-container-body .gap-exceed { background: #DBEAFE; color: #1E40AF; }
    #main-container-body .sm-gap-legend {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        column-gap: 20px;
        row-gap: 8px;
    }
    #main-container-body .sm-gap-legend-item {
        display: inline-flex;
        align-items: center;
        column-gap: 8px;
        font-size: 11px;
        color: #6b7280;
    }
    #main-container-body .level-dot {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        padding: 0 4px;
        border-radius: 50%;
        font-size: 10px;
        font-weight: 500;
    }
    #main-container-body .level-1 { background: #FEE2E2; color: #991B1B; }
    #main-container-body .level-2 { background: #FEF3C7; color: #92400E; }
    #main-container-body .level-3 { background: #DCFCE7; color: #14532D; }
    #main-container-body .gap-neutral { background: #f3f4f6; color: #6b7280; }
    #main-container-body .sm-page-header-card {
        background: #fff;
    }
    #main-container-body .sm-page-header-actions label {
        font-size: 12px;
        color: #6c757d;
        margin: 0;
        white-space: nowrap;
        font-weight: 600;
    }
    #main-container-body .sm-dashboard-toolbar {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        margin-bottom: 16px;
        gap: 12px;
    }
    #main-container-body .sm-dashboard-toolbar label {
        font-size: 12px;
        color: var(--color-text-secondary, #6b7280);
        margin: 0;
    }
    #main-container-body .sm-dashboard-preview-hint {
        font-size: 12px;
        color: var(--color-text-secondary, #6b7280);
        background: var(--color-background-secondary, #f9fafb);
    }
    #main-container-body .sm-list-filters-card,
    #main-container-body .sm-list-table-card {
        background: #fff;
    }
    #main-container-body .sm-list-filters-row {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        width: 100%;
        row-gap: 16px;
    }
    #main-container-body .sm-list-filters-fields {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        flex: 1 1 auto;
        column-gap: 24px;
        row-gap: 16px;
        margin-right: 24px;
    }
    #main-container-body .sm-list-filter-field {
        flex: 0 1 auto;
        min-width: 140px;
        margin: 0;
    }
    #main-container-body .sm-list-filter-search {
        flex: 1 1 240px;
        min-width: 220px;
        max-width: 440px;
    }
    #main-container-body .sm-list-filter-field:not(.sm-list-filter-search):not(.sm-list-filter-per-page) {
        min-width: 160px;
    }
    #main-container-body .sm-list-filter-per-page {
        width: 120px;
        min-width: 120px;
        flex: 0 0 auto;
    }
    #main-container-body .sm-list-filter-per-page .form-select {
        min-width: 120px;
    }
    #main-container-body .sm-list-filter-actions {
        flex: 0 0 auto;
        margin-left: auto;
        padding-left: 8px;
    }
    #main-container-body .sm-list-filters-card .form-label {
        margin-bottom: 6px;
    }
    #main-container-body .sm-list-filters-card .tag-select-container label {
        margin-right: 20px;
        margin-bottom: 8px;
    }
    #main-container-body .sm-modern-table thead tr {
        background-color: rgba(0, 0, 0, 0.03);
    }
    #main-container-body .sm-modern-table thead th {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
        white-space: nowrap;
        padding: 12px 14px;
    }
    #main-container-body .sm-modern-table tbody td {
        padding: 12px 14px;
        vertical-align: middle;
        font-size: 13px;
        border-bottom: 1px solid #f3f4f6;
    }
    #main-container-body .sm-modern-table tbody tr:hover {
        background-color: #f9fafb;
    }
    #main-container-body .sm-table-actions {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
    }
    #main-container-body .sm-table-actions > *:not(:last-child) {
        margin-right: 8px;
    }
    #main-container-body .sm-modern-table .sm-table-actions .rm-act-btn {
        margin-right: 0;
    }
    #main-container-body .sm-modern-table .sm-table-actions .rm-act-btn + .rm-act-btn {
        margin-left: 8px;
    }
    #main-container-body .sm-list-pagination .pagination {
        margin-bottom: 0;
    }
    #main-container-body .training-list {
        display: flex;
        flex-direction: column;
        row-gap: 8px;
    }
    #main-container-body .training-item {
        display: flex;
        align-items: center;
        column-gap: 12px;
        padding: 10px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #fff;
    }
    #main-container-body .training-week {
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        width: 52px;
        flex-shrink: 0;
        text-align: center;
    }
    #main-container-body .training-item-body {
        flex: 1 1 auto;
        min-width: 0;
    }
    #main-container-body .training-name {
        font-size: 13px;
        font-weight: 500;
        color: #111827;
    }
    #main-container-body .training-trainer {
        font-size: 11px;
        color: #6b7280;
        margin-top: 2px;
    }
    #main-container-body .training-meta {
        font-size: 11px;
        color: #9ca3af;
        margin-top: 2px;
    }
    #main-container-body .sm-training-status {
        font-size: 10px;
        padding: 3px 8px;
        border-radius: 20px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    #main-container-body .sm-training-status--planned {
        background: #FEF3C7;
        color: #92400E;
    }
    #main-container-body .sm-training-status--done {
        background: #DCFCE7;
        color: #14532D;
    }
    #main-container-body .sm-training-status--cancelled {
        background: #f3f4f6;
        color: #6b7280;
    }
    #main-container-body .sm-tp-hint {
        border-radius: 12px;
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
    }
    #main-container-body .sm-training-plan-layout {
        display: grid;
        grid-template-columns: minmax(280px, 340px) 1fr;
        gap: 20px;
        align-items: start;
    }
    @media (max-width: 991px) {
        #main-container-body .sm-training-plan-layout {
            grid-template-columns: 1fr;
        }
    }
    #main-container-body .sm-tp-sidebar,
    #main-container-body .sm-tp-workspace {
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }
    #main-container-body .sm-tp-sidebar-head {
        padding: 16px 18px;
        border-bottom: 1px solid #eef2f7;
        background: #fafbfc;
    }
    #main-container-body .sm-tp-sidebar-body {
        padding: 12px;
        max-height: calc(100vh - 280px);
        overflow-y: auto;
    }
    #main-container-body .sm-tp-section-label {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #94a3b8;
        padding: 4px 8px 8px;
    }
    #main-container-body .sm-tp-session-card {
        display: flex;
        align-items: flex-start;
        width: 100%;
        text-align: left;
        column-gap: 10px;
        padding: 12px;
        margin-bottom: 8px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        cursor: pointer;
        transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
    }
    #main-container-body .sm-tp-session-card:hover {
        border-color: #93c5fd;
        box-shadow: 0 2px 8px rgba(29, 78, 216, 0.08);
    }
    #main-container-body .sm-tp-session-card.is-active {
        border-color: #3b82f6;
        background: #eff6ff;
        box-shadow: 0 0 0 1px #3b82f6;
    }
    #main-container-body .sm-tp-session-card-week {
        flex-shrink: 0;
        width: 44px;
        font-size: 10px;
        font-weight: 700;
        color: #64748b;
        text-align: center;
        padding-top: 2px;
    }
    #main-container-body .sm-tp-session-card-body {
        flex: 1;
        min-width: 0;
    }
    #main-container-body .sm-tp-session-card-title {
        font-size: 13px;
        font-weight: 600;
        color: #0f172a;
        line-height: 1.35;
        margin-bottom: 2px;
    }
    #main-container-body .sm-tp-session-card-meta,
    #main-container-body .sm-tp-session-card-trainer {
        font-size: 11px;
        color: #64748b;
        line-height: 1.4;
    }
    #main-container-body .sm-tp-session-card .sm-training-status {
        flex-shrink: 0;
        margin-top: 2px;
    }
    #main-container-body .sm-tp-empty-note {
        font-size: 12px;
        color: #94a3b8;
        padding: 8px 10px 16px;
        margin: 0;
    }
    #main-container-body .sm-tp-workspace {
        min-height: 420px;
        display: flex;
        flex-direction: column;
    }
    #main-container-body .sm-tp-workspace-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 12px;
        padding: 20px 22px;
        border-bottom: 1px solid #eef2f7;
        background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
    }
    #main-container-body .sm-tp-workspace-week {
        display: inline-block;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #1d4ed8;
        background: #dbeafe;
        padding: 3px 8px;
        border-radius: 6px;
        margin-bottom: 8px;
    }
    #main-container-body .sm-tp-workspace-title {
        font-size: 18px;
        font-weight: 700;
        color: #0f172a;
    }
    #main-container-body .sm-tp-workspace-sub {
        font-size: 12px;
    }
    #main-container-body .sm-tp-session-tabs {
        display: flex;
        flex-wrap: wrap;
        column-gap: 4px;
        padding: 0 16px;
        border-bottom: 1px solid #e5e7eb;
        background: #fafbfc;
    }
    #main-container-body .sm-tp-session-tab {
        display: inline-flex;
        align-items: center;
        column-gap: 6px;
        padding: 12px 16px;
        margin-bottom: -1px;
        border: none;
        border-bottom: 2px solid transparent;
        background: transparent;
        font-size: 13px;
        font-weight: 500;
        color: #64748b;
        cursor: pointer;
        transition: color 0.15s, border-color 0.15s;
    }
    #main-container-body .sm-tp-session-tab:hover {
        color: #1d4ed8;
    }
    #main-container-body .sm-tp-session-tab.is-active {
        color: #1d4ed8;
        border-bottom-color: #3b82f6;
        background: #fff;
    }
    #main-container-body .sm-tp-workspace-body {
        flex: 1;
        padding: 20px 22px 24px;
    }
    #main-container-body .sm-tp-panel-head {
        margin-bottom: 16px;
    }
    #main-container-body .sm-tp-invite-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        column-gap: 12px;
        row-gap: 12px;
        padding: 14px 16px;
        margin-bottom: 16px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
    }
    #main-container-body .sm-tp-invite-btn {
        margin-bottom: 1px;
    }
    #main-container-body .sm-tp-pill {
        display: inline-block;
        font-size: 11px;
        font-weight: 500;
        padding: 3px 8px;
        border-radius: 6px;
    }
    #main-container-body .sm-tp-pill--ok {
        background: #dcfce7;
        color: #14532d;
    }
    #main-container-body .sm-tp-pill--muted {
        background: #f3f4f6;
        color: #6b7280;
    }
    #main-container-body .sm-tp-upload-zone {
        padding: 16px;
        margin-bottom: 16px;
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        background: #f8fafc;
    }
    #main-container-body .sm-tp-materials-list {
        display: flex;
        flex-direction: column;
        row-gap: 8px;
    }
    #main-container-body .sm-tp-material-row {
        display: flex;
        align-items: center;
        column-gap: 12px;
        padding: 12px 14px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
    }
    #main-container-body .sm-tp-material-icon {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eff6ff;
        color: #1d4ed8;
        border-radius: 8px;
        font-size: 18px;
    }
    #main-container-body .sm-tp-material-name {
        flex: 1;
        font-size: 13px;
        font-weight: 500;
        color: #111827;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    #main-container-body .sm-tp-eval-form {
        padding: 16px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fafbfc;
    }
    #main-container-body .sm-tp-inline-empty,
    #main-container-body .sm-tp-workspace-empty {
        text-align: center;
        color: #94a3b8;
        font-size: 13px;
    }
    #main-container-body .sm-tp-workspace-empty {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 48px 24px;
    }
    #main-container-body .sm-tp-workspace-empty-icon {
        width: 64px;
        height: 64px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: #f1f5f9;
        color: #94a3b8;
        font-size: 32px;
        margin-bottom: 16px;
    }
    #main-container-body .sm-table-actions.justify-content-end {
        justify-content: flex-end;
        width: 100%;
    }
    #main-container-body .staff-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 12px;
    }
    #main-container-body .staff-card {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 16px;
        background: #fff;
        border: 0.5px solid #e5e7eb;
        border-radius: 12px;
        transition: box-shadow 0.15s, border-color 0.15s;
    }
    #main-container-body .staff-card:hover {
        border-color: #bfdbfe;
        box-shadow: 0 4px 12px rgba(29, 78, 216, 0.08);
    }
    @media (max-width: 992px) {
        #main-container-body .metric-grid,
        #main-container-body .sm-metric-grid { grid-template-columns: repeat(2, 1fr); }
        #main-container-body .two-col,
        #main-container-body .sm-two-col { grid-template-columns: 1fr; }
    }
</style>
