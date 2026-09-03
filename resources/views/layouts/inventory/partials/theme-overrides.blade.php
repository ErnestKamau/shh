{{--
  Bridges legacy inventory markup to lab surface / workflow panel styling
  using the HIO/LS design system and UI Kit tokens.
--}}
<style>
	/* ==========================================================================
	   1. CORE BASE & TYPOGRAPHY
	   ========================================================================== */
	.inventory-page,
	.inventory-page main,
	.inventory-page section {
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, -apple-system, sans-serif);
		color: var(--ls-color-ink, var(--workflow-text-main, #1e293b));
		-webkit-font-smoothing: antialiased;
	}

	.inventory-page h1,
	.inventory-page h2,
	.inventory-page h3,
	.inventory-page h4,
	.inventory-page h5,
	.inventory-page h6 {
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-weight: var(--ls-font-semibold, 600);
		color: var(--ls-color-ink, #1e293b);
		letter-spacing: -0.015em;
	}

	.inventory-page h2.p-4,
	.inventory-page h3.p-4,
	.inventory-page h4.p-4,
	.inventory-page .inventory-page-header {
		padding: 0.65rem 0 1rem !important;
		font-size: 1.15rem;
		font-weight: 650;
		color: var(--workflow-text-main, #1e293b);
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 0.65rem;
		margin-bottom: 0.25rem;
	}

	.inventory-page h2.p-4 > .mdi:first-child,
	.inventory-page h3.p-4 > .mdi:first-child,
	.inventory-page h4.p-4 > .mdi:first-child {
		color: var(--color-primary, #8b1e2d);
		font-size: 1.35rem;
		margin-right: 0.25rem;
	}

	.inventory-page .my-small-text {
		font-size: var(--ls-text-xs, 0.75rem) !important;
	}

	/* ==========================================================================
	   2. BREADCRUMBS & BATCH HEADER BARS
	   ========================================================================== */
	.inventory-page .breadcrumb {
		background: transparent;
		padding: 0.35rem 0;
		margin-bottom: 0.5rem;
		font-size: 0.75rem;
		font-weight: 500;
	}

	.inventory-page .breadcrumb a {
		color: var(--workflow-muted, #64748b);
		text-decoration: none;
	}

	.inventory-page .breadcrumb a:hover {
		color: var(--color-primary, #8b1e2d);
		text-decoration: underline;
	}

	.inventory-page .breadcrumb-item.active {
		color: var(--workflow-text-main, #1e293b);
		font-weight: 600;
	}

	.inventory-page .batch-header-bar {
		display: flex;
		align-items: center;
		justify-content: space-between;
		flex-wrap: wrap;
		gap: 0.75rem;
		padding: 0.65rem 0;
		border-bottom: 1px solid var(--workflow-border, #e2e8f0);
		margin-bottom: 1rem;
	}

	.inventory-page .batch-header-top {
		display: flex;
		align-items: center;
		gap: 0.75rem;
		flex-wrap: wrap;
	}

	.inventory-page .batch-title-group {
		display: inline-flex;
		align-items: center;
		gap: 0.5rem;
		flex-wrap: wrap;
	}

	.inventory-page .batch-code-label {
		font-size: 1.125rem;
		font-weight: 700;
		color: var(--workflow-text-main, #1e293b);
		letter-spacing: -0.02em;
	}

	.inventory-page .batch-stage-pill {
		display: inline-flex;
		align-items: center;
		gap: 0.35rem;
		padding: 0.25rem 0.65rem;
		border-radius: 999px;
		background: var(--color-primary-soft, #f8ecec);
		color: var(--color-primary, #8b1e2d);
		border: 1px solid var(--color-primary-border-soft, #e2b4b4);
		font-size: 0.75rem;
		font-weight: 600;
	}

	/* ==========================================================================
	   3. CARDS, SURFACES & WRAPPERS
	   ========================================================================== */
	.inventory-page > .bg-light,
	.inventory-page .bg-light:not(.modal-body):not(.dropdown-menu):not(.card-body):not(thead):not(.badge) {
		background: var(--ls-color-surface, #ffffff) !important;
		border: 1px solid var(--workflow-border, var(--color-border, #e2e8f0));
		border-radius: 12px;
		box-shadow: var(--card-shadow, 0 1px 3px 0 rgba(0, 0, 0, 0.06));
		padding: 0 !important;
		overflow: hidden;
		margin-bottom: 1rem;
	}

	.inventory-page .bg-light > .table-responsive,
	.inventory-page .bg-light > .card-body {
		padding: 0.85rem 1rem;
	}

	.inventory-page .card {
		border-radius: 12px;
		border: 1px solid var(--workflow-border, #e2e8f0);
		box-shadow: var(--card-shadow, 0 1px 3px 0 rgba(0, 0, 0, 0.06));
		background: #ffffff;
		overflow: hidden;
		margin-bottom: 1rem;
	}

	.inventory-page .card-body {
		padding: 1rem 1.25rem;
	}

	.inventory-page .card-head,
	.inventory-page .card-head-sm,
	.inventory-page .card .card-header:not(.tab-card-header) {
		background: #f8fafc;
		border-bottom: 1px solid var(--workflow-border, #e2e8f0);
		padding: 0.75rem 1rem;
		font-size: 0.875rem;
		font-weight: 600;
		color: var(--workflow-text-main, #1e293b);
	}

	.inventory-page .card-title {
		font-size: 0.95rem;
		font-weight: 650;
		color: var(--workflow-text-main, #1e293b);
		margin-bottom: 0.85rem;
		display: flex;
		align-items: center;
		justify-content: space-between;
	}

	.inventory-page .workflow-board-panel {
		background: #ffffff;
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 12px;
		box-shadow: var(--card-shadow, 0 1px 3px 0 rgba(0, 0, 0, 0.06));
		overflow: hidden;
		margin-bottom: 1rem;
	}

	.inventory-page .workflow-board-panel-body {
		padding: 0;
	}

	/* ==========================================================================
	   4. TABS & TAB CARDS
	   ========================================================================== */
	.inventory-page .tab-card {
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 12px;
		box-shadow: var(--card-shadow, 0 1px 3px 0 rgba(0, 0, 0, 0.06));
		overflow: hidden;
		background: #ffffff;
		margin-bottom: 1rem;
	}

	.inventory-page .tab-card .card-header.tab-card-header {
		background: #f8fafc;
		border-bottom: 1px solid var(--workflow-border, #e2e8f0);
		padding: 0.35rem 0.85rem 0;
	}

	.inventory-page .tab-card-header > .nav-tabs {
		border-bottom: none;
		margin: 0;
		gap: 0.25rem;
	}

	.inventory-page .tab-card-header > .nav-tabs > li {
		margin-right: 0;
	}

	.inventory-page .tab-card-header > .nav-tabs > li > a,
	.inventory-page .nav-tabs .nav-link {
		border: 1px solid transparent;
		border-top-left-radius: 8px;
		border-top-right-radius: 8px;
		color: var(--workflow-muted, #64748b);
		font-size: var(--ls-text-base, 0.8125rem);
		font-weight: 600;
		padding: 0.55rem 1rem;
		transition: all 0.15s ease;
		background: transparent;
	}

	.inventory-page .tab-card-header > .nav-tabs > li > a:hover,
	.inventory-page .nav-tabs .nav-link:hover {
		color: var(--color-primary, #8b1e2d);
		background-color: rgba(255, 255, 255, 0.6);
		border-color: transparent;
	}

	.inventory-page .tab-card-header > .nav-tabs > li > a.show,
	.inventory-page .tab-card-header > .nav-tabs > li > a.active,
	.inventory-page .tab-card .nav-link.active {
		background-color: #ffffff !important;
		border-color: var(--workflow-border, #e2e8f0) var(--workflow-border, #e2e8f0) #ffffff !important;
		color: var(--color-primary, #8b1e2d) !important;
		border-bottom: 2px solid var(--color-primary, #8b1e2d) !important;
		font-weight: 650;
	}

	.inventory-page .tab-content {
		padding: 1rem;
	}

	/* ==========================================================================
	   5. TABLES (HIO/LS STYLING)
	   ========================================================================== */
	.inventory-page .table-responsive {
		border-radius: 8px;
		margin-bottom: 0;
	}

	.inventory-page .table {
		width: 100%;
		margin-bottom: 0;
		color: var(--workflow-text-main, #1e293b);
		border-collapse: separate;
		border-spacing: 0;
	}

	.inventory-page .table.table-bordered {
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 8px;
	}

	.inventory-page .table thead th {
		background: #f8fafc !important;
		color: var(--workflow-muted, #64748b) !important;
		font-size: var(--ls-text-xs, 0.6875rem);
		font-weight: 650;
		text-transform: uppercase;
		letter-spacing: 0.05em;
		border-top: none;
		border-bottom: 1px solid var(--workflow-border, #e2e8f0) !important;
		border-left: none;
		border-right: none;
		padding: 0.65rem 0.85rem;
		white-space: nowrap;
		vertical-align: middle;
	}

	.inventory-page .table thead.bg-light,
	.inventory-page .table thead.bg-light th,
	.inventory-page .table thead.p-2 {
		background: #f8fafc !important;
	}

	.inventory-page .table tbody td {
		vertical-align: middle;
		color: var(--workflow-text-main, #1e293b);
		border-top: 1px solid var(--workflow-border, #e2e8f0);
		border-bottom: none;
		border-left: none;
		border-right: none;
		padding: 0.65rem 0.85rem;
		font-size: 0.8125rem;
	}

	.inventory-page .table-striped tbody tr:nth-of-type(odd) {
		background-color: rgba(248, 250, 252, 0.65);
	}

	.inventory-page .table-hover tbody tr:hover {
		background-color: var(--ls-blue-soft, #eff6ff);
	}

	.inventory-page .table td img {
		border-radius: 6px;
		border: 1px solid #e2e8f0;
		object-fit: cover;
	}

	/* Approval and status indicators on table rows */
	.inventory-page .approval-complete {
		border-left: 4px solid #16a34a !important;
	}
	.inventory-page .purchase-order-sent {
		border-left: 4px solid #16a34a !important;
	}
	.inventory-page .awaiting-approval {
		border-left: 4px solid #eab308 !important;
	}
	.inventory-page .partially-approved {
		border-left: 4px solid #f97316 !important;
	}

	/* ==========================================================================
	   6. FORMS & INPUT CONTROLS (MATCHING .ls-field)
	   ========================================================================== */
	.inventory-page .form-group {
		margin-bottom: 0.95rem;
	}

	.inventory-page .form-group label,
	.inventory-page label.control-label {
		font-size: 0.75rem;
		font-weight: 600;
		color: var(--ls-ink, #1e293b);
		margin-bottom: 0.35rem;
		display: inline-block;
	}

	.inventory-page .form-group label small,
	.inventory-page label.control-label small {
		font-size: 0.7rem;
		font-weight: 400;
		color: var(--workflow-muted, #64748b);
	}

	.inventory-page .form-control,
	.inventory-page select.form-control,
	.inventory-page input[type="text"].form-control,
	.inventory-page input[type="date"].form-control,
	.inventory-page input[type="email"].form-control,
	.inventory-page input[type="number"].form-control,
	.inventory-page textarea.form-control {
		height: 36px;
		border: 1px solid var(--ls-border, #e2e8f0);
		border-radius: var(--ls-radius, 8px);
		background-color: #ffffff;
		color: var(--ls-ink, #1e293b);
		font-size: 0.8125rem;
		padding: 0.45rem 0.75rem;
		box-shadow: none;
		transition: border-color 0.15s ease, box-shadow 0.15s ease;
	}

	.inventory-page textarea.form-control {
		height: auto;
		min-height: 80px;
	}

	.inventory-page .form-control:focus {
		border-color: var(--ls-blue-focus, #93c5fd);
		box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.35);
		outline: none;
		background-color: #ffffff;
	}

	.inventory-page .form-control[disabled],
	.inventory-page .form-control[readonly] {
		background-color: #f1f5f9;
		color: #64748b;
		cursor: not-allowed;
	}

	.inventory-page .form-control-sm {
		height: 30px;
		font-size: 0.75rem;
		padding: 0.25rem 0.5rem;
		border-radius: 6px;
	}

	/* ==========================================================================
	   7. SELECT2 THEMING (HIO/LS STANDARDS)
	   ========================================================================== */
	.inventory-page .select2-container .select2-selection--single {
		height: 36px !important;
		border: 1px solid var(--ls-border, #e2e8f0) !important;
		border-radius: var(--ls-radius, 8px) !important;
		background-color: #ffffff !important;
		display: flex !important;
		align-items: center !important;
		transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
	}

	.inventory-page .select2-container--default .select2-selection--single .select2-selection__rendered {
		color: var(--ls-ink, #1e293b) !important;
		font-size: 0.8125rem !important;
		line-height: 34px !important;
		padding-left: 0.75rem !important;
		padding-right: 1.75rem !important;
	}

	.inventory-page .select2-container--default .select2-selection--single .select2-selection__arrow {
		height: 34px !important;
		right: 6px !important;
	}

	.inventory-page .select2-container--default.select2-container--focus .select2-selection--single,
	.inventory-page .select2-container--default.select2-container--open .select2-selection--single {
		border-color: var(--ls-blue-focus, #93c5fd) !important;
		box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.35) !important;
		outline: none !important;
	}

	.inventory-page .select2-container .select2-selection--multiple {
		min-height: 36px !important;
		border: 1px solid var(--ls-border, #e2e8f0) !important;
		border-radius: var(--ls-radius, 8px) !important;
		background-color: #ffffff !important;
		padding: 2px 4px !important;
		transition: border-color 0.15s ease, box-shadow 0.15s ease !important;
	}

	.inventory-page .select2-container--default.select2-container--focus .select2-selection--multiple,
	.inventory-page .select2-container--default.select2-container--open .select2-selection--multiple {
		border-color: var(--ls-blue-focus, #93c5fd) !important;
		box-shadow: 0 0 0 3px rgba(147, 197, 253, 0.35) !important;
		outline: none !important;
	}

	.inventory-page .select2-container--default .select2-selection--multiple .select2-selection__choice {
		background-color: var(--color-primary-soft, #f8ecec) !important;
		border: 1px solid var(--color-primary-border-soft, #e2b4b4) !important;
		color: var(--color-primary, #8b1e2d) !important;
		border-radius: 6px !important;
		padding: 2px 8px !important;
		font-size: 0.75rem !important;
		font-weight: 600 !important;
		margin: 2px 4px 2px 0 !important;
	}

	.inventory-page .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
		color: var(--color-primary, #8b1e2d) !important;
		margin-right: 4px !important;
		font-weight: bold !important;
	}

	.inventory-page .select2-dropdown {
		border: 1px solid var(--ls-border, #e2e8f0) !important;
		border-radius: var(--ls-radius-lg, 10px) !important;
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12) !important;
		z-index: 1060 !important;
		overflow: hidden !important;
	}

	.inventory-page .select2-dropdown .select2-search__field {
		border: 1px solid #e2e8f0 !important;
		border-radius: 6px !important;
		padding: 0.4rem 0.6rem !important;
		font-size: 0.8125rem !important;
		margin-bottom: 4px !important;
	}

	.inventory-page .select2-results__option {
		padding: 0.45rem 0.75rem !important;
		font-size: 0.8125rem !important;
		color: var(--ls-ink, #1e293b) !important;
	}

	.inventory-page .select2-container--default .select2-results__option--highlighted[aria-selected] {
		background-color: var(--ls-blue-soft, #eff6ff) !important;
		color: var(--ls-ink, #1e293b) !important;
	}

	.inventory-page .select2-container--default .select2-results__option[aria-selected="true"] {
		background-color: #f1f5f9 !important;
		color: var(--color-primary, #8b1e2d) !important;
		font-weight: 600 !important;
	}

	/* ==========================================================================
	   8. BUTTONS & CONTROLS
	   ========================================================================== */
	.inventory-page .btn {
		border-radius: 6px;
		font-size: 0.8125rem;
		font-weight: 500;
		padding: 0.4rem 0.85rem;
		transition: all 0.15s ease;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: 0.35rem;
	}

	.inventory-page .btn-sm {
		height: 30px;
		padding: 0.25rem 0.65rem;
		font-size: 0.75rem;
	}

	.inventory-page .btn-primary {
		background-color: var(--color-primary, #8b1e2d) !important;
		border-color: var(--color-primary, #8b1e2d) !important;
		color: #ffffff !important;
	}

	.inventory-page .btn-primary:hover:not(:disabled) {
		background-color: var(--color-primary-hover, #6b0000) !important;
		border-color: var(--color-primary-hover, #6b0000) !important;
		box-shadow: 0 2px 4px rgba(139, 30, 45, 0.25);
	}

	.inventory-page .btn-outline-primary {
		color: var(--color-primary, #8b1e2d) !important;
		border-color: var(--color-primary, #8b1e2d) !important;
		background-color: transparent !important;
	}

	.inventory-page .btn-outline-primary:hover {
		background-color: var(--color-primary, #8b1e2d) !important;
		color: #ffffff !important;
	}

	.inventory-page .btn-secondary,
	.inventory-page .btn-default {
		background-color: #f1f5f9 !important;
		border-color: #e2e8f0 !important;
		color: var(--workflow-text-main, #1e293b) !important;
	}

	.inventory-page .btn-secondary:hover,
	.inventory-page .btn-default:hover {
		background-color: #e2e8f0 !important;
		color: #0f172a !important;
	}

	.inventory-page .btn-transparent {
		background: transparent !important;
		border-color: transparent !important;
		box-shadow: none !important;
	}

	.inventory-page .btn-transparent:hover {
		background: rgba(0, 0, 0, 0.04) !important;
	}

	.inventory-page .workflow-header-receive-btn {
		height: 32px;
		padding: 0 14px !important;
		font-size: 0.82rem !important;
		border-radius: 6px !important;
		display: inline-flex !important;
		align-items: center;
		justify-content: center;
		gap: 5px;
		font-weight: 600 !important;
		background-color: var(--color-primary, #8b1e2d) !important;
		border-color: var(--color-primary, #8b1e2d) !important;
		color: #ffffff !important;
		transition: all 0.2s ease-in-out;
		vertical-align: middle;
	}

	.inventory-page .workflow-header-receive-btn:hover:not(:disabled) {
		background-color: var(--color-primary-hover, #6b0000) !important;
		border-color: var(--color-primary-hover, #6b0000) !important;
		box-shadow: 0 4px 8px rgba(139, 30, 45, 0.25) !important;
	}

	/* ==========================================================================
	   9. BADGES & STATUS PILLS
	   ========================================================================== */
	.inventory-page .badge {
		font-weight: 600;
		font-size: 0.75rem;
		padding: 0.25rem 0.55rem;
		border-radius: 6px;
		display: inline-flex;
		align-items: center;
		gap: 0.25rem;
	}

	.inventory-page .badge-pill {
		border-radius: 999px;
		padding: 0.25rem 0.65rem;
	}

	.inventory-page .badge-primary {
		background-color: var(--color-primary-soft, #f8ecec) !important;
		color: var(--color-primary, #8b1e2d) !important;
		border: 1px solid var(--color-primary-border-soft, #e2b4b4);
	}

	.inventory-page .badge-success {
		background-color: #dcfce7 !important;
		color: #166534 !important;
		border: 1px solid #bbf7d0;
	}

	.inventory-page .badge-warning {
		background-color: #fef3c7 !important;
		color: #92400e !important;
		border: 1px solid #fde68a;
	}

	.inventory-page .badge-danger {
		background-color: #fee2e2 !important;
		color: #991b1b !important;
		border: 1px solid #fecaca;
	}

	.inventory-page .badge-info {
		background-color: #e0f2fe !important;
		color: #075985 !important;
		border: 1px solid #bae6fd;
	}

	.inventory-page .badge.badge-pill.bg-white,
	.inventory-page .badge-light {
		background-color: #ffffff !important;
		color: var(--workflow-text-main, #1e293b) !important;
		border: 1px solid var(--workflow-border, #e2e8f0);
	}

	/* ==========================================================================
	   10. MODALS (MATCHING .ls-modal-card)
	   ========================================================================== */
	.inventory-page .modal-content,
	.modal .modal-content {
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 12px;
		box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
		overflow: hidden;
		background-color: #ffffff;
	}

	.inventory-page .modal-header,
	.modal .modal-header {
		background: #f8fafc;
		border-bottom: 1px solid var(--workflow-border, #e2e8f0);
		padding: 0.85rem 1.25rem;
		border-top-left-radius: 12px;
		border-top-right-radius: 12px;
		display: flex;
		align-items: center;
		justify-content: space-between;
	}

	.inventory-page .modal-title,
	.modal .modal-title {
		font-size: 0.95rem;
		font-weight: 650;
		color: var(--workflow-text-main, #1e293b);
		margin: 0;
		display: flex;
		align-items: center;
		gap: 0.4rem;
	}

	.inventory-page .modal-title .mdi,
	.modal .modal-title .mdi {
		color: var(--color-primary, #8b1e2d);
		font-size: 1.15rem;
	}

	.inventory-page .modal-body,
	.modal .modal-body {
		padding: 1.25rem;
		background: #ffffff;
	}

	.inventory-page .modal-footer,
	.modal .modal-footer {
		background: #f8fafc;
		border-top: 1px solid var(--workflow-border, #e2e8f0);
		padding: 0.75rem 1.25rem;
		border-bottom-left-radius: 12px;
		border-bottom-right-radius: 12px;
		display: flex;
		align-items: center;
		justify-content: flex-end;
		gap: 0.5rem;
	}

	/* ==========================================================================
	   11. DATATABLES STYLING
	   ========================================================================== */
	.inventory-page .dataTables_wrapper {
		padding: 0.25rem 0;
	}

	.inventory-page .dataTables_wrapper .dataTables_length,
	.inventory-page .dataTables_wrapper .dataTables_filter {
		margin-bottom: 0.75rem;
		font-size: 0.75rem;
		color: var(--workflow-muted, #64748b);
		font-weight: 500;
	}

	.inventory-page .dataTables_wrapper .dataTables_length select {
		height: 30px;
		border: 1px solid #e2e8f0;
		border-radius: 6px;
		padding: 0.2rem 0.5rem;
		font-size: 0.75rem;
		color: var(--workflow-text-main, #1e293b);
		margin: 0 0.25rem;
	}

	.inventory-page .dataTables_wrapper .dataTables_filter input {
		height: 30px;
		border: 1px solid #e2e8f0;
		border-radius: 6px;
		padding: 0.2rem 0.6rem;
		font-size: 0.75rem;
		color: var(--workflow-text-main, #1e293b);
		margin-left: 0.4rem;
		outline: none;
	}

	.inventory-page .dataTables_wrapper .dataTables_filter input:focus {
		border-color: var(--ls-blue-focus, #93c5fd);
		box-shadow: 0 0 0 2px rgba(147, 197, 253, 0.35);
	}

	.inventory-page .dataTables_wrapper .dataTables_info {
		padding-top: 0.75rem;
		font-size: 0.75rem;
		color: var(--workflow-muted, #64748b);
		font-weight: 500;
	}

	.inventory-page .dataTables_wrapper .dataTables_paginate {
		padding-top: 0.75rem;
	}

	.inventory-page .dataTables_wrapper .dataTables_paginate .paginate_button {
		border-radius: 6px !important;
		padding: 0.25rem 0.65rem !important;
		font-size: 0.75rem !important;
		font-weight: 600 !important;
		border: 1px solid transparent !important;
		color: var(--workflow-text-main, #1e293b) !important;
		background: transparent !important;
		margin: 0 2px !important;
		transition: all 0.15s ease !important;
	}

	.inventory-page .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
		background: #f1f5f9 !important;
		color: var(--color-primary, #8b1e2d) !important;
		border-color: #e2e8f0 !important;
	}

	.inventory-page .dataTables_wrapper .dataTables_paginate .paginate_button.current,
	.inventory-page .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
		background: var(--color-primary, #8b1e2d) !important;
		color: #ffffff !important;
		border-color: var(--color-primary, #8b1e2d) !important;
	}

	.inventory-page .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
	.inventory-page .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
		color: #cbd5e1 !important;
		cursor: not-allowed !important;
		background: transparent !important;
	}

	.inventory-page .dt-buttons {
		margin-bottom: 0.75rem;
		display: inline-flex;
		gap: 0.35rem;
	}

	.inventory-page .dt-buttons .btn,
	.inventory-page .dt-buttons .dt-button {
		border-radius: 6px !important;
		padding: 0.25rem 0.65rem !important;
		font-size: 0.75rem !important;
		font-weight: 600 !important;
		background-color: #f8fafc !important;
		border: 1px solid #e2e8f0 !important;
		color: var(--workflow-text-main, #1e293b) !important;
	}

	.inventory-page .dt-buttons .btn:hover,
	.inventory-page .dt-buttons .dt-button:hover {
		background-color: #f1f5f9 !important;
		border-color: #cbd5e1 !important;
		color: var(--color-primary, #8b1e2d) !important;
	}

	/* ==========================================================================
	   12. REQUISITION DOCUMENT FLOW & STEPPERS
	   ========================================================================== */
	.inventory-page #document-flow-holder {
		white-space: nowrap;
		overflow-x: auto;
		overflow-y: hidden;
		padding: 0.75rem 0.25rem 1rem;
		display: flex;
		gap: 0.85rem;
	}

	.inventory-page .flow-doc-holder {
		display: inline-block;
		padding: 0;
		vertical-align: text-top;
		flex-shrink: 0;
	}

	.inventory-page .flow-doc {
		max-width: 280px;
		width: 240px;
		text-align: left;
		border-radius: 10px;
		border: 1px solid var(--workflow-border, #e2e8f0);
		box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.06);
		background: #ffffff;
		overflow: hidden;
		transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
	}

	.inventory-page .flow-doc:hover {
		transform: translateY(-2px);
		box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
	}

	.inventory-page .flow-doc.is_current {
		border: 2px solid var(--color-primary, #8b1e2d);
		box-shadow: 0 0 0 3px rgba(139, 30, 45, 0.15);
	}

	.inventory-page .flow-doc.is_deleted {
		border-color: #fca5a5;
		background: #fef2f2;
	}

	.inventory-page .flow-doc .header {
		font-size: 0.8125rem;
		font-weight: 650;
		color: var(--workflow-text-main, #1e293b);
		padding: 0.6rem 0.85rem;
		background: #f8fafc;
		border-bottom: 1px solid var(--workflow-border, #e2e8f0);
		display: flex;
		align-items: center;
		justify-content: space-between;
	}

	.inventory-page .flow-doc .body {
		font-size: 0.75rem;
		padding: 0.65rem 0.85rem;
		color: var(--workflow-muted, #64748b);
		border-bottom: 1px solid #f1f5f9;
		line-height: 1.4;
	}

	.inventory-page .flow-doc .footer {
		font-size: 0.75rem;
		padding: 0.45rem 0.85rem;
		color: var(--color-primary, #8b1e2d);
		background: #ffffff;
		font-weight: 600;
	}

	/* ==========================================================================
	   13. ALERTS & CALLOUTS
	   ========================================================================== */
	.inventory-page .alert {
		border-radius: 8px;
		font-size: 0.8125rem;
		padding: 0.75rem 1rem;
		border: 1px solid transparent;
		display: flex;
		align-items: center;
		gap: 0.5rem;
		margin-bottom: 1rem;
	}

	.inventory-page .alert-callout {
		border-left-width: 4px;
	}

	.inventory-page .alert-info {
		background-color: #f0f9ff;
		border-color: #bae6fd;
		color: #0369a1;
	}

	.inventory-page .alert-success {
		background-color: #f0fdf4;
		border-color: #bbf7d0;
		color: #15803d;
	}

	.inventory-page .alert-warning {
		background-color: #fffbeb;
		border-color: #fde68a;
		color: #b45309;
	}

	.inventory-page .alert-danger {
		background-color: #fef2f2;
		border-color: #fecaca;
		color: #b91c1c;
	}

	/* ==========================================================================
	   14. BARCODES, RATINGS & SPECIALIZED ELEMENTS
	   ========================================================================== */
	.inventory-page .barcode svg {
		max-height: 44px;
		width: auto;
	}

	.inventory-page .rating-star {
		padding: 2px;
		font-size: 16px;
		color: #cbd5e1;
		transition: color 0.15s ease;
	}

	.inventory-page .rating-star:hover,
	.inventory-page .rating-star.selected {
		cursor: pointer;
		color: #eab308;
	}

	.inventory-page .removeThis {
		position: absolute;
		top: -4px;
		right: -4px;
		width: 18px;
		height: 18px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		font-size: 11px;
		background-color: #ef4444;
		border-radius: 50%;
		color: #ffffff;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
		cursor: pointer;
		z-index: 10;
	}

	/* ==========================================================================
	   15. SIDEBAR OVERRIDES
	   ========================================================================== */
	#sidebar-container .list-group > a.list-group-item {
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.8125rem;
		font-weight: 500;
	}

	#sidebar-container .list-group > a.list-group-item.active {
		background-color: var(--color-primary, #8b1e2d) !important;
		border-color: var(--color-primary, #8b1e2d) !important;
		color: #ffffff !important;
		font-weight: 650;
	}

	#sidebar-container .list-group > a.list-group-item .badge-danger:empty,
	#sidebar-container .list-group > a.list-group-item .badge:empty {
		display: none;
	}
</style>
