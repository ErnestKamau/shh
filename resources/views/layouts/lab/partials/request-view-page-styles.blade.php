<style>
	/* Compact density aligned with RFT / sample-workflow pages */
	.request-view-page.workflow-board-page {
		padding-bottom: 1.5rem;
		background: var(--workflow-bg, #f8fafc);
		min-height: calc(100vh - 56px);
		font-size: 0.8125rem;
	}

	.request-view-page .request-view-shell {
		background: transparent;
		max-width: none;
		width: 100%;
		margin-left: 0;
		margin-right: 0;
		padding-left: 0.25rem;
		padding-right: 0.25rem;
	}

	.request-view-page .breadcrumb-container {
		margin-bottom: 0.75rem;
	}

	/* Lab console: context rail + work canvas — equal height cards */
	.request-view-page .rv-console {
		display: grid;
		grid-template-columns: minmax(260px, 28%) minmax(0, 1fr);
		gap: 1rem;
		align-items: stretch;
	}

	.request-view-page .rv-console-canvas {
		min-width: 0;
		display: flex;
		flex-direction: column;
	}

	.request-view-page .rv-console-rail {
		position: sticky;
		top: 0.75rem;
		max-height: calc(100vh - 4.5rem);
		overflow-y: auto;
		overscroll-behavior: contain;
		display: flex;
		flex-direction: column;
		min-height: 100%;
	}

	/* Sample collection canvas tab */
	.request-view-page .rv-sample-collection-tab__header {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: 1rem;
		margin-bottom: 1.15rem;
		flex-wrap: wrap;
	}

	.request-view-page .rv-sample-collection-tab__title {
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 1rem;
		font-weight: 600;
		letter-spacing: -0.015em;
		color: #1e293b;
		margin: 0;
		display: flex;
		align-items: center;
		gap: 0.4rem;
	}

	.request-view-page .rv-sample-collection-tab__hint {
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.8125rem;
		font-weight: 400;
		color: #64748b;
	}

	.request-view-page .rv-sample-collection-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
		gap: 0.85rem;
	}

	.request-view-page .rv-sample-collection-card {
		display: flex;
		gap: 0.75rem;
		align-items: flex-start;
		padding: 0.85rem 0.95rem;
		background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
		border: 1px solid #e2e8f0;
		border-radius: 10px;
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
	}

	.request-view-page .rv-sample-collection-card__icon.ls-icon-tile__glyph,
	.request-view-page .rv-sample-collection-card .ls-icon-tile__glyph {
		flex-shrink: 0;
		width: 2.5rem;
		height: 2.5rem;
		font-size: 1.2rem;
		border-radius: 10px;
	}

	.request-view-page .rv-tab-title-icon.ls-icon-tile__glyph {
		width: 1.75rem;
		height: 1.75rem;
		font-size: 0.95rem;
		border-radius: 8px;
		margin-right: 0.15rem;
	}

	.request-view-page .rv-tab-panel-header {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: 1rem;
		flex-wrap: wrap;
		padding: 1rem 1.15rem 0.85rem;
		margin-bottom: 0.25rem;
	}

	.request-view-page .rv-tab-panel-header .btn-primary {
		background: var(--workflow-accent, var(--color-primary, #8b1e2d));
		border-color: var(--workflow-accent, var(--color-primary, #8b1e2d));
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-weight: 600;
		font-size: 0.78rem;
		border-radius: 8px;
	}

	.request-view-page .rv-tab-panel-header .btn-primary:hover,
	.request-view-page .rv-tab-panel-header .btn-primary:focus {
		background: color-mix(in srgb, var(--workflow-accent, #8b1e2d) 88%, #000);
		border-color: color-mix(in srgb, var(--workflow-accent, #8b1e2d) 88%, #000);
	}

	.request-view-page .rv-sample-collection-card__label {
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.7rem;
		font-weight: 500;
		letter-spacing: 0.01em;
		text-transform: none;
		color: #64748b;
		margin-bottom: 0.15rem;
	}

	.request-view-page .rv-sample-collection-card__value {
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.875rem;
		font-weight: 500;
		color: #0f172a;
		line-height: 1.35;
		word-break: break-word;
	}

	.request-view-page .rv-sample-collection-empty {
		text-align: center;
		padding: 2.5rem 1rem;
		color: #64748b;
		border: 1px dashed #cbd5e1;
		border-radius: 10px;
		background: #f8fafc;
	}

	.request-view-page .rv-sample-collection-empty .mdi {
		font-size: 2rem;
		display: block;
		margin-bottom: 0.5rem;
		color: #94a3b8;
	}

	/* Request-header quotation bell */
	.request-view-page .rv-quote-bell {
		position: relative;
		flex-shrink: 0;
	}

	.request-view-page .rv-quote-bell__btn {
		position: relative;
		width: 2.15rem;
		height: 2.15rem;
		padding: 0;
		border-radius: 8px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}

	.request-view-page .rv-quote-bell__btn .mdi {
		font-size: 1.2rem;
	}

	.request-view-page .rv-quote-bell__btn.is-awaiting .mdi {
		color: #b45309;
		animation: rv-approval-pulse 1.6s ease-in-out infinite;
	}

	/* Approver green check — only when pending + configured role */
	.request-view-page .rv-quote-approve__btn {
		background: #ecfdf5 !important;
		border-color: #a7f3d0 !important;
		color: #047857 !important;
	}

	.request-view-page .rv-quote-approve__btn .mdi {
		color: #047857 !important;
		font-size: 1.25rem;
		animation: rv-approve-shake 1.35s ease-in-out infinite;
		transform-origin: 50% 55%;
	}

	.request-view-page .rv-quote-approve__btn.is-awaiting .mdi {
		color: #047857 !important;
		animation: rv-approve-shake 1.35s ease-in-out infinite;
	}

	.request-view-page .rv-quote-approve__badge {
		background: #047857;
	}

	@keyframes rv-approve-shake {
		0%, 100% { transform: rotate(0deg) scale(1); }
		12% { transform: rotate(12deg) scale(1.05); }
		24% { transform: rotate(-10deg) scale(1.05); }
		36% { transform: rotate(8deg) scale(1.04); }
		48% { transform: rotate(-6deg) scale(1.03); }
		60% { transform: rotate(3deg) scale(1.02); }
		72% { transform: rotate(0deg) scale(1); }
	}

	@keyframes rv-approval-pulse {
		0%, 100% { opacity: 1; transform: scale(1); }
		50% { opacity: 0.72; transform: scale(1.06); }
	}

	@keyframes rv-bell-ring {
		0%, 100% { transform: rotate(0deg); }
		10% { transform: rotate(14deg); }
		20% { transform: rotate(-12deg); }
		30% { transform: rotate(10deg); }
		40% { transform: rotate(-8deg); }
		50% { transform: rotate(4deg); }
		60% { transform: rotate(0deg); }
	}

	.request-view-page .rv-quote-bell__badge {
		position: absolute;
		top: -4px;
		right: -4px;
		min-width: 1.05rem;
		height: 1.05rem;
		padding: 0 0.25rem;
		border-radius: 999px;
		background: var(--workflow-accent, #7a1f3d);
		color: #fff;
		font-size: 0.65rem;
		font-weight: 700;
		line-height: 1.05rem;
		text-align: center;
	}

	.request-view-page .rv-quote-bell {
		position: relative;
	}

	/* Placed under the approve control; JS clamps left to #main-container-body
	   so the panel stays in the page and never covers the sidebar. */
	.request-view-page .rv-quote-bell__menu {
		position: fixed;
		min-width: 17rem;
		max-width: min(22rem, calc(100vw - 1rem));
		max-height: min(70vh, 520px);
		overflow-y: auto;
		padding: 0.5rem 0;
		background: #fff;
		border: 1px solid #dbe5f0;
		border-radius: 10px;
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14);
		z-index: 1055;
	}

	.request-view-page .rv-quote-bell__item {
		display: flex;
		align-items: center;
		gap: 0.5rem;
		width: 100%;
		padding: 0.55rem 0.9rem;
		border: 0;
		background: transparent;
		text-align: left;
		font-size: 0.8125rem;
		font-weight: 500;
		color: #0369a1;
		cursor: pointer;
	}

	.request-view-page .rv-quote-bell__item:hover {
		background: #eff6ff;
		color: #1e3a8a;
	}

	.request-view-page .rv-quote-bell__item .mdi {
		color: #0369a1;
	}

	.request-view-page .rv-quote-bell__item .text-success,
	.request-view-page .rv-quote-bell__item .mdi.text-success {
		color: #047857 !important;
	}

	.request-view-page .rv-quote-bell__item--danger {
		color: #b91c1c;
	}

	.request-view-page .rv-quote-bell__item--danger .mdi {
		color: #b91c1c;
	}

	.request-view-page .rv-quote-bell__panel {
		padding: 0.65rem 0.9rem;
		border-top: 1px solid #e8eef4;
	}

	.request-view-page .rv-quote-bell__meta {
		padding: 0.35rem 0.9rem 0.55rem;
		font-size: 0.75rem;
		color: #64748b;
	}

	.request-view-page .rv-rail-panel {
		background: #fff;
		border: 1px solid var(--ls-border, var(--workflow-border, #e2e8f0));
		border-radius: 14px;
		box-shadow: 0 1px 2px rgb(15 23 42 / 0.04);
		overflow: hidden;
		flex: 1 1 auto;
		display: flex;
		flex-direction: column;
		min-height: 100%;
	}

	.request-view-page .rv-rail-section {
		padding: 0;
		border-bottom: 1px solid var(--ls-border, var(--workflow-border, #e2e8f0));
	}

	/* Client info — quotation-rail gloss head (baby blue) */
	.request-view-page .rv-rail-section--client {
		background: #fff;
	}

	.request-view-page .rv-rail-head {
		position: relative;
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		gap: 0.5rem;
		padding: 1rem 1.05rem 0.85rem;
		overflow: hidden;
		/* Diagonal wedge: wide at top-left, tapering toward top-right */
		background:
			linear-gradient(
				to bottom right,
				#eff6ff 0%,
				rgb(219 234 254 / 0.92) 12%,
				rgb(239 246 255 / 0.52) 26%,
				rgb(239 246 255 / 0.16) 38%,
				transparent 48%
			),
			#fff;
		border-bottom: 1px solid #eef2f7;
	}

	.request-view-page .rv-rail-head::before {
		content: '';
		position: absolute;
		inset: 0;
		background: linear-gradient(
			to bottom right,
			rgba(255, 255, 255, 0.78) 0%,
			rgba(255, 255, 255, 0.32) 9%,
			transparent 24%
		);
		pointer-events: none;
	}

	.request-view-page .rv-rail-head__copy {
		position: relative;
		z-index: 1;
		min-width: 0;
		flex: 1 1 auto;
	}

	.request-view-page .rv-rail-head__actions {
		position: relative;
		z-index: 1;
		flex: 0 0 auto;
	}

	.request-view-page .rv-rail-eyebrow {
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.65rem;
		font-weight: 700;
		letter-spacing: 0.08em;
		text-transform: uppercase;
		color: #1e3a8a;
		margin-bottom: 0.2rem;
	}

	.request-view-page .rv-rail-head-title {
		margin: 0;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 1.05rem;
		font-weight: 700;
		color: #0f172a;
		line-height: 1.25;
		word-break: break-word;
	}

	.request-view-page .rv-rail-head-subtitle {
		margin: 0.2rem 0 0;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.75rem;
		font-weight: 400;
		color: #64748b;
		line-height: 1.35;
		word-break: break-word;
	}

	.request-view-page .rv-rail-body {
		padding: 0.85rem 1.05rem;
	}

	.request-view-page .rv-rail-section:not(.rv-rail-section--client) {
		padding: 0.85rem 0.95rem;
	}

	.request-view-page .rv-rail-section:last-child {
		border-bottom: none;
		flex: 1 1 auto;
	}

	.request-view-page .rv-rail-section-heading {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 0.5rem;
		margin-bottom: 1rem;
	}

	/* Rail section titles — quotation-rail pattern, blue accent icons */
	.request-view-page .rv-rail-section-title {
		display: flex;
		align-items: center;
		gap: 0.35rem;
		margin: 0 0 0.7rem;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.72rem;
		font-weight: 700;
		letter-spacing: 0.03em;
		text-transform: uppercase;
		color: #475569;
	}

	.request-view-page .rv-rail-section-title .mdi {
		font-size: 0.95rem;
		color: #1e3a8a;
	}

	.request-view-page .rv-rail-icon-actions {
		gap: 0.35rem;
	}

	.request-view-page .rv-rail-edit-btn {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: 0.25rem;
		padding: 0.2rem 0.5rem;
		border: 1px solid color-mix(in srgb, #1e3a8a 20%, #e2e8f0);
		border-radius: 7px;
		background: #f8fafc;
		color: #1e3a8a;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.7rem;
		font-weight: 500;
		line-height: 1.2;
		cursor: pointer;
		transition: background 0.12s ease, border-color 0.12s ease, color 0.12s ease;
	}

	.request-view-page .rv-rail-edit-btn--icon {
		width: 1.35rem;
		height: 1.35rem;
		padding: 0;
		font-size: 0.8rem;
		border-radius: 5px;
	}

	.request-view-page .rv-rail-edit-btn:hover,
	.request-view-page .rv-rail-edit-btn:focus {
		background: #eff6ff;
		border-color: color-mix(in srgb, #1e3a8a 35%, #93c5fd);
		color: #1e3a8a;
		outline: none;
	}

	.request-view-page .rv-rail-identity-stack {
		display: block;
	}

	.request-view-page .rv-rail-identity-grid {
		display: grid;
		grid-template-columns: 1fr;
		gap: 0.85rem;
	}

	@media (min-width: 360px) {
		.request-view-page .rv-rail-identity-grid {
			grid-template-columns: 1fr 1fr;
			gap: 0.75rem 1rem;
		}
	}

	.request-view-page .rv-rail-empty {
		font-size: 0.7rem;
		font-weight: 400;
		color: var(--ls-muted, #64748b);
	}

	.request-view-page .rv-rail-fields {
		margin: 0;
		display: flex;
		flex-direction: column;
		gap: 0.75rem;
	}

	/* Client info: labels carry hierarchy (former value look); values quieter (former label look) */
	.request-view-page .rv-rail-field dt {
		margin: 0 0 0.1rem;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.875rem;
		font-weight: 500;
		letter-spacing: -0.01em;
		text-transform: none;
		color: #1e293b;
		line-height: 1.35;
	}

	.request-view-page .rv-rail-field dd {
		margin: 0;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.7rem;
		font-weight: 500;
		letter-spacing: 0.01em;
		color: #64748b;
		line-height: 1.4;
		word-break: break-word;
	}

	.request-view-page .rv-rail-value--emphasis {
		font-weight: 500;
		color: #64748b;
		font-size: 0.7rem;
		letter-spacing: 0.01em;
	}

	.request-view-page .rv-rail-contact-email {
		display: flex;
		align-items: center;
		gap: 0.25rem;
		margin-top: 0.2rem;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.62rem;
		font-weight: 400;
		color: #94a3b8;
		line-height: 1.3;
		word-break: break-all;
	}

	.request-view-page .rv-rail-contact-email .mdi {
		font-size: 0.72rem;
		line-height: 1;
		flex-shrink: 0;
		color: #94a3b8;
	}

	.request-view-page .rv-rail-docs {
		list-style: none;
		margin: 0;
		padding: 0;
		display: flex;
		flex-direction: column;
		gap: 0.35rem;
	}

	.request-view-page .rv-rail-doc-link {
		display: flex;
		align-items: center;
		gap: 0.45rem;
		padding: 0.45rem 0.55rem;
		border-radius: 8px;
		border: 1px solid var(--workflow-border, #e2e8f0);
		background: #f8fafc;
		color: #1e293b;
		text-decoration: none;
		font-size: 0.8125rem;
		font-weight: 500;
		transition: background-color 0.15s ease, border-color 0.15s ease;
	}

	.request-view-page a.rv-rail-doc-link:hover,
	.request-view-page a.rv-rail-doc-link:focus {
		background: #eff6ff;
		border-color: #bfdbfe;
		color: #1e3a8a;
		text-decoration: none;
	}

	.request-view-page .rv-rail-doc-link .mdi:first-child {
		color: var(--workflow-accent, #3b5fc0);
		font-size: 1rem;
	}

	.request-view-page .rv-rail-doc-external {
		margin-left: auto;
		font-size: 0.85rem;
		opacity: 0.55;
	}

	.request-view-page .rv-rail-doc-link.is-unavailable {
		opacity: 0.72;
		cursor: default;
	}

	.request-view-page .rv-rail-doc-muted {
		margin-left: auto;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.7rem;
		font-weight: 500;
		text-transform: none;
		letter-spacing: 0.01em;
		color: var(--workflow-text-muted, #64748b);
	}

	.request-view-page .rv-rail-more-toggle {
		display: flex;
		align-items: center;
		justify-content: space-between;
		width: 100%;
		padding: 0;
		margin: 0;
		border: none;
		background: transparent;
		color: var(--workflow-text-muted, #64748b);
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.75rem;
		font-weight: 500;
		letter-spacing: 0.01em;
		text-transform: none;
		cursor: pointer;
	}

	.request-view-page .rv-rail-more-toggle span {
		display: inline-flex;
		align-items: center;
		gap: 0.4rem;
	}

	.request-view-page .rv-rail-more-toggle:hover,
	.request-view-page .rv-rail-more-toggle:focus {
		color: #334155;
		outline: none;
	}

	.request-view-page .rv-rail-section--more .rv-rail-fields {
		margin-top: 0.65rem;
	}

	.request-view-page .rv-rail-mobile-toggle {
		display: none;
	}

	.request-view-page .rv-rail-head-contact {
		margin: 0.35rem 0 0;
		font-size: 0.8125rem;
		font-weight: 500;
		color: #475569;
		display: inline-flex;
		align-items: center;
		gap: 0.3rem;
	}

	@media (max-width: 991.98px) {
		.request-view-page .rv-console {
			grid-template-columns: 1fr;
		}

		.request-view-page .rv-console-rail {
			position: static;
			max-height: none;
			overflow: visible;
		}

		.request-view-page .rv-rail-mobile-toggle {
			display: flex;
			align-items: center;
			justify-content: space-between;
			width: 100%;
			min-height: var(--touch-min, 44px);
			margin: 0.35rem 0 0;
			padding: 0.55rem 0.75rem;
			border: 1px solid #e2e8f0;
			border-radius: 8px;
			background: #f8fafc;
			color: #334155;
			font-size: 0.8125rem;
			font-weight: 600;
			cursor: pointer;
		}

		.request-view-page .rv-rail-mobile-toggle + .rv-rail-collapsible {
			display: none;
		}

		.request-view-page .rv-rail-mobile-toggle + .rv-rail-collapsible.is-open {
			display: block;
			margin-top: 0.65rem;
		}

		.request-view-page .batch-tabs-panel .batch-nav-tabs {
			flex-wrap: nowrap;
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
			scrollbar-width: thin;
			position: sticky;
			top: 0;
			z-index: 5;
			background: #fff;
		}

		.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link {
			white-space: nowrap;
			min-height: var(--touch-min, 44px);
			flex: 0 0 auto;
		}

		.request-view-page .rv-tests-table-wrap,
		.request-view-page .ls-table-wrap {
			overflow-x: auto;
			-webkit-overflow-scrolling: touch;
			max-width: 100%;
		}

		.request-view-page .rv-tests-table .btn,
		.request-view-page .rv-tests-table .rm-act-btn,
		.request-view-page .rv-tests-table button {
			min-width: var(--touch-min, 44px);
			min-height: var(--touch-min, 44px);
		}

		.request-view-page .rv-rail-doc-link {
			min-height: var(--touch-min, 44px);
		}
	}

	@media (min-width: 992px) {
		.request-view-page .rv-rail-collapsible {
			display: block !important;
		}
	}

	/* Header pill — Slice 1 authorized snapshot (dark burgundy × blue + stars) */
	.request-view-page .rv-header {
		--rv-burgundy: var(--lab-chrome-burgundy, #6d0a0e);
		--rv-blue-accent: var(--lab-chrome-blue-accent, #8ac0ff);
		--rv-blue-soft: var(--lab-chrome-blue-soft, #eff6ff);
		margin-bottom: 1rem;
		/* Above fixed sidebar (z-index 1000) so approve / Actions menus are not covered */
		position: relative;
		z-index: 1100;
	}

	/* Scenario G — current request burgundy×blue pill + white identity; Acc/Sub CTAs + green approve from F */
	.request-view-page .rv-header-bar,
	.request-view-page.workflow-theme .batch-header-bar.rv-header-bar,
	.request-view-page .workflow-board-header .batch-header-bar.rv-header-bar {
		position: relative;
		isolation: isolate;
		overflow: visible;
		padding: 12px 16px !important;
		border-radius: 12px;
		margin-bottom: 0;
		background: linear-gradient(
			135deg,
			color-mix(in srgb, var(--rv-burgundy, #6d0a0e) 85%, var(--rv-blue-soft, #eff6ff)) 0%,
			color-mix(in srgb, var(--rv-burgundy, #6d0a0e) 78%, #f8fafc) 55%,
			color-mix(in srgb, var(--rv-burgundy, #6d0a0e) 70%, var(--rv-blue-soft, #eff6ff)) 100%
		) !important;
		border: 1px solid color-mix(in srgb, var(--rv-burgundy, #6d0a0e) 55%, #dbeafe) !important;
		box-shadow: 0 2px 10px color-mix(in srgb, var(--rv-burgundy, #6d0a0e) 28%, transparent) !important;
		z-index: 1;
	}

	/* Subtle white stars — bottom right of pill */
	.request-view-page .rv-header-bar::after {
		content: '';
		position: absolute;
		right: 0;
		bottom: 0;
		width: min(52%, 22rem);
		height: min(85%, 7.5rem);
		pointer-events: none;
		z-index: 0;
		opacity: 0.55;
		border-radius: 0 0 12px 0;
		overflow: hidden;
		background-image:
			radial-gradient(1.4px 1.4px at 12% 78%, rgba(255, 255, 255, 0.95), transparent 60%),
			radial-gradient(1px 1px at 22% 58%, rgba(255, 255, 255, 0.75), transparent 60%),
			radial-gradient(1.6px 1.6px at 34% 88%, rgba(255, 255, 255, 0.9), transparent 60%),
			radial-gradient(1px 1px at 48% 62%, rgba(255, 255, 255, 0.65), transparent 60%),
			radial-gradient(1.3px 1.3px at 58% 82%, rgba(255, 255, 255, 0.85), transparent 60%),
			radial-gradient(1px 1px at 70% 52%, rgba(255, 255, 255, 0.55), transparent 60%),
			radial-gradient(1.5px 1.5px at 78% 90%, rgba(255, 255, 255, 0.9), transparent 60%),
			radial-gradient(1px 1px at 86% 68%, rgba(255, 255, 255, 0.7), transparent 60%),
			radial-gradient(1.2px 1.2px at 94% 84%, rgba(255, 255, 255, 0.8), transparent 60%),
			radial-gradient(1px 1px at 40% 42%, rgba(255, 255, 255, 0.45), transparent 60%),
			radial-gradient(1.1px 1.1px at 66% 38%, rgba(255, 255, 255, 0.4), transparent 60%);
		background-repeat: no-repeat;
		mask-image: linear-gradient(to top left, #000 15%, transparent 72%);
		-webkit-mask-image: linear-gradient(to top left, #000 15%, transparent 72%);
	}

	.request-view-page .rv-header-top,
	.request-view-page .rv-header-identity,
	.request-view-page .rv-header-actions,
	.request-view-page .batch-header-actions {
		position: relative;
		z-index: 1;
	}

	.request-view-page .rv-header-top {
		display: flex;
		align-items: center;
		justify-content: space-between;
		flex-wrap: wrap;
		gap: 8px;
		padding-bottom: 0;
	}

	.request-view-page .rv-header-identity {
		display: flex;
		flex-wrap: wrap;
		flex-direction: row;
		align-items: center;
		gap: 0.4rem 0.55rem;
		margin-bottom: 0;
	}

	.request-view-page .rv-header-request,
	.request-view-page .request-view-title {
		font-family: var(--ls-font-mono, "IBM Plex Mono", ui-monospace, monospace);
		font-size: 1.05rem;
		font-weight: 600;
		color: #ffffff !important;
		letter-spacing: -0.01em;
		line-height: 1.25;
	}

	.request-view-page .rv-header-form-name,
	.request-view-page .request-view-form-name,
	.request-view-page .request-view-meta .text-muted {
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.875rem;
		font-weight: 400;
		color: rgba(255, 255, 255, 0.92) !important;
		line-height: 1.3;
	}

	.request-view-page .rv-header-sep {
		display: inline-block;
		color: rgba(255, 255, 255, 0.55);
		font-weight: 400;
		line-height: 1;
		padding: 0 0.1rem;
	}

	/* Status pill — glass language of active sidebar item */
	.request-view-page .rv-header-stage,
	.request-view-page .batch-stage-pill {
		margin-left: 0.15rem;
		background: var(--lab-chrome-glass, rgba(255, 255, 255, 0.16)) !important;
		color: #ffffff !important;
		border: 1px solid rgba(255, 255, 255, 0.2) !important;
		backdrop-filter: blur(10px);
		-webkit-backdrop-filter: blur(10px);
		box-shadow:
			inset 0 1px 0 rgba(255, 255, 255, 0.22),
			0 0 0 1px color-mix(in srgb, var(--rv-blue-accent, #8ac0ff) 18%, transparent) !important;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.7rem;
		font-weight: 500;
		letter-spacing: 0.01em;
		border-radius: 999px;
		padding: 0.15rem 0.55rem;
		text-transform: none;
	}

	/* Accredited-style Actions / chip (Scenario F/B) */
	.request-view-page .rv-header-chip-btn,
	.request-view-page .batch-header-actions .rv-header-chip-btn,
	.request-view-page .batch-header-actions .btn-outline-secondary,
	.request-view-page .workflow-board-header .btn-outline-secondary.btn-action-sm {
		background: #ecfdf5 !important;
		border: 1px solid #a7f3d0 !important;
		color: #047857 !important;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-weight: 700;
		font-size: 0.78rem;
		letter-spacing: 0.02em;
		border-radius: 8px;
		backdrop-filter: none;
		-webkit-backdrop-filter: none;
		box-shadow: none;
	}

	.request-view-page .rv-header-chip-btn:hover,
	.request-view-page .rv-header-chip-btn:focus,
	.request-view-page .batch-header-actions .rv-header-chip-btn:hover,
	.request-view-page .batch-header-actions .btn-outline-secondary:hover {
		background: #d1fae5 !important;
		border-color: #6ee7b7 !important;
		color: #047857 !important;
		box-shadow: 0 0 0 3px color-mix(in srgb, #a7f3d0 45%, transparent);
	}

	/* Primary CTA — transparent glass + baby-blue left bar (active sidebar language) */
	.request-view-page .rv-header-primary-btn {
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-weight: 700;
		font-size: 0.8125rem;
		letter-spacing: 0.02em;
		min-height: 34px;
		padding: 0.35rem 0.85rem;
		display: inline-flex;
		align-items: center;
		gap: 6px;
		border-radius: 8px;
		background: var(--lab-chrome-glass, rgba(255, 255, 255, 0.16)) !important;
		border: 1px solid rgba(255, 255, 255, 0.16) !important;
		color: #ffffff !important;
		backdrop-filter: blur(8px);
		-webkit-backdrop-filter: blur(8px);
		box-shadow:
			inset 3px 0 0 0 var(--lab-chrome-blue-accent, #8ac0ff),
			inset 0 1px 0 rgba(255, 255, 255, 0.12) !important;
	}

	.request-view-page .rv-header-primary-btn:hover,
	.request-view-page .rv-header-primary-btn:focus {
		background: color-mix(in srgb, var(--lab-chrome-blue-soft, #eff6ff) 18%, var(--lab-chrome-glass, rgba(255, 255, 255, 0.16))) !important;
		border-color: color-mix(in srgb, var(--lab-chrome-blue-accent, #8ac0ff) 35%, rgba(255, 255, 255, 0.2)) !important;
		color: #ffffff !important;
		box-shadow:
			inset 3px 0 0 0 var(--lab-chrome-blue-accent, #8ac0ff),
			inset 0 1px 0 rgba(255, 255, 255, 0.15),
			0 0 0 3px color-mix(in srgb, var(--lab-chrome-blue-accent, #8ac0ff) 22%, transparent) !important;
	}

	/* Quiet quote docs chip stays Acc green; green approve keeps its own styles */
	.request-view-page .rv-quote-bell__btn:not(.rv-quote-approve__btn) {
		background: #ecfdf5 !important;
		border-color: #a7f3d0 !important;
		color: #047857 !important;
	}

	.request-view-page .rv-quote-bell__btn:not(.rv-quote-approve__btn) .mdi {
		color: #047857 !important;
	}

	.request-view-page .rv-actions-dropdown {
		position: relative;
		flex-shrink: 0;
	}

	.request-view-page .rv-actions-dropdown > .dropdown-menu {
		position: absolute !important;
		top: 100% !important;
		right: 0 !important;
		left: auto !important;
		transform: none !important;
		float: none;
		margin-top: 0.35rem;
		min-width: 15.5rem;
		max-width: 20rem;
		max-height: min(70vh, 520px);
		overflow-y: auto;
		padding: 0.35rem 0;
		border: 1px solid #dbe5f0;
		border-radius: 10px;
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
		z-index: 1050;
		background: #fff;
	}

	.request-view-page .rv-actions-dropdown .dropdown-menu > li {
		list-style: none;
		margin: 0;
		padding: 0;
	}

	.request-view-page .rv-actions-dropdown .btn-action-sm {
		height: 32px;
		padding: 0 14px;
		font-size: 0.82rem;
		border-radius: 6px;
		display: inline-flex;
		align-items: center;
		gap: 5px;
		font-weight: 500;
	}

	.request-view-page .rv-actions-dropdown .dropdown-item {
		display: flex;
		align-items: center;
		width: 100%;
		padding: 0.5rem 0.85rem;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.8rem;
		font-weight: 500;
		line-height: 1.35;
		color: #0369a1 !important;
		border: none;
		border-radius: 0;
		background: transparent !important;
		box-shadow: none !important;
		text-align: left;
		white-space: nowrap;
		text-decoration: none;
	}

	.request-view-page .rv-actions-dropdown .dropdown-item:hover,
	.request-view-page .rv-actions-dropdown .dropdown-item:focus {
		background: var(--ls-blue-soft, #eff6ff) !important;
		color: #1e3a8a !important;
		text-decoration: none;
	}

	.request-view-page .rv-actions-dropdown .dropdown-item:active,
	.request-view-page .rv-actions-dropdown .dropdown-item.active {
		background: var(--ls-blue-soft, #eff6ff) !important;
		color: #1e3a8a !important;
	}

	.request-view-page .rv-actions-dropdown .dropdown-item .mdi {
		flex-shrink: 0;
		width: 1.1rem;
		margin-right: 0.45rem;
		text-align: center;
		color: #0369a1 !important;
		font-size: 1.05rem;
		line-height: 1;
	}

	.request-view-page .rv-actions-dropdown .dropdown-item:hover .mdi,
	.request-view-page .rv-actions-dropdown .dropdown-item:focus .mdi,
	.request-view-page .rv-actions-dropdown .dropdown-item:active .mdi {
		color: #1e3a8a !important;
	}

	/* Scenario G typography for rv-action-item (mirror playground .cp-pill__menu-item) */
	.request-view-page .rv-actions-dropdown .rv-action-item {
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif) !important;
		font-size: 0.8rem !important;
		font-weight: 500 !important;
		line-height: 1.35;
		gap: 0.45rem;
		padding: 0.5rem 0.85rem;
	}

	.request-view-page .rv-actions-dropdown .rv-action-item .mdi {
		width: 1.1rem;
		font-size: 1.05rem !important;
		line-height: 1;
	}

	.request-view-page .rv-actions-dropdown .request-view-actions-form {
		margin: 0;
		padding: 0;
		display: block;
		width: 100%;
	}

	.request-view-page .rv-actions-dropdown .request-view-actions-form .dropdown-item {
		width: 100%;
	}

	.request-view-page .rv-actions-dropdown .dropdown-divider {
		margin: 0.35rem 0;
		border-top: 1px solid #e8eef4;
	}

	/* Kill legacy danger/burgundy item styles inside Actions */
	.request-view-page .rv-actions-dropdown .dropdown-item-danger,
	.request-view-page .rv-actions-dropdown .dropdown-item-danger:hover,
	.request-view-page .rv-actions-dropdown .dropdown-item-danger:focus,
	.request-view-page .rv-actions-dropdown .dropdown-item-danger:active,
	.request-view-page .request-view-actions-menu .dropdown-item-danger,
	.request-view-page .request-view-actions-menu .dropdown-item-danger:hover,
	.request-view-page .request-view-actions-menu .dropdown-item-danger:focus,
	.request-view-page .request-view-actions-menu .dropdown-item-danger:active {
		background: #f1f5f9 !important;
		color: #1e293b !important;
	}

	.request-view-page .rv-actions-dropdown .dropdown-item-danger .mdi,
	.request-view-page .request-view-actions-menu .dropdown-item-danger .mdi {
		color: #64748b !important;
	}

	/* Equal-height flex card grids — compact */
	.request-view-page .rv-section-grid {
		display: flex;
		flex-wrap: wrap;
		align-items: stretch;
		gap: 16px;
		margin-bottom: 1.5rem;
	}

	.request-view-page .rv-section-grid > .rv-card {
		flex: 1 1 240px;
		min-width: 220px;
		max-width: 100%;
	}

	.request-view-page .rv-bottom-grid {
		display: flex;
		flex-wrap: wrap;
		align-items: stretch;
		gap: 16px;
		margin-bottom: 1.5rem;
	}

	.request-view-page .rv-bottom-grid > .rv-card--samples {
		flex: 2 1 420px;
		min-width: 280px;
	}

	.request-view-page .rv-bottom-grid > .rv-card--actions {
		flex: 1 1 240px;
		min-width: 220px;
		max-width: 320px;
	}

	.request-view-page .rv-card {
		display: flex;
		flex-direction: column;
		border-radius: 10px;
		border: 1px solid var(--workflow-border, #e2e8f0);
		overflow: hidden;
		min-height: 100%;
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
		background: #fff;
	}

	.request-view-page .rv-card-body {
		display: flex;
		flex-direction: column;
		flex: 1 1 auto;
		padding: 10px 14px 14px;
		gap: 8px;
	}

	.request-view-page .rv-card-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 8px;
		padding: 10px 14px 0;
	}

	.request-view-page .rv-card-header--plain { padding-bottom: 0; }
	.request-view-page .rv-card-header--actions { padding-top: 12px; }

	.request-view-page .rv-card-tag {
		display: inline-block;
		background: var(--workflow-accent-soft, var(--color-primary-soft, #fdf2f2));
		color: var(--workflow-accent, var(--color-primary, #8B1A1A));
		font-size: 0.68rem;
		font-weight: 700;
		letter-spacing: 0.02em;
		padding: 3px 8px;
		border-radius: 999px;
	}

	.request-view-page .rv-card-header-icon {
		color: rgba(30, 41, 59, 0.4);
		font-size: 1rem;
	}

	.request-view-page .rv-card-title {
		margin: 0;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif) !important;
		font-size: 0.95rem;
		font-weight: 700;
		color: #1e293b;
		line-height: 1.3;
	}

	.request-view-page .rv-card-title--sm { font-size: 0.9rem; }

	.request-view-page .rv-card-subtitle {
		font-size: 0.72rem;
		color: #64748b;
		margin-top: 1px;
	}

	.request-view-page .rv-card--sand,
	.request-view-page .rv-card--sky,
	.request-view-page .rv-card--sage,
	.request-view-page .rv-card--lavender,
	.request-view-page .rv-card--mist,
	.request-view-page .rv-card--white {
		background: #fff;
		border-color: var(--workflow-border, #e2e8f0);
	}

	.request-view-page .rv-card--actions-light {
		background: #fff;
		border-color: var(--workflow-border, #e2e8f0);
		color: #1e293b;
	}

	.request-view-page .rv-field-list {
		list-style: none; margin: 0; padding: 0;
		display: flex; flex-direction: column; gap: 7px; flex: 1 1 auto;
	}
	.request-view-page .rv-field-list--compact { gap: 6px; }
	.request-view-page .rv-field-item { display: flex; align-items: flex-start; gap: 8px; }
	.request-view-page .rv-field-item > .mdi {
		margin-top: 1px; color: rgba(30, 41, 59, 0.5); font-size: 0.95rem; flex-shrink: 0;
	}
	.request-view-page .rv-field-label {
		display: block;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.7rem;
		font-weight: 500;
		text-transform: none;
		letter-spacing: 0.01em;
		color: #64748b;
		margin-bottom: 0.1rem;
	}
	.request-view-page .rv-field-value {
		display: block;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.875rem;
		font-weight: 500;
		color: #1e293b;
		line-height: 1.4;
		word-break: break-word;
	}
	.request-view-page .rv-contact-block {
		margin-top: auto; padding-top: 8px; border-top: 1px solid rgba(30, 41, 59, 0.1);
	}
	.request-view-page .rv-contact-heading {
		display: block; font-size: 0.7rem; font-weight: 700; color: #475569; margin-bottom: 6px;
	}
	.request-view-page .rv-pill-row { display: flex; flex-wrap: wrap; gap: 5px; }
	.request-view-page .rv-pill {
		display: inline-block; background: rgba(255,255,255,0.75); border: 1px solid rgba(30,41,59,0.1);
		border-radius: 999px; padding: 2px 8px; font-size: 0.7rem; font-weight: 600; color: #1e293b;
	}
	.request-view-page .rv-card--white .rv-pill { background: #f1f5f9; }

	.request-view-page .rv-samples-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
	.request-view-page .rv-meta-label {
		font-size: 0.7rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.03em;
	}
	.request-view-page .rv-status-badge {
		display: inline-block; background: var(--workflow-accent-soft, #fdf2f2);
		color: var(--workflow-accent, var(--color-primary)); border: 1px solid var(--color-primary-border-soft, #f0d4d4);
		border-radius: 999px; padding: 2px 8px; font-size: 0.7rem; font-weight: 600;
	}
	.request-view-page .rv-btn-compact {
		height: 28px; padding: 0 10px; font-size: 0.75rem; font-weight: 600; border-radius: 6px;
		display: inline-flex; align-items: center; gap: 4px;
	}
	.request-view-page .rv-sample-table-wrap { overflow-x: auto; flex: 1 1 auto; }
	.request-view-page .rv-sample-table {
		width: 100%;
		border-collapse: collapse;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif) !important;
		font-size: 0.8125rem;
	}
	.request-view-page .rv-sample-table th {
		text-align: left; font-size: 0.68rem; font-weight: 700; text-transform: uppercase;
		letter-spacing: 0.03em; color: #64748b; padding: 6px 8px; border-bottom: 1px solid #e2e8f0; white-space: nowrap;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif) !important;
	}
	.request-view-page .rv-sample-table td {
		padding: 8px; border-bottom: 1px solid #f1f5f9; vertical-align: top; color: #1e293b; font-weight: 500;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif) !important;
	}
	.request-view-page .rv-sample-table tbody tr:last-child td { border-bottom: none; }
	.request-view-page .rv-sample-index { font-weight: 700; color: #94a3b8; width: 2rem; }
	.request-view-page .rv-sample-id { font-weight: 700; color: #1e293b; }
	.request-view-page .rv-test-codes { display: flex; flex-wrap: wrap; gap: 4px; }
	.request-view-page .rv-test-code {
		display: inline-block; background: #f8fafc;
		color: #475569; border: 1px solid #e2e8f0;
		border-radius: 4px; padding: 1px 5px; font-size: 0.62rem; font-weight: 600;
		font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; letter-spacing: 0.01em;
		line-height: 1.35; max-width: 14rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
	}
	.request-view-page .rv-empty-copy { font-size: 0.8125rem; color: #64748b; }
	.request-view-page .rv-samples-footer { margin-top: auto; padding-top: 6px; }

	.request-view-page .rv-actions-body { gap: 8px; }
	.request-view-page .rv-actions-list {
		list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 4px; flex: 1 1 auto;
	}
	.request-view-page .rv-action-form { margin: 0; display: block; width: 100%; }
	.request-view-page .rv-action-btn {
		display: flex; align-items: center; gap: 0.45rem; width: 100%; border-radius: 8px;
		padding: 0.45rem 0.7rem; font-size: 0.8125rem; font-weight: 600; text-align: left;
		border: 1px solid transparent; cursor: pointer; text-decoration: none; min-height: 34px;
		transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
	}
	.request-view-page .rv-action-btn .mdi { font-size: 1rem; flex-shrink: 0; }
	.request-view-page .rv-action-btn--primary,
	.request-view-page .rv-action-btn--accent {
		background: #fff;
		border-color: var(--workflow-accent, var(--color-primary, #8B1A1A));
		color: var(--workflow-accent, var(--color-primary, #8B1A1A));
		justify-content: center;
		padding: 0.55rem 0.85rem;
	}
	.request-view-page .rv-action-btn--primary:hover,
	.request-view-page .rv-action-btn--primary:focus,
	.request-view-page .rv-action-btn--accent:hover,
	.request-view-page .rv-action-btn--accent:focus {
		background: var(--workflow-accent-soft, #fdf2f2);
		border-color: var(--workflow-accent, var(--color-primary, #8B1A1A));
		color: var(--workflow-accent, var(--color-primary, #8B1A1A));
		text-decoration: none;
		filter: none;
	}
	.request-view-page .rv-action-btn--secondary {
		background: #fff; border-color: var(--workflow-border, #e2e8f0); color: #334155;
	}
	.request-view-page .rv-action-btn--secondary:hover,
	.request-view-page .rv-action-btn--secondary:focus {
		background: var(--workflow-accent-soft, #fdf2f2);
		border-color: var(--color-primary-border-soft, #f0d4d4);
		color: var(--workflow-accent, var(--color-primary));
		text-decoration: none;
	}
	.request-view-page .rv-actions-danger { margin-top: auto; padding-top: 8px; border-top: 1px solid #f1f5f9; }
	.request-view-page .rv-action-btn--danger {
		background: #fff; border-color: #fecaca; color: #b91c1c;
	}
	.request-view-page .rv-action-btn--danger:hover,
	.request-view-page .rv-action-btn--danger:focus {
		background: #fef2f2; border-color: #fca5a5; color: #991b1b; text-decoration: none;
	}

	@media (max-width: 991.98px) {
		.request-view-page .rv-bottom-grid > .rv-card--actions { max-width: none; }
	}
	@media (prefers-reduced-motion: reduce) {
		.request-view-page .rv-action-btn { transition: none; }
	}

	/* Header bar — base layout; burgundy gradient applied via workflow-theme */
	.request-view-page .batch-header-bar {
		border-radius: 12px;
		padding: 18px 22px;
		margin-bottom: 1.25rem;
	}

	.request-view-page:not(.workflow-theme) .batch-header-bar {
		background: #fff;
		border: 1px solid var(--workflow-border, #e2e8f0);
		box-shadow: var(--card-shadow, 0 1px 3px 0 rgb(0 0 0 / 0.1));
	}

	.request-view-page .batch-header-top {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		flex-wrap: wrap;
		gap: 12px;
	}

	.request-view-page .batch-title-group {
		display: flex;
		flex-direction: column;
		align-items: flex-start;
		gap: 6px;
	}

	.request-view-page .request-view-title {
		font-size: 1.65rem;
		font-weight: 800;
		color: var(--workflow-text-main, #1e293b);
		margin: 0;
		line-height: 1.2;
		letter-spacing: -0.02em;
	}

	.request-view-page.workflow-theme .request-view-title {
		color: #ffffff !important;
	}

	.request-view-page .request-view-form-name {
		font-size: 0.9rem;
		color: var(--workflow-text-muted, #64748b);
		margin: 0;
		display: flex;
		align-items: center;
		gap: 6px;
	}

	.request-view-page .request-view-meta {
		display: flex;
		align-items: center;
		flex-wrap: wrap;
		gap: 8px;
		margin-top: 2px;
	}

	.request-view-page .request-view-meta .text-muted {
		font-size: 0.85rem;
	}

	.request-view-page .batch-header-actions .btn {
		border-radius: 8px;
		font-weight: 600;
		font-size: 0.82rem;
		padding: 6px 14px;
	}

	.request-view-page .batch-header-actions .btn-outline-secondary:hover,
	.request-view-page .batch-header-actions .btn-outline-secondary:focus,
	.request-view-page .batch-header-actions .btn-outline-secondary:active,
	.request-view-page .batch-header-actions .btn-outline-secondary.show {
		background: #f8fafc;
		border-color: #f8fafc;
		color: var(--color-primary);
		box-shadow: 0 0 0 0.15rem rgba(255, 255, 255, 0.35);
	}

	.request-view-page .batch-header-actions .btn-group {
		position: relative;
	}

	.request-view-page .batch-header-actions .dropdown-menu {
		position: absolute !important;
		top: 100% !important;
		right: 0 !important;
		left: auto !important;
		transform: none !important;
		z-index: 1050;
	}

	.request-view-page .request-view-actions-dropdown .dropdown-toggle::after {
		margin-left: 0.45rem;
		vertical-align: 0.15em;
	}

	.request-view-page .request-view-actions-menu {
		min-width: 15.5rem;
		max-width: 20rem;
		padding: 0.35rem 0;
		margin-top: 0.35rem;
		border: 1px solid var(--color-border);
		border-radius: 10px;
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
		overflow: visible;
	}

	.request-view-page .request-view-actions-menu .dropdown-divider {
		margin: 0.35rem 0;
		border-top-color: #e8eef4;
	}

	.request-view-page .request-view-actions-form {
		margin: 0;
		padding: 0;
		display: block;
		width: 100%;
	}

	.request-view-page .request-view-actions-menu .dropdown-item {
		display: flex;
		align-items: center;
		gap: 0.65rem;
		width: 100%;
		padding: 0.55rem 1rem;
		font-size: 0.8125rem;
		font-weight: 500;
		line-height: 1.35;
		color: #111827;
		border: none;
		background: transparent;
		text-align: left;
		white-space: normal;
	}


	.request-view-page .request-view-actions-menu .dropdown-item > i.mdi {
		flex-shrink: 0;
		width: 1.125rem;
		font-size: 1.05rem;
		line-height: 1;
		text-align: center;
		color: #64748b;
	}

	.request-view-page .request-view-actions-menu .dropdown-item > span {
		flex: 1;
		min-width: 0;
	}

	.request-view-page .request-view-actions-menu .dropdown-item:hover,
	.request-view-page .request-view-actions-menu .dropdown-item:focus {
		background: #f1f5f9 !important;
		color: #1e293b !important;
	}

	.request-view-page .request-view-actions-menu .dropdown-item:active {
		background: #f1f5f9 !important;
		color: #1e293b !important;
	}

	.request-view-page .request-view-actions-menu .dropdown-item-danger {
		color: #334155 !important;
		background: transparent !important;
	}

	.request-view-page .request-view-actions-menu .dropdown-item-danger > i.mdi {
		color: #64748b !important;
	}

	.request-view-page .request-view-actions-menu .dropdown-item-danger:hover,
	.request-view-page .request-view-actions-menu .dropdown-item-danger:focus,
	.request-view-page .request-view-actions-menu .dropdown-item-danger:active {
		background: #f1f5f9 !important;
		color: #1e293b !important;
	}

	.request-view-page .request-view-actions-menu .dropdown-item-danger:hover > i.mdi,
	.request-view-page .request-view-actions-menu .dropdown-item-danger:focus > i.mdi {
		color: #475569 !important;
	}

	.request-view-page .workflow-status-chip--in-review {
		background: #eef2ff;
		color: #3b5fc0;
		border-color: #c7d7fc;
	}

	.request-view-page .workflow-status-chip--submitted {
		background: #ecfeff;
		color: #0e7490;
		border-color: #a5f3fc;
	}

	.request-view-page .workflow-status-chip--approved,
	.request-view-page .workflow-status-chip--complete {
		background: #f0fdf4;
		color: #15803d;
		border-color: #bbf7d0;
	}

	.request-view-page .workflow-status-chip--rejected {
		background: #fff1f2;
		color: #be123c;
		border-color: #fecdd3;
	}

	.request-view-page .priority-chip {
		display: inline-flex;
		align-items: center;
		padding: 4px 10px;
		border-radius: 20px;
		font-weight: 600;
		font-size: 0.75rem;
		border: 1px solid #e2e8f0;
		background: #f8fafc;
		color: #475569;
	}

	.request-view-page .priority-chip--normal {
		background: #ecfeff;
		color: #0e7490;
		border-color: #a5f3fc;
	}

	.request-view-page .priority-chip--high,
	.request-view-page .priority-chip--urgent {
		background: #fffbeb;
		color: #b45309;
		border-color: #fde68a;
	}

	/* Alerts */
	.request-view-page .request-view-alerts .alert {
		border-radius: 10px;
		border: 1px solid rgba(0, 0, 0, 0.06);
	}

	/* Stat cards — first card status text */
	.request-view-page .stat-card .stat-status-label {
		font-size: 0.95rem;
		font-weight: 700;
		color: var(--workflow-text-main, #1e293b);
	}

	/* Tabs */
	.request-view-page .batch-tabs-panel .batch-nav-tabs {
		display: flex;
		flex-wrap: wrap;
		gap: 2px;
		padding: 0 14px;
		border-bottom: 1px solid var(--ls-border, #e2e8f0);
		background: transparent;
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-item {
		margin-bottom: -1px;
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		border: none;
		border-bottom: 2px solid transparent;
		border-radius: 0;
		color: var(--ls-muted, #64748b);
		padding: 10px 12px;
		font-weight: 600;
		font-size: 0.8125rem;
		background: transparent;
		transition: color 0.15s ease, border-color 0.15s ease;
	}

	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link:hover {
		color: #1e3a8a;
		border-bottom-color: #bfdbfe;
	}

	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link.active {
		color: #1e3a8a;
		border-bottom-color: #1e3a8a;
	}

	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link:focus,
	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link.focus {
		color: #1e3a8a;
		border-bottom: 2px solid #bfdbfe;
	}

	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link.active:focus,
	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link.active.focus,
	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link.active:active {
		border-bottom-color: #1e3a8a;
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link:hover {
		color: #1e3a8a;
		border-bottom-color: #bfdbfe;
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link.active {
		color: #1e3a8a;
		border-bottom-color: #1e3a8a;
		background: transparent;
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link:focus,
	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link.focus,
	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link:active {
		outline: none;
		box-shadow: none;
		border: none;
		border-radius: 0;
		background: transparent;
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link:focus,
	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link.focus {
		color: var(--workflow-accent, #3b5fc0);
		border-bottom: 2px solid #cbd5e1;
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link.active:focus,
	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link.active.focus,
	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link.active:active {
		border-bottom-color: var(--workflow-accent, #3b5fc0);
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link .badge {
		font-size: 0.7rem;
		font-weight: 700;
		padding: 2px 7px;
		border-radius: 999px;
		background: #e2e8f0;
		color: #475569;
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link.active .badge {
		background: var(--ls-blue-soft, #eff6ff);
		color: #1e3a8a;
	}

	.request-view-page .batch-tabs-panel .tab-content {
		padding: 14px 16px 16px;
	}

	/* Captured request details panel */
	.request-view-page .captured-details-panel-header {
		flex-direction: column;
		align-items: flex-start;
	}

	.request-view-page .captured-details-panel-subtitle {
		margin: 4px 0 0;
		padding-left: 28px;
		font-size: 0.82rem;
		color: var(--workflow-text-muted, #64748b);
		font-weight: 400;
		line-height: 1.4;
	}

	.request-view-page .captured-details-panel-body {
		background: var(--workflow-bg, #f8fafc);
		padding: 20px 24px 24px;
	}

	.request-view-page .clinical-form-display {
		display: grid;
		grid-template-columns: 1fr;
		gap: 1.25rem;
		width: 100%;
		align-items: start;
		font-family: inherit;
	}

	/* Section cards */
	.request-view-page .clinical-form-display .clinical-section-card {
		background: #fff;
		border: 1px solid var(--color-primary, var(--color-primary));
		border-radius: 12px;
		box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
		overflow: hidden;
		margin-bottom: 0;
		min-width: 0;
		transition: box-shadow 0.2s ease, transform 0.2s ease;
	}

	@media (prefers-reduced-motion: no-preference) {
		.request-view-page .clinical-form-display .clinical-section-card:hover {
			box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
			transform: translateY(-1px);
		}
	}

	.request-view-page .clinical-form-display .clinical-section-header {
		background: #f0f4f8;
		border: none;
		border-bottom: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 12px 12px 0 0;
		padding: 14px 20px;
		width: 100%;
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		text-align: left;
		cursor: pointer;
		transition: background-color 0.2s ease;
	}

	.request-view-page .clinical-form-display .clinical-section-header:hover,
	.request-view-page .clinical-form-display .clinical-section-header:focus {
		background: #e8edf3;
	}

	.request-view-page .clinical-form-display .clinical-section-header:focus-visible {
		outline: 2px solid var(--color-primary, var(--color-primary));
		outline-offset: -2px;
	}

	.request-view-page .clinical-form-display .clinical-section-header-text {
		display: flex;
		flex-direction: column;
		gap: 4px;
		min-width: 0;
		flex: 1;
	}

	.request-view-page .clinical-form-display .clinical-section-title {
		font-size: 1.05rem;
		font-weight: 600;
		color: var(--color-primary, var(--color-primary));
		margin: 0;
		display: flex;
		align-items: center;
		gap: 0.5rem;
	}

	.request-view-page .clinical-form-display .clinical-section-icon {
		color: var(--color-primary, var(--color-primary));
		font-size: 1.1rem;
		flex-shrink: 0;
	}

	.request-view-page .clinical-form-display .clinical-section-description {
		margin: 0 0 0 1.75rem;
		color: var(--color-primary, var(--color-primary));
		opacity: 0.85;
		font-size: 0.82rem;
		line-height: 1.45;
		font-weight: 400;
	}

	.request-view-page .clinical-form-display .clinical-section-chevron {
		color: var(--color-primary, var(--color-primary));
		font-size: 1.35rem;
		flex-shrink: 0;
		transition: transform 0.2s ease;
	}

	.request-view-page .clinical-form-display .clinical-section-toggle[aria-expanded="false"] .clinical-section-chevron {
		transform: rotate(-90deg);
	}

	.request-view-page .clinical-form-display .clinical-section-card:has(.clinical-section-toggle[aria-expanded="false"]) .clinical-section-header {
		border-radius: 12px;
	}

	.request-view-page .clinical-form-display .clinical-section-content {
		border: none;
		border-radius: 0;
		padding: 16px 20px 20px;
		background: #fff;
	}

	/* Field grid */
	.request-view-page .clinical-form-display .clinical-fields-holder {
		width: 100%;
	}

	.request-view-page .clinical-form-display .clinical-fields-grid {
		display: grid;
		gap: 1rem;
		width: 100%;
	}

	.request-view-page .clinical-form-display .clinical-grid-1 {
		grid-template-columns: 1fr;
	}

	.request-view-page .clinical-form-display .clinical-grid-2 {
		grid-template-columns: repeat(2, 1fr);
	}

	.request-view-page .clinical-form-display .clinical-grid-3 {
		grid-template-columns: repeat(3, 1fr);
	}

	@media (max-width: 1200px) {
		.request-view-page .clinical-form-display .clinical-grid-3 {
			grid-template-columns: repeat(2, 1fr);
		}
	}

	@media (max-width: 768px) {
		.request-view-page .clinical-form-display .clinical-grid-2,
		.request-view-page .clinical-form-display .clinical-grid-3 {
			grid-template-columns: 1fr;
		}

		.request-view-page .captured-details-panel-body {
			padding: 16px;
		}

		.request-view-page .clinical-form-display .clinical-section-header,
		.request-view-page .clinical-form-display .clinical-section-content {
			padding-left: 16px;
			padding-right: 16px;
		}
	}

	.request-view-page .clinical-form-display .clinical-field {
		display: flex;
		flex-direction: column;
	}

	.request-view-page .clinical-form-display .clinical-field-label {
		font-size: 0.75rem;
		letter-spacing: normal;
		text-transform: none;
		color: var(--workflow-text-muted, #64748b);
		font-weight: 600;
		margin-bottom: 0.4rem;
	}

	.request-view-page .clinical-form-display .clinical-required {
		color: #ef4444;
		margin-left: 0.25rem;
	}

	.request-view-page .clinical-form-display .clinical-field-value-box {
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 8px;
		padding: 0.75rem 1rem;
		min-height: 2.75rem;
		background: #f1f5f9;
		display: flex;
		align-items: center;
		transition: border-color 0.2s ease;
	}

	.request-view-page .clinical-form-display .clinical-field-value-box--signature,
	.request-view-page .clinical-form-display .clinical-field-value-box--textarea {
		min-height: 5rem;
		align-items: flex-start;
		padding-top: 0.875rem;
	}

	.request-view-page .clinical-form-display .clinical-field-value-box--signature {
		align-items: center;
		justify-content: flex-start;
	}

	.request-view-page .clinical-form-display .clinical-field-value {
		font-size: 0.875rem;
		font-weight: 500;
		color: var(--workflow-text-main, #1e293b);
		word-break: break-word;
		line-height: 1.5;
	}

	.request-view-page .clinical-form-display .clinical-field-value--empty {
		color: #94a3b8;
		font-style: italic;
		font-weight: 400;
	}

	/* Tables */
	.request-view-page .clinical-form-display .clinical-rows-holder {
		margin-top: 0.25rem;
	}

	.request-view-page .clinical-form-display .clinical-table-wrapper {
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 8px;
		overflow-x: auto;
		overflow-y: hidden;
		-webkit-overflow-scrolling: touch;
		max-width: 100%;
		background: #fff;
	}

	.request-view-page .clinical-form-display .clinical-data-table {
		width: max-content;
		min-width: 100%;
		margin: 0;
		border-collapse: collapse;
	}

	.request-view-page .clinical-form-display .clinical-table-header {
		background: #475569;
	}

	.request-view-page .clinical-form-display .clinical-table-th {
		font-size: 0.8rem;
		font-weight: 600;
		color: #fff;
		text-transform: none;
		letter-spacing: normal;
		padding: 12px 14px;
		text-align: left;
		white-space: nowrap;
		border: none;
	}

	.request-view-page .clinical-form-display .clinical-row-index {
		width: 52px;
		text-align: center;
		background: #f8fafc;
		font-weight: 600;
		color: #94a3b8;
		font-size: 0.8rem;
	}

	.request-view-page .clinical-form-display .clinical-table-header .clinical-row-index {
		background: #3d4f63;
		color: #e2e8f0;
	}

	.request-view-page .clinical-form-display .clinical-table-row {
		border-bottom: 1px solid #e2e8f0;
		transition: background-color 0.15s ease;
	}

	.request-view-page .clinical-form-display .clinical-table-row--populated {
		background-color: #ecfdf5;
	}

	.request-view-page .clinical-form-display .clinical-table-row--populated:hover {
		background-color: #d1fae5;
	}

	.request-view-page .clinical-form-display .clinical-table-row:not(.clinical-table-row--populated):hover {
		background-color: #f8fafc;
	}

	.request-view-page .clinical-form-display .clinical-table-row:last-child {
		border-bottom: none;
	}

	.request-view-page .clinical-form-display .clinical-table-td {
		padding: 12px 14px;
		font-size: 0.875rem;
		color: var(--workflow-text-main, #1e293b);
		vertical-align: middle;
		border: none;
	}

	.request-view-page .clinical-form-display .clinical-table-td .clinical-field-value {
		font-size: 0.875rem;
	}

	/* Signature, files, empty state */
	.request-view-page .clinical-form-display .clinical-signature {
		max-width: 140px;
		max-height: 64px;
		border-radius: 4px;
		display: block;
	}

	.request-view-page .clinical-form-display .clinical-file-link {
		display: inline-flex;
		align-items: center;
		gap: 0.5rem;
		color: var(--workflow-accent, var(--color-primary));
		font-weight: 500;
		font-size: 0.875rem;
		text-decoration: none;
		transition: color 0.15s ease;
	}

	.request-view-page .clinical-form-display .clinical-file-link:hover {
		color: #8c1419;
		text-decoration: underline;
	}

	.request-view-page .clinical-form-display .clinical-file-link:focus-visible {
		outline: 2px solid var(--workflow-accent, var(--color-primary));
		outline-offset: 2px;
		border-radius: 4px;
	}

	.request-view-page .clinical-form-display .clinical-empty-state {
		text-align: center;
		padding: 2.5rem 1rem;
		color: #94a3b8;
	}

	.request-view-page .clinical-form-display .clinical-empty-state i {
		font-size: 2rem;
		margin-bottom: 0.5rem;
		opacity: 0.5;
		display: block;
	}

	.request-view-page .clinical-form-display .clinical-empty-state p {
		margin: 0;
		font-size: 0.875rem;
		font-weight: 500;
	}

	/* Notes composer */
	.request-view-page .request-notes-composer {
		background: #f8fafc;
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 10px;
		padding: 16px 18px;
		margin: 0 1.15rem 1.25rem;
	}

	.request-view-page .request-notes-composer h6,
	.request-view-page .request-notes-composer .ls-type-label {
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.8125rem;
		font-weight: 600;
		color: #1e293b;
		margin-bottom: 12px;
	}

	.request-view-page .request-notes-composer .btn-primary {
		border-radius: 8px;
		font-weight: 600;
		background: var(--workflow-accent, var(--color-primary, #8b1e2d));
		border-color: var(--workflow-accent, var(--color-primary, #8b1e2d));
	}

	/* Attachments upload modal — gallery shell */
	.request-view-page .rv-attachment-modal-backdrop {
		position: fixed;
		inset: 0;
		/* Above .rv-header (z-index 1100) so this modal isn't covered by the header pill */
		z-index: 1150;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 1rem;
		background: rgba(15, 23, 42, 0.45);
	}

	.request-view-page .rv-attachment-modal {
		width: min(560px, 100%);
		max-height: min(90vh, 720px);
		overflow: hidden;
		display: flex;
		flex-direction: column;
		background: #fff;
		border: 1px solid var(--ls-border, #e2e8f0);
		border-radius: 14px;
		box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
	}

	.request-view-page .rv-attachment-modal .rv-modal-header {
		background: linear-gradient(
			135deg,
			color-mix(in srgb, var(--workflow-accent, #8b1e2d) 22%, #eff6ff) 0%,
			color-mix(in srgb, var(--workflow-accent, #8b1e2d) 10%, #f8fafc) 100%
		);
		border-bottom: 1px solid color-mix(in srgb, var(--workflow-accent, #8b1e2d) 22%, #dbeafe);
	}

	.request-view-page .rv-attachment-modal .rv-modal-title {
		display: inline-flex;
		align-items: center;
		gap: 0.45rem;
		color: #1e3a8a;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-weight: 600;
	}

	.request-view-page .rv-attachment-modal .rv-modal-body {
		padding: 1rem 1.15rem;
	}

	.request-view-page .rv-attachment-modal .rv-modal-footer {
		display: flex;
		justify-content: flex-end;
		gap: 0.5rem;
		padding: 0.85rem 1.15rem;
		border-top: 1px solid var(--ls-border, #e2e8f0);
		background: #f8fafc;
	}

	.request-view-page .rv-attachment-modal .ls-upload {
		max-width: none;
		border: none;
		padding: 0;
		background: transparent;
	}

	.request-view-page .rv-attachment-modal .ls-upload__drop {
		cursor: pointer;
	}

	.request-view-page .rv-attachment-modal .btn-primary {
		background: var(--workflow-accent, var(--color-primary, #8b1e2d));
		border-color: var(--workflow-accent, var(--color-primary, #8b1e2d));
	}

	.request-view-page .batch-tabs-panel {
		margin-top: 0.25rem;
		margin-bottom: 0;
		flex: 1 1 auto;
		display: flex;
		flex-direction: column;
		min-height: 100%;
		background: #fff;
		border: 1px solid var(--ls-border, #e2e8f0);
		border-radius: 10px;
		box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
		overflow: hidden;
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs {
		display: flex;
		flex-wrap: wrap;
		gap: 2px;
		padding: 0 14px;
		border-bottom: 1px solid var(--ls-border, #e2e8f0);
		background: transparent;
	}

	.request-view-page .batch-tabs-panel .batch-nav-tabs .nav-link {
		display: inline-flex;
		align-items: center;
		gap: 6px;
		border: none;
		border-bottom: 2px solid transparent;
		border-radius: 0;
		color: var(--ls-muted, #64748b);
		padding: 10px 12px;
		font-weight: 600;
		font-size: 0.8125rem;
		background: transparent;
		transition: color 0.15s ease, border-color 0.15s ease;
	}

	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link:hover {
		color: #1e3a8a;
		border-bottom-color: #bfdbfe;
	}

	.request-view-page.workflow-theme .batch-tabs-panel .batch-nav-tabs .nav-link.active {
		color: #1e3a8a;
		border-bottom-color: #1e3a8a;
		background: transparent;
	}

	.request-view-page .batch-tabs-panel .tab-content {
		padding: 14px 16px 16px;
		flex: 1 1 auto;
		background: transparent;
	}

	/* Unify tab panel titles with Sample collection (gallery ink / caption) */
	.request-view-page .batch-tabs-panel .workflow-board-panel-header h5,
	.request-view-page .batch-tabs-panel .tab-pane h5,
	.request-view-page .rv-sample-collection-tab__title {
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 1rem;
		font-weight: 600;
		letter-spacing: -0.015em;
		color: #1e293b;
	}

	.request-view-page .batch-tabs-panel .workflow-board-panel-header,
	.request-view-page .rv-tests-tab-header {
		color: #1e293b;
	}

	.request-view-page .batch-tabs-panel .text-muted,
	.request-view-page .rv-sample-collection-tab__hint {
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: 0.8125rem;
		font-weight: 400;
		color: var(--ls-muted, #64748b);
	}

	.request-view-page .rv-col-actions {
		width: 2.5rem;
		text-align: center;
		white-space: nowrap;
	}

	.request-view-page .rv-icon-btn {
		display: inline-flex;
		align-items: center;
		gap: 3px;
		border: 1px solid #e2e8f0;
		background: #fff;
		color: var(--workflow-accent, var(--color-primary));
		border-radius: 6px;
		padding: 2px 6px;
		font-size: 0.65rem;
		font-weight: 700;
		cursor: pointer;
		line-height: 1;
	}

	.request-view-page .rv-icon-btn--solo {
		padding: 4px 6px;
		font-size: 1rem;
	}

	.request-view-page .rv-icon-btn:hover,
	.request-view-page .rv-icon-btn:focus {
		background: var(--workflow-accent-soft, #fdf2f2);
		border-color: var(--color-primary-border-soft, #f0d4d4);
	}

	.request-view-page .rv-modal-backdrop {
		position: fixed;
		inset: 0;
		/* Above .rv-header (z-index 1100) so these modals aren't covered by the header pill */
		z-index: 1150;
		background: rgba(15, 23, 42, 0.45);
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 1rem;
	}

	.request-view-page .rv-modal {
		background: #fff;
		border-radius: 10px;
		border: 1px solid #e2e8f0;
		box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
		width: min(560px, 100%);
		max-height: min(80vh, 640px);
		overflow: hidden;
		display: flex;
		flex-direction: column;
	}

	.request-view-page .rv-modal-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		padding: 12px 14px;
		border-bottom: 1px solid #e2e8f0;
	}

	.request-view-page .rv-modal-title {
		margin: 0;
		font-size: 0.95rem;
		font-weight: 700;
		color: #1e293b;
	}

	.request-view-page .rv-modal-close {
		border: none;
		background: transparent;
		color: #64748b;
		font-size: 1.15rem;
		line-height: 1;
		padding: 2px;
		cursor: pointer;
	}

	.request-view-page .rv-modal-body {
		padding: 14px;
		overflow: auto;
	}

	.request-view-page .rv-test-codes--modal .rv-test-code {
		max-width: none;
		white-space: normal;
		font-size: 0.7rem;
	}

	.request-view-page .rv-detail-grid {
		margin: 0;
	}

	.request-view-page .rv-detail-row {
		display: grid;
		grid-template-columns: minmax(120px, 38%) 1fr;
		gap: 8px 12px;
		padding: 8px 0;
		border-bottom: 1px solid #f1f5f9;
	}

	.request-view-page .rv-detail-row:last-child {
		border-bottom: none;
	}

	.request-view-page .rv-detail-row dt {
		margin: 0;
		font-size: 0.68rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.03em;
		color: #64748b;
	}

	.request-view-page .rv-detail-row dd {
		margin: 0;
		font-size: 0.8125rem;
		font-weight: 600;
		color: #1e293b;
		word-break: break-word;
	}

	.request-view-page [x-cloak] {
		display: none !important;
	}

	/* Receive / Move to In Review modal — match workflow board compact confirm */
	.request-view-page #receive-sample-modal .modal-content {
		max-height: calc(100vh - 2rem);
	}

	.request-view-page #receive-sample-modal .modal-body {
		padding: 0 1.5rem 1.25rem;
		overflow-y: auto;
		overscroll-behavior: contain;
		min-height: 4rem;
	}

	.request-view-page #receive-sample-modal.receive-sample-modal--compact .modal-body {
		padding: 0 1.25rem 0.75rem;
	}

	.request-view-page #receive-sample-modal .receive-sample-modal-body {
		padding: 0;
	}

	/* Request Info card — batch-details 4-col read-only */
	.request-view-page .rv-request-info-panel {
		margin-bottom: 0.75rem;
	}

	.request-view-page .rv-request-info-toggle {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 12px;
		width: 100%;
		padding: 10px 14px;
		border: none;
		border-radius: 10px 10px 0 0;
		background: transparent;
		cursor: pointer;
		text-align: left;
	}

	.request-view-page .rv-request-info-panel:has(.rv-request-info-toggle[aria-expanded="false"]) .rv-request-info-toggle {
		border-radius: 10px;
	}

	.request-view-page .rv-request-info-toggle:hover,
	.request-view-page .rv-request-info-toggle:focus {
		background: #f8fafc;
	}

	.request-view-page .rv-request-info-toggle:focus-visible {
		outline: 2px solid var(--color-primary, #8B1A1A);
		outline-offset: -2px;
	}

	.request-view-page .rv-request-info-toggle h5 {
		font-size: 0.875rem;
		margin: 0;
		display: inline-flex;
		align-items: center;
		gap: 0.4rem;
		font-weight: 700;
		color: #1e293b;
	}

	.request-view-page .rv-request-info-chevron {
		font-size: 1.25rem;
		color: #64748b;
		flex-shrink: 0;
		line-height: 1;
	}

	.request-view-page .rv-request-info-panel .workflow-board-panel-body {
		padding: 6px 10px 8px;
		border-top: 1px solid var(--workflow-border, #e2e8f0);
	}

	.request-view-page .rv-request-info-grid {
		margin-top: 1rem;
		row-gap: 1rem;
	}

	.request-view-page .rv-request-info-grid .rv-info-field {
		margin-bottom: 0 !important;
	}

	.request-view-page .rv-request-info-grid .rv-info-label,
	.request-view-page .rv-request-info-grid .control-label {
		display: block;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif) !important;
		font-size: 0.75rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.04em;
		color: #0a0a0a;
		margin-bottom: 0.2rem;
	}

	.request-view-page .rv-info-value {
		min-height: 34px;
		height: auto;
		padding: 0.35rem 0.65rem;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif) !important;
		font-size: 0.8125rem !important;
		font-weight: 500 !important;
		line-height: 1.35 !important;
		color: #1e293b;
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 6px;
		display: flex;
		align-items: center;
		white-space: pre-wrap;
		word-break: break-word;
		cursor: default;
		box-shadow: none;
	}

	.request-view-page .rv-info-value--emphasis {
		font-weight: 700 !important;
		color: #0a0a0a;
	}

	.request-view-page .rv-info-remarks {
		min-height: 72px;
		height: auto;
		padding: 0.5rem 0.65rem;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif) !important;
		font-size: 0.8125rem !important;
		font-weight: 500 !important;
		color: #1e293b;
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 6px;
		white-space: pre-wrap;
		word-break: break-word;
		cursor: default;
		line-height: 1.45;
	}

	.request-view-page .rv-header-primary-btn .mdi {
		font-size: 1rem;
		line-height: 1;
		color: #ffffff !important;
	}

	.request-view-page .rv-trf-edit-subtitle {
		font-size: 0.75rem;
		color: var(--workflow-text-muted, #64748b);
		font-weight: 400;
	}

	.request-view-page .rv-trf-edit-steps {
		display: flex;
		flex-wrap: wrap;
		gap: 0.35rem;
		padding: 0.65rem 1rem;
		border-bottom: 1px solid rgba(30, 41, 59, 0.1);
		background: #f8fafc;
	}

	.request-view-page .rv-trf-edit-step {
		display: inline-flex;
		align-items: center;
		gap: 0.4rem;
		padding: 0.35rem 0.7rem;
		border: 1px solid transparent;
		border-radius: 999px;
		background: transparent;
		color: #64748b;
		font-size: 0.75rem;
		font-weight: 600;
		cursor: pointer;
	}

	.request-view-page .rv-trf-edit-step:hover {
		background: #fff;
		border-color: #e2e8f0;
		color: #334155;
	}

	.request-view-page .rv-trf-edit-step.is-active {
		background: #fff;
		border-color: var(--workflow-accent, #3b5fc0);
		color: #1e293b;
		box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
	}

	.request-view-page .rv-trf-edit-step-index {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 1.15rem;
		height: 1.15rem;
		border-radius: 999px;
		background: #e2e8f0;
		color: #475569;
		font-size: 0.65rem;
	}

	.request-view-page .rv-trf-edit-step.is-active .rv-trf-edit-step-index {
		background: var(--workflow-accent, #3b5fc0);
		color: #fff;
	}

	.request-view-page .rv-trf-sample-list {
		display: flex;
		flex-direction: column;
		gap: 0.65rem;
	}

	.request-view-page .rv-trf-sample-card {
		border: 1px solid var(--workflow-border, #e2e8f0);
		border-radius: 10px;
		background: #fff;
		overflow: hidden;
	}

	.request-view-page .rv-trf-sample-card.is-expanded {
		border-color: #bfdbfe;
		box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
	}

	.request-view-page .rv-trf-sample-card-toggle {
		display: flex;
		align-items: center;
		justify-content: space-between;
		width: 100%;
		padding: 0.7rem 0.85rem;
		border: none;
		background: #f8fafc;
		color: #1e293b;
		font-size: 0.8125rem;
		font-weight: 600;
		text-align: left;
		cursor: pointer;
	}

	.request-view-page .rv-trf-sample-card.is-expanded .rv-trf-sample-card-toggle {
		background: #eff6ff;
		border-bottom: 1px solid #dbeafe;
	}

	.request-view-page .rv-trf-sample-card-body {
		padding: 0.85rem 0.85rem 0.35rem;
	}

	.request-view-page .rv-tests-tab-header {
		display: flex;
		align-items: center;
		justify-content: space-between;
		flex-wrap: wrap;
		gap: 8px;
		padding: 10px 14px;
		border-bottom: 1px solid var(--ls-border, #e2e8f0);
		background: #fff;
	}

	.request-view-page .rv-tests-tab-header h5 {
		font-size: 0.95rem;
		font-weight: 700;
		margin: 0;
		color: #1e3a8a;
		display: inline-flex;
		align-items: center;
		gap: 0.35rem;
	}

	.request-view-page .rv-samples-count-badge {
		display: inline-block;
		background: var(--ls-blue-soft, #eff6ff);
		color: #1e3a8a;
		border: 1px solid var(--ls-blue-soft-border, #dbeafe);
		border-radius: 999px;
		padding: 2px 8px;
		font-size: 0.68rem;
		font-weight: 700;
	}

	/* Tests & samples: allow wide columns to scroll horizontally */
	.request-view-page .rv-tests-table-wrap.ls-table-wrap,
	.request-view-page .rv-tests-table-wrap {
		overflow-x: auto;
		overflow-y: hidden;
		max-width: 100%;
		-webkit-overflow-scrolling: touch;
	}

	.request-view-page .rv-tests-table {
		font-size: 0.8125rem;
		width: max-content;
		min-width: 100%;
	}

	.request-view-page .rv-tests-table thead th {
		font-size: 0.68rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.03em;
		color: #64748b;
		padding: 8px 10px;
		white-space: nowrap;
		background: #f8fafc;
	}

	.request-view-page .rv-tests-table tbody td {
		padding: 8px 10px;
		vertical-align: middle;
		font-size: 0.8125rem;
		font-weight: 500;
		color: #1e293b;
	}

	.request-view-page .rv-tests-table .btn-icon {
		width: 28px;
		height: 28px;
		padding: 0;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 6px;
	}

	.request-view-page .rv-sample-description-cell {
		max-width: 220px;
		vertical-align: middle;
	}

	.request-view-page .rv-sample-description-inline {
		font-size: 0.8125rem;
		line-height: 1.4;
		font-weight: 500;
		color: #1e293b;
		word-break: break-word;
	}

	.request-view-page .rv-sample-description-inline p,
	.request-view-page .rv-sample-description-inline div {
		margin: 0;
	}

	.request-view-page .rv-modal--wide {
		width: min(720px, 100%);
	}

	.request-view-page .rv-modal--xl,
	.request-view-page .rv-trf-edit-dialog.rv-modal--xl {
		width: min(1100px, calc(100vw - 7rem));
		max-height: min(90vh, 860px);
	}

	.request-view-page .rv-trf-edit-stage {
		display: flex;
		align-items: center;
		justify-content: center;
		gap: 0.75rem;
		width: min(1240px, 100%);
		max-width: 100%;
	}

	.request-view-page .rv-trf-carousel-nav {
		flex: 0 0 auto;
		width: 2.75rem;
		height: 2.75rem;
		border-radius: 999px;
		border: 1px solid rgba(255, 255, 255, 0.35);
		background: rgba(15, 23, 42, 0.55);
		color: #fff;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		box-shadow: 0 8px 24px rgba(15, 23, 42, 0.28);
		cursor: pointer;
		transition: background-color 0.15s ease, transform 0.15s ease;
	}

	.request-view-page .rv-trf-carousel-nav .mdi {
		font-size: 1.5rem;
		line-height: 1;
	}

	.request-view-page .rv-trf-carousel-nav:hover:not(.is-disabled),
	.request-view-page .rv-trf-carousel-nav:focus:not(.is-disabled) {
		background: rgba(15, 23, 42, 0.78);
		outline: none;
		transform: scale(1.04);
	}

	.request-view-page .rv-trf-carousel-nav.is-disabled {
		opacity: 0.28;
		cursor: default;
		pointer-events: none;
	}

	.request-view-page .rv-trf-edit-dialog {
		flex: 1 1 auto;
		min-width: 0;
	}

	.request-view-page .rv-modal--xl .rv-modal-body,
	.request-view-page .rv-trf-edit-dialog .rv-modal-body {
		max-height: none;
		flex: 1 1 auto;
		overflow-y: auto;
	}

	.request-view-page .rv-trf-edit-section + .rv-trf-edit-section {
		margin-top: 0.35rem;
		padding-top: 0.85rem;
		border-top: 1px solid #eef2f7;
	}

	.request-view-page .rv-trf-edit-section-title {
		margin: 0 0 0.75rem;
		font-size: calc(0.8rem + 4px);
		font-weight: 700;
		letter-spacing: 0.03em;
		text-transform: uppercase;
		color: #1e3a8a;
	}

	.request-view-page .rv-trf-edit-section-hint {
		margin: -0.35rem 0 0.85rem;
		font-size: 0.75rem;
		color: #64748b;
	}

	.request-view-page .rv-trf-edit-step-divider {
		width: 1.25rem;
		height: 1px;
		background: #cbd5e1;
		align-self: center;
	}

	.request-view-page .rv-trf-option-grid,
	.request-view-page .rv-trf-edit-modal .rv-trf-option-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
		gap: 0.45rem;
	}

	.request-view-page .rv-trf-option-grid--2,
	.request-view-page .rv-trf-edit-modal .rv-trf-option-grid--2 {
		grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
	}

	.request-view-page .rv-trf-option-grid--3,
	.request-view-page .rv-trf-edit-modal .rv-trf-option-grid--3 {
		grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
	}

	.request-view-page .rv-trf-option-grid--4,
	.request-view-page .rv-trf-edit-modal .rv-trf-option-grid--4 {
		grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
	}

	.request-view-page .rv-trf-option-grid--compact,
	.request-view-page .rv-trf-edit-modal .rv-trf-option-grid--compact {
		grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
	}

	/* Transport: 2-col, content-sized chips, tight horizontal gap */
	.request-view-page .rv-trf-option-grid--transport,
	.request-view-page .rv-trf-edit-modal .rv-trf-option-grid--transport {
		grid-template-columns: repeat(2, max-content) !important;
		justify-items: start;
		justify-content: start;
		width: max-content;
		max-width: 100%;
		column-gap: 0.3rem;
		row-gap: 0.35rem;
	}

	.request-view-page .rv-trf-option-grid--transport .rv-trf-option-chip,
	.request-view-page .rv-trf-edit-modal .rv-trf-option-grid--transport .rv-trf-option-chip {
		width: auto !important;
		max-width: 9.5rem;
		padding: 0.4rem 0.45rem;
	}

	/* Apparatus: 3-col grid */
	.request-view-page .rv-trf-option-grid--apparatus,
	.request-view-page .rv-trf-edit-modal .rv-trf-option-grid--apparatus {
		grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
		column-gap: 0.35rem;
		row-gap: 0.4rem;
	}

	.request-view-page .rv-trf-option-grid--apparatus .rv-trf-option-chip,
	.request-view-page .rv-trf-edit-modal .rv-trf-option-grid--apparatus .rv-trf-option-chip {
		width: 100%;
		max-width: 100%;
		padding: 0.4rem 0.4rem;
	}

	/* Method: 3-col, little horizontal gap */
	.request-view-page .rv-trf-option-grid--method,
	.request-view-page .rv-trf-edit-modal .rv-trf-option-grid--method {
		grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
		justify-items: stretch;
		column-gap: 0.2rem;
		row-gap: 0.35rem;
	}

	.request-view-page .rv-trf-option-grid--method .rv-trf-option-chip,
	.request-view-page .rv-trf-edit-modal .rv-trf-option-grid--method .rv-trf-option-chip {
		width: 100%;
		max-width: 100%;
		padding: 0.4rem 0.35rem;
		font-size: 0.72rem;
	}

	@media (max-width: 767.98px) {
		.request-view-page .rv-trf-option-grid--2,
		.request-view-page .rv-trf-option-grid--3,
		.request-view-page .rv-trf-option-grid--4,
		.request-view-page .rv-trf-option-grid--apparatus,
		.request-view-page .rv-trf-option-grid--method,
		.request-view-page .rv-trf-edit-modal .rv-trf-option-grid--2,
		.request-view-page .rv-trf-edit-modal .rv-trf-option-grid--3,
		.request-view-page .rv-trf-edit-modal .rv-trf-option-grid--4,
		.request-view-page .rv-trf-edit-modal .rv-trf-option-grid--apparatus,
		.request-view-page .rv-trf-edit-modal .rv-trf-option-grid--method {
			grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
		}

		.request-view-page .rv-trf-option-grid--transport,
		.request-view-page .rv-trf-edit-modal .rv-trf-option-grid--transport {
			grid-template-columns: repeat(2, max-content) !important;
			width: max-content;
			max-width: 100%;
			column-gap: 0.3rem;
		}

		.request-view-page .rv-trf-option-grid--transport .rv-trf-option-chip,
		.request-view-page .rv-trf-edit-modal .rv-trf-option-grid--transport .rv-trf-option-chip {
			width: auto !important;
			max-width: 9.5rem;
		}

		.request-view-page .rv-trf-option-grid--method .rv-trf-option-chip,
		.request-view-page .rv-trf-option-grid--apparatus .rv-trf-option-chip {
			width: 100%;
			max-width: 100%;
		}
	}

	/* Chips: keep native inputs inside the card (override Bootstrap .form-check-input absolute + negative margin) */
	.request-view-page .rv-trf-edit-modal .rv-trf-option-chip,
	.request-view-page .rv-trf-option-chip {
		display: flex !important;
		align-items: center;
		gap: 0.5rem;
		box-sizing: border-box;
		width: 100%;
		margin: 0;
		padding: 0.5rem 0.65rem;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		background: #f8fafc;
		font-size: 0.78rem;
		font-weight: 500;
		color: #334155;
		cursor: pointer;
		line-height: 1.25;
		overflow: hidden;
	}

	.request-view-page .rv-trf-edit-modal .rv-trf-option-chip:hover,
	.request-view-page .rv-trf-option-chip:hover {
		border-color: #bfdbfe;
		background: #eff6ff;
	}

	.request-view-page .rv-trf-edit-modal .rv-trf-option-chip:has(input:checked),
	.request-view-page .rv-trf-option-chip:has(.rv-trf-option-input:checked) {
		border-color: color-mix(in srgb, var(--workflow-accent, #8b1e2d) 45%, #e2e8f0);
		background: color-mix(in srgb, var(--workflow-accent, #8b1e2d) 8%, #ffffff);
	}

	.request-view-page .rv-trf-edit-modal .rv-trf-option-chip input[type="checkbox"],
	.request-view-page .rv-trf-edit-modal .rv-trf-option-chip input[type="radio"],
	.request-view-page .rv-trf-option-chip .rv-trf-option-input,
	.request-view-page .rv-trf-option-chip .form-check-input,
	.request-view-page .rv-trf-edit-modal .rv-trf-option-chip .form-check-input {
		position: static !important;
		float: none !important;
		inset: auto !important;
		left: auto !important;
		top: auto !important;
		margin: 0 !important;
		margin-top: 0 !important;
		margin-left: 0 !important;
		margin-right: 0 !important;
		flex: 0 0 auto;
		width: 1rem !important;
		height: 1rem !important;
		transform: none !important;
		vertical-align: middle;
	}

	.request-view-page .rv-trf-option-chip__label {
		flex: 1 1 auto;
		min-width: 0;
	}

	.request-view-page .rv-trf-edit-modal .rv-trf-catalog-row.rv-trf-catalog-row--type-tests,
	.request-view-page .rv-trf-edit-modal .rv-trf-catalog-row--type-tests {
		grid-template-columns: minmax(0, 0.85fr) minmax(0, 0.85fr);
	}

	.request-view-page .rv-trf-catalog-row {
		display: grid;
		grid-template-columns: repeat(2, minmax(0, 1fr));
		gap: 0.75rem 1rem;
		align-items: start;
	}

	/* Samples step: both columns −15% width; left pad-left +15%; right pad-right +15% */
	.request-view-page .rv-trf-edit-modal .rv-trf-catalog-row {
		grid-template-columns: minmax(0, 0.85fr) minmax(0, 0.85fr);
		column-gap: clamp(1.25rem, 5vw, 2.75rem);
		row-gap: 0.75rem;
	}

	/* Per-sample Sample collection: date | time | location (and further fields) */
	.request-view-page .rv-trf-edit-modal .rv-trf-sample-collection-grid,
	.request-view-page .rv-trf-view-modal .rv-trf-sample-collection-grid {
		display: grid;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		column-gap: clamp(1rem, 3.5vw, 1.75rem);
		row-gap: 0.85rem;
		align-items: start;
	}

	.request-view-page .rv-trf-edit-modal .rv-trf-sample-collection-grid__cell,
	.request-view-page .rv-trf-view-modal .rv-trf-sample-collection-grid__cell {
		min-width: 0;
	}

	.request-view-page .rv-trf-edit-modal .rv-trf-sample-collection-grid__cell--full,
	.request-view-page .rv-trf-view-modal .rv-trf-sample-collection-grid__cell--full {
		grid-column: 1 / -1;
	}

	.request-view-page .rv-trf-edit-modal .rv-trf-sample-collection-grid__cell > .mb-3,
	.request-view-page .rv-trf-view-modal .rv-trf-sample-collection-grid__cell > .mb-3 {
		margin-bottom: 0 !important;
	}

	@media (max-width: 767.98px) {
		.request-view-page .rv-trf-edit-modal .rv-trf-sample-collection-grid,
		.request-view-page .rv-trf-view-modal .rv-trf-sample-collection-grid {
			grid-template-columns: 1fr;
		}
	}

	@media (min-width: 768px) {
		.request-view-page .rv-trf-edit-modal .rv-trf-sample-card-body > .row,
		.request-view-page .rv-trf-view-modal .rv-trf-sample-card-body > .row {
			display: grid;
			grid-template-columns: repeat(3, minmax(0, 1fr));
			column-gap: clamp(1rem, 3.5vw, 1.75rem);
			margin-left: 0;
			margin-right: 0;
			padding-left: 1.265rem;
			padding-right: 1.265rem;
		}

		.request-view-page .rv-trf-edit-modal .rv-trf-sample-card-body > .row > [class*="col-12"],
		.request-view-page .rv-trf-edit-modal .rv-trf-sample-card-body > .row > .col-12,
		.request-view-page .rv-trf-view-modal .rv-trf-sample-card-body > .row > [class*="col-12"],
		.request-view-page .rv-trf-view-modal .rv-trf-sample-card-body > .row > .col-12 {
			grid-column: 1 / -1;
			max-width: none;
			width: auto;
			padding-left: 0;
			padding-right: 0;
		}

		.request-view-page .rv-trf-edit-modal .rv-trf-sample-card-body > .row > .col-md-6,
		.request-view-page .rv-trf-edit-modal .rv-trf-sample-card-body > .row > .col-md-4,
		.request-view-page .rv-trf-view-modal .rv-trf-sample-card-body > .row > .col-md-6,
		.request-view-page .rv-trf-view-modal .rv-trf-sample-card-body > .row > .col-md-4 {
			max-width: none;
			width: auto;
			flex: unset;
			padding-left: 0;
			padding-right: 0;
		}
	}

	@media (max-width: 767.98px) {
		.request-view-page .rv-trf-catalog-row {
			grid-template-columns: 1fr;
		}
	}

	.request-view-page .rv-trf-catalog-row__cell {
		min-width: 0;
		align-self: start;
	}

	.request-view-page .rv-trf-condition-temp-row {
		display: flex;
		flex-wrap: nowrap;
		align-items: flex-end;
		gap: 0.65rem 0.85rem;
	}

	.request-view-page .rv-trf-condition-temp-row__condition {
		flex: 1 1 auto;
		min-width: 0;
	}

	.request-view-page .rv-trf-condition-temp-row__temp {
		flex: 0 0 7.5rem;
		min-width: 6.5rem;
	}

	/* Samples step: category | condition | temp on one horizontal track */
	.request-view-page .rv-trf-category-condition-temp-row {
		display: grid;
		grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr) minmax(8.27rem, 0.42fr);
		align-items: start;
		column-gap: 0.85rem;
		row-gap: 0.75rem;
	}

	.request-view-page .rv-trf-category-condition-temp-row__category,
	.request-view-page .rv-trf-category-condition-temp-row__condition,
	.request-view-page .rv-trf-category-condition-temp-row__temp {
		min-width: 0;
	}

	.request-view-page .rv-trf-category-condition-temp-row__category > .mb-3,
	.request-view-page .rv-trf-category-condition-temp-row__condition > .mb-3,
	.request-view-page .rv-trf-category-condition-temp-row__temp > .mb-3 {
		margin-bottom: 0 !important;
	}

	/* Sample Temp was 7.5rem; +5% ≈ 7.875rem, with a bit more track share */
	.request-view-page .rv-trf-category-condition-temp-row__temp {
		min-width: 7.875rem;
	}

	@media (max-width: 767.98px) {
		.request-view-page .rv-trf-category-condition-temp-row {
			grid-template-columns: 1fr;
		}
	}

	@media (max-width: 575.98px) {
		.request-view-page .rv-trf-condition-temp-row {
			flex-wrap: wrap;
		}
	}

	.request-view-page .rv-trf-view-modal .rv-trf-edit-body {
		min-height: 12rem;
	}

	.request-view-page .rv-trf-edit-body {
		padding: 1rem 1.25rem 1.15rem;
	}

	/* Collection step: real gutters + row spacing (Bootstrap 4 has no row-gap) */
	.request-view-page .rv-trf-edit-modal .rv-trf-collection-grid {
		margin-left: -1.1rem;
		margin-right: -1.1rem;
	}

	.request-view-page .rv-trf-edit-modal .rv-trf-collection-grid > [class*="col-"] {
		padding-left: 1.1rem;
		padding-right: 1.1rem;
		margin-bottom: 1.75rem;
	}

	/*
	 * Shared Collection tracks: date|time|received, location|transport|reason, apparatus|·|method.
	 * Left −15% width + padding-left +15%; right (date received) −30% width + padding-right +15%.
	 */
	.request-view-page .rv-trf-edit-modal .rv-trf-collection-trio {
		display: grid;
		grid-template-columns: minmax(0, 0.68fr) minmax(0, 0.5fr) minmax(0, 0.7fr);
		column-gap: clamp(1.75rem, 7vw, 3.75rem);
		align-items: start;
		margin-bottom: 1.75rem;
		overflow: visible;
	}

	.request-view-page .rv-trf-edit-modal .rv-trf-collection-trio > *,
	.request-view-page .rv-trf-edit-modal .rv-trf-collection-col--left,
	.request-view-page .rv-trf-edit-modal .rv-trf-collection-col--mid,
	.request-view-page .rv-trf-edit-modal .rv-trf-collection-col--right {
		min-width: 0;
		overflow: visible;
	}

	/* Equipment ID near bottom of Collection — open list upward to avoid modal/card clipping. */
	.request-view-page .trf-sampling-equipment-ids {
		overflow: visible;
		position: relative;
		z-index: 30;
	}

	.request-view-page .trf-sampling-equipment-ids .searchable-dropdown-wrapper {
		overflow: visible;
		z-index: 20;
	}

	.request-view-page .trf-sampling-equipment-ids .dropdown-list {
		top: auto;
		bottom: 100%;
		margin-top: 0;
		margin-bottom: 4px;
		z-index: 2060;
	}

	/* Left column: +15% padding-left (base gutter 1.1rem) */
	.request-view-page .rv-trf-edit-modal .rv-trf-collection-col--left {
		padding-left: 1.265rem;
	}

	/* Right column: +15% padding-right — keeps Date received / Reason / Method on one edge */
	.request-view-page .rv-trf-edit-modal .rv-trf-collection-col--right {
		padding-right: 1.265rem;
	}

	.request-view-page .rv-trf-edit-modal .rv-trf-collection-grid > .col-12:has(.rv-trf-collection-trio) {
		margin-bottom: 0;
	}

	@media (max-width: 767.98px) {
		.request-view-page .rv-trf-edit-modal .rv-trf-collection-trio {
			grid-template-columns: 1fr;
			column-gap: 0;
			row-gap: 1.25rem;
		}

		.request-view-page .rv-trf-edit-modal .rv-trf-collection-col--left,
		.request-view-page .rv-trf-edit-modal .rv-trf-collection-col--right {
			padding-left: 0;
			padding-right: 0;
		}

		.request-view-page .rv-trf-edit-modal .trf-ww-collection-row3,
		.request-view-page .rv-trf-edit-modal .trf-ww-collection-row4,
		.request-view-page .rv-trf-edit-modal .trf-ww-apparatus-grid,
		.request-view-page .rv-trf-view-modal .trf-ww-collection-row3,
		.request-view-page .rv-trf-view-modal .trf-ww-collection-row4,
		.request-view-page .rv-trf-view-modal .trf-ww-apparatus-grid {
			grid-template-columns: 1fr;
		}

		.request-view-page .rv-trf-edit-modal .trf-ww-apparatus-grid__cell--chlorine,
		.request-view-page .rv-trf-view-modal .trf-ww-apparatus-grid__cell--chlorine {
			grid-column: auto;
		}

		.request-view-page .rv-trf-edit-modal .trf-ww-field-data-grid,
		.request-view-page .rv-trf-view-modal .trf-ww-field-data-grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}

	/* Waste Water LWS-036 collection layout (edit + view modals) */
	.request-view-page .rv-trf-edit-modal .trf-ww-collection-row3,
	.request-view-page .rv-trf-view-modal .trf-ww-collection-row3 {
		display: grid;
		grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) minmax(0, 3fr);
		column-gap: clamp(1rem, 4vw, 2rem);
		align-items: start;
		margin-bottom: 1.25rem;
	}

	.request-view-page .rv-trf-edit-modal .trf-ww-collection-row4,
	.request-view-page .rv-trf-view-modal .trf-ww-collection-row4 {
		display: grid;
		/* Align with row3: Reason 2fr | Technique+Source 4fr */
		grid-template-columns: minmax(0, 2fr) minmax(0, 4fr);
		column-gap: clamp(1rem, 4vw, 2rem);
		align-items: start;
		margin-bottom: 1.25rem;
	}

	.request-view-page .rv-trf-edit-modal .trf-ww-collection-row4 > *,
	.request-view-page .rv-trf-view-modal .trf-ww-collection-row4 > * {
		min-width: 0;
	}

	.request-view-page .rv-trf-edit-modal .trf-ww-apparatus-block,
	.request-view-page .rv-trf-view-modal .trf-ww-apparatus-block {
		border: 1px solid var(--ls-blue-soft-border, #dbeafe);
		border-radius: var(--ls-radius-md, 0.5rem);
		padding: 0.75rem;
		background: color-mix(in srgb, var(--ls-blue-soft, #eff6ff) 35%, #fff);
	}

	.request-view-page .rv-trf-edit-modal .trf-ww-apparatus-grid,
	.request-view-page .rv-trf-view-modal .trf-ww-apparatus-grid {
		display: grid;
		grid-template-columns: minmax(0, 0.85fr) minmax(0, 0.85fr) minmax(0, 1.15fr) minmax(0, 1.15fr);
		gap: 0.65rem 0.75rem;
		align-items: stretch;
	}

	.request-view-page .rv-trf-edit-modal .trf-ww-apparatus-grid__cell,
	.request-view-page .rv-trf-view-modal .trf-ww-apparatus-grid__cell {
		min-width: 0;
		display: flex;
	}

	.request-view-page .rv-trf-edit-modal .trf-ww-apparatus-grid__cell--option .trf-ww-apparatus-chip,
	.request-view-page .rv-trf-view-modal .trf-ww-apparatus-grid__cell--option .trf-ww-apparatus-chip {
		width: auto;
		max-width: 100%;
	}

	.request-view-page .rv-trf-edit-modal .trf-ww-apparatus-grid__cell--chlorine,
	.request-view-page .rv-trf-view-modal .trf-ww-apparatus-grid__cell--chlorine {
		grid-column: span 2;
		max-width: 85%;
	}

	.request-view-page .rv-trf-edit-modal .trf-ww-apparatus-chip,
	.request-view-page .rv-trf-view-modal .trf-ww-apparatus-chip {
		width: 100%;
		justify-content: flex-start;
		min-height: 100%;
	}

	.request-view-page .rv-trf-edit-modal .trf-ww-apparatus-id-chip,
	.request-view-page .rv-trf-view-modal .trf-ww-apparatus-id-chip {
		cursor: default;
		gap: 0.35rem;
		padding: 0.4rem 0.5rem;
		overflow: visible;
	}

	.request-view-page .rv-trf-edit-modal .trf-ww-apparatus-id-chip:hover,
	.request-view-page .rv-trf-view-modal .trf-ww-apparatus-id-chip:hover {
		border-color: #e2e8f0;
		background: #f8fafc;
	}

	.request-view-page .rv-trf-edit-modal .trf-ww-apparatus-id-chip .rv-trf-option-chip__label,
	.request-view-page .rv-trf-view-modal .trf-ww-apparatus-id-chip .rv-trf-option-chip__label {
		flex: 0 0 auto;
		min-width: max-content;
		white-space: nowrap;
		font-size: 0.7rem;
		line-height: 1.2;
	}

	.request-view-page .rv-trf-edit-modal .trf-ww-apparatus-id-chip__input,
	.request-view-page .rv-trf-edit-modal .trf-ww-apparatus-id-chip .form-control,
	.request-view-page .rv-trf-view-modal .trf-ww-apparatus-id-chip__value {
		flex: 1 1 3rem;
		min-width: 2.75rem;
		max-width: 100%;
		width: auto !important;
		height: 1.55rem;
		min-height: 1.55rem;
		padding: 0.1rem 0.35rem;
		font-size: 0.72rem;
		line-height: 1.2;
		border-radius: 6px;
	}

	.request-view-page .rv-trf-edit-modal .trf-ww-apparatus-grid__cell--chlorine .trf-ww-apparatus-id-chip__input,
	.request-view-page .rv-trf-view-modal .trf-ww-apparatus-grid__cell--chlorine .trf-ww-apparatus-id-chip__value {
		flex: 1 1 4.25rem;
		min-width: 3.8rem;
	}

	.request-view-page .rv-trf-view-modal .trf-ww-apparatus-id-chip__value {
		display: inline-flex;
		align-items: center;
		border: 1px solid #e2e8f0;
		background: #fff;
		color: #334155;
	}

	.request-view-page .rv-trf-edit-modal .trf-option-grid--ww-apparatus-4,
	.request-view-page .rv-trf-view-modal .trf-option-grid--ww-apparatus-4,
	.request-view-page .rv-trf-edit-modal .rv-trf-option-grid.trf-option-grid--ww-apparatus-4,
	.request-view-page .rv-trf-view-modal .rv-trf-option-grid.trf-option-grid--ww-apparatus-4 {
		grid-template-columns: repeat(4, minmax(0, 1fr));
	}

	.request-view-page .rv-trf-edit-modal .trf-option-grid--ww-apparatus-4 .rv-trf-option-chip,
	.request-view-page .rv-trf-view-modal .trf-option-grid--ww-apparatus-4 .rv-trf-option-chip {
		width: auto;
		max-width: 100%;
	}

	.request-view-page .rv-trf-edit-modal .trf-ww-field-data-grid,
	.request-view-page .rv-trf-view-modal .trf-ww-field-data-grid {
		display: grid;
		grid-template-columns: repeat(4, minmax(0, 1fr));
		gap: 0.75rem 1rem;
	}

	.request-view-page .rv-trf-edit-modal .trf-ww-extra-equipment-row,
	.request-view-page .rv-trf-view-modal .trf-ww-extra-equipment-row {
		display: grid;
		grid-template-columns: minmax(0, 1fr) minmax(0, 1fr) auto;
		gap: 0.4rem;
		margin-bottom: 0.45rem;
		align-items: center;
	}

	.request-view-page .rv-trf-edit-modal .trf-sampling-equipment-ids .trf-ww-extra-equipment-row,
	.request-view-page .rv-trf-view-modal .trf-sampling-equipment-ids .trf-ww-extra-equipment-row {
		grid-template-columns: minmax(0, 1fr) auto;
	}

	.request-view-page .rv-trf-edit-modal .trf-option-grid--ww-reason,
	.request-view-page .rv-trf-view-modal .trf-option-grid--ww-reason {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}

	.request-view-page .rv-trf-edit-modal .trf-option-grid--ww-technique,
	.request-view-page .rv-trf-edit-modal .trf-option-grid--ww-test-category,
	.request-view-page .rv-trf-edit-modal .trf-option-grid--ww-transport,
	.request-view-page .rv-trf-view-modal .trf-option-grid--ww-technique,
	.request-view-page .rv-trf-view-modal .trf-option-grid--ww-test-category,
	.request-view-page .rv-trf-view-modal .trf-option-grid--ww-transport {
		grid-template-columns: 1fr;
	}

	.request-view-page .rv-trf-edit-modal .trf-option-grid--ww-source,
	.request-view-page .rv-trf-view-modal .trf-option-grid--ww-source {
		grid-template-columns: repeat(3, minmax(0, 1fr));
	}

	.request-view-page .rv-trf-edit-modal .trf-option-grid--ww-sample-types,
	.request-view-page .rv-trf-view-modal .trf-option-grid--ww-sample-types {
		grid-template-columns: repeat(2, minmax(0, 1fr));
	}

	.request-view-page .rv-trf-edit-modal .rv-trf-collection-grid .form-control,
	.request-view-page .rv-trf-edit-modal .rv-trf-collection-grid .form-control-sm,
	.request-view-page .rv-trf-edit-modal .rv-trf-collection-grid .select2-container--default .select2-selection--single .select2-selection__rendered {
		font-size: 0.75rem !important;
		font-weight: 500 !important;
		line-height: 1.3 !important;
	}

	.request-view-page .rv-trf-edit-modal .rv-trf-collection-grid .form-control,
	.request-view-page .rv-trf-edit-modal .rv-trf-collection-grid .form-control-sm {
		min-height: 2rem !important;
		height: 2rem;
		padding-top: 0.25rem;
		padding-bottom: 0.25rem;
	}

	.request-view-page .rv-trf-edit-modal .rv-trf-collection-grid .select2-container--default .select2-selection--single {
		min-height: 2rem !important;
		height: 2rem !important;
	}

	.request-view-page .rv-trf-edit-modal .rv-trf-collection-grid .select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 1.9rem !important;
		padding-left: 0.5rem !important;
	}

	.request-view-page .rv-trf-edit-modal .rv-trf-collection-grid .select2-container--default .select2-selection--single .select2-selection__arrow {
		height: 1.9rem !important;
	}

	/* Catalog Select2 — grow with tags only (do NOT force search field to 100% width) */
	.request-view-page .rv-trf-select-shell {
		position: relative;
		padding: 0.2rem;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		background: #f8fafc;
	}

	/* Sample type / Analysis type: strip shell chrome so height = chips only */
	.request-view-page .rv-trf-catalog-row .rv-trf-select-shell {
		padding: 0;
		border: none;
		border-radius: 0;
		background: transparent;
		box-shadow: none;
	}

	.request-view-page .rv-trf-catalog-row .rv-trf-select-shell .select2-container,
	.request-view-page .rv-trf-catalog-row .rv-trf-select-shell .select2-container .selection,
	.request-view-page .rv-trf-catalog-row .rv-trf-select-shell .select2-container .select2-selection--multiple {
		height: auto !important;
		min-height: 0 !important;
		max-height: none !important;
	}

	.request-view-page .rv-trf-catalog-row .rv-trf-select-shell .select2-container--default .select2-selection--multiple {
		min-height: 2rem !important;
		height: auto !important;
		padding: 3px 6px !important;
	}

	.request-view-page .rv-trf-catalog-row .rv-trf-select-shell select.livewire-select2 {
		min-height: 0 !important;
		height: 0 !important;
	}

	/* Multi-select: search lives in the dropdown, not in the chips box */
	.request-view-page .rv-trf-edit-modal .select2-selection--multiple .select2-search--inline,
	.request-view-page .rv-sample-row-edit-modal .select2-selection--multiple .select2-search--inline {
		display: none !important;
		width: 0 !important;
		height: 0 !important;
		min-height: 0 !important;
		margin: 0 !important;
		padding: 0 !important;
		overflow: hidden !important;
	}

	.request-view-page .rv-trf-edit-modal .select2-dropdown .select2-search--dropdown,
	.request-view-page .rv-sample-row-edit-modal .select2-dropdown .select2-search--dropdown {
		display: block !important;
		padding: 0.45rem 0.5rem;
		border-bottom: 1px solid #e2e8f0;
	}

	.request-view-page .rv-trf-edit-modal .select2-dropdown .select2-search--dropdown .select2-search__field,
	.request-view-page .rv-sample-row-edit-modal .select2-dropdown .select2-search--dropdown .select2-search__field {
		width: 100% !important;
		min-height: 2rem !important;
		height: 2rem !important;
		margin: 0 !important;
		padding: 0.25rem 0.5rem !important;
		border: 1px solid #cbd5e1 !important;
		border-radius: 6px !important;
		font-size: 0.75rem !important;
		line-height: 1.3 !important;
		box-sizing: border-box;
	}

	.request-view-page .rv-trf-catalog-row .rv-trf-select-shell .select2-selection--multiple .select2-search--inline,
	.request-view-page .rv-trf-catalog-row .rv-trf-select-shell .select2-selection--multiple li.select2-search {
		display: none !important;
	}

	.request-view-page .rv-trf-select-shell .select2-container {
		width: 100% !important;
		height: auto !important;
	}

	.request-view-page .rv-trf-select-shell .select2-container .selection,
	.request-view-page .rv-trf-select-shell .select2-container .select2-selection--multiple {
		height: auto !important;
		min-height: 0 !important;
	}

	.request-view-page .rv-trf-select-shell .select2-container--default .select2-selection--multiple,
	.request-view-page .rv-trf-select-shell .select2-container--default .select2-selection--single {
		min-height: 2rem !important;
		height: auto !important;
		border: 1px solid #cbd5e1 !important;
		border-radius: 6px !important;
		background: #ffffff !important;
		padding: 2px 4px !important;
		box-shadow: none !important;
	}

	.request-view-page .rv-trf-select-shell .select2-container--default .select2-selection--multiple .select2-selection__rendered {
		display: flex !important;
		flex-wrap: wrap !important;
		align-items: center !important;
		align-content: flex-start !important;
		gap: 4px !important;
		padding: 0 !important;
		margin: 0 !important;
		min-height: 0 !important;
		height: auto !important;
	}

	.request-view-page .rv-trf-select-shell .select2-container--default .select2-selection--multiple .select2-selection__choice {
		display: inline-flex !important;
		align-items: center;
		gap: 0.15rem;
		margin: 0 !important;
		padding: 0.15rem 0.45rem 0.15rem 0.35rem !important;
		border: 1px solid color-mix(in srgb, var(--workflow-accent, #8b1e2d) 28%, #e2e8f0) !important;
		border-radius: 999px !important;
		background: color-mix(in srgb, var(--workflow-accent, #8b1e2d) 12%, #ffffff) !important;
		color: var(--workflow-accent, #8b1e2d) !important;
		font-size: 0.7rem !important;
		font-weight: 600;
		line-height: 1.25;
		max-width: 100%;
	}

	.request-view-page .rv-trf-select-shell .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
		color: color-mix(in srgb, var(--workflow-accent, #8b1e2d) 70%, #64748b) !important;
		margin-right: 0.1rem;
		border: none !important;
		font-weight: 700;
	}

	.request-view-page .rv-trf-select-shell .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
		color: var(--workflow-accent, #8b1e2d) !important;
		background: transparent !important;
	}

	.request-view-page .rv-trf-select-shell .select2-container--default .select2-selection--multiple .select2-search--inline {
		float: none !important;
		display: inline-flex !important;
		flex: 0 0 auto !important;
		width: auto !important;
		max-width: 7rem !important;
		margin: 0 !important;
		padding: 0 !important;
		height: auto !important;
	}

	/* Critical: Select2 sets inline width:100% when placeholder is present — override it */
	.request-view-page .rv-trf-select-shell .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field,
	.request-view-page .rv-trf-edit-modal .select2-selection--multiple .select2-search--inline .select2-search__field {
		width: 5rem !important;
		min-width: 5rem !important;
		max-width: 7rem !important;
		height: 1.4rem !important;
		min-height: 1.4rem !important;
		max-height: 1.4rem !important;
		margin: 0 !important;
		padding: 0 !important;
		line-height: 1.4rem !important;
		font-size: 0.75rem !important;
		resize: none !important;
		overflow: hidden !important;
		color: #64748b;
	}

	.request-view-page .rv-trf-select-shell .select2-container--default.select2-container--focus .select2-selection--multiple,
	.request-view-page .rv-trf-select-shell .select2-container--default.select2-container--open .select2-selection--multiple,
	.request-view-page .rv-trf-select-shell .select2-container--default.select2-container--focus .select2-selection--single,
	.request-view-page .rv-trf-select-shell .select2-container--default.select2-container--open .select2-selection--single {
		border-color: color-mix(in srgb, var(--workflow-accent, #8b1e2d) 55%, #cbd5e1) !important;
		box-shadow: 0 0 0 3px color-mix(in srgb, var(--workflow-accent, #8b1e2d) 14%, transparent) !important;
	}

	.request-view-page .rv-trf-select-shell .select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 1.75rem !important;
		padding-left: 2px !important;
		padding-right: 1.5rem !important;
		font-size: 0.75rem;
		color: #334155;
	}

	.request-view-page .rv-trf-select-shell .select2-container--default .select2-selection--single .select2-selection__arrow {
		height: 1.85rem !important;
		right: 4px;
	}

	.request-view-page .rv-trf-select-shell .select2-container--default .select2-selection--single .select2-selection__clear {
		margin-right: 1.25rem;
	}

	.request-view-page .rv-sample-row-select2-wrap:not(.rv-trf-select-shell) .select2-container--default .select2-selection--multiple {
		min-height: 2rem;
		height: auto;
		border: 1px solid #ced4da;
		border-radius: 6px;
		padding: 2px 6px;
	}

	.request-view-page .rv-sample-row-select2-wrap:not(.rv-trf-select-shell) .select2-container--default .select2-selection--multiple .select2-selection__rendered {
		display: flex;
		flex-wrap: wrap;
		gap: 4px;
		padding: 0;
	}

	.request-view-page .rv-sample-row-select2-wrap:not(.rv-trf-select-shell) .select2-container--default .select2-selection--multiple .select2-selection__choice {
		margin: 0;
		padding: 1px 6px;
		font-size: 0.75rem;
		line-height: 1.35;
		border-radius: 4px;
	}

	.request-view-page .rv-sample-row-select2-wrap:not(.rv-trf-select-shell) .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
		margin-top: 2px;
		height: 22px !important;
		min-height: 22px !important;
		max-height: 22px !important;
		width: 5rem !important;
		max-width: 7rem !important;
	}

	.request-view-page .rv-sample-row-select2-wrap:not(.rv-trf-select-shell) .select2-container--default .select2-selection--single {
		height: 2rem;
		min-height: 2rem;
		border: 1px solid #ced4da;
		border-radius: 6px;
	}

	.request-view-page .rv-sample-row-select2-wrap:not(.rv-trf-select-shell) .select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 1.9rem;
		padding-left: 8px;
		font-size: 0.75rem;
	}

	.request-view-page .rv-sample-row-select2-wrap:not(.rv-trf-select-shell) .select2-container--default .select2-selection--single .select2-selection__arrow {
		height: 1.9rem;
	}

	.request-view-page .rv-trf-edit-footer {
		display: grid;
		grid-template-columns: 1fr auto 1fr;
		align-items: center;
		gap: 0.75rem;
		padding: 0.75rem 1rem;
		border-top: 1px solid rgba(30, 41, 59, 0.12);
	}

	.request-view-page .rv-trf-edit-footer-left {
		justify-self: start;
	}

	.request-view-page .rv-trf-edit-footer-right {
		justify-self: end;
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
	}

	.request-view-page .rv-trf-edit-footer-dots {
		display: inline-flex;
		align-items: center;
		gap: 0.35rem;
	}

	.request-view-page .rv-trf-edit-dot {
		width: 0.45rem;
		height: 0.45rem;
		border-radius: 999px;
		background: #cbd5e1;
	}

	.request-view-page .rv-trf-edit-dot.is-active {
		width: 1.1rem;
		background: var(--workflow-accent, #3b5fc0);
	}

	@media (max-width: 991.98px) {
		.request-view-page .rv-trf-edit-stage {
			gap: 0.35rem;
			width: 100%;
		}

		.request-view-page .rv-modal--xl,
		.request-view-page .rv-trf-edit-dialog.rv-modal--xl {
			width: min(100%, calc(100vw - 4.5rem));
		}

		.request-view-page .rv-trf-carousel-nav {
			width: 2.25rem;
			height: 2.25rem;
		}

		.request-view-page .rv-trf-edit-footer {
			grid-template-columns: 1fr;
			justify-items: stretch;
		}

		.request-view-page .rv-trf-edit-footer-left,
		.request-view-page .rv-trf-edit-footer-right,
		.request-view-page .rv-trf-edit-footer-dots {
			justify-self: stretch;
		}

		.request-view-page .rv-trf-edit-footer-right {
			justify-content: flex-end;
		}

		.request-view-page .rv-trf-edit-footer-dots {
			justify-content: center;
			order: -1;
		}
	}

	/* Ensure TRF edit/view modals always show LS field chrome even if tokens miss. */
	.request-view-page .rv-trf-edit-modal .ls-field__control,
	.request-view-page .rv-trf-view-modal .ls-field__control {
		border: 1px solid #e2e8f0 !important;
		border-radius: 8px;
		background: #fff;
		min-height: 38px;
	}

	.request-view-page .rv-trf-edit-modal .ls-field.is-success .ls-field__control,
	.request-view-page .rv-trf-view-modal .ls-field.is-success .ls-field__control,
	.request-view-page .rv-trf-edit-modal .ls-search-basic.is-success .ls-field__control,
	.request-view-page .rv-trf-edit-modal .ls-combo.is-success .ls-field__control {
		border-color: var(--ls-blue-focus, #93c5fd) !important;
		background: var(--ls-blue-soft, #eff6ff);
	}

	.request-view-page .rv-trf-edit-modal .ls-field.is-success .ls-field__icon-btn .mdi-check-circle,
	.request-view-page .rv-trf-edit-modal .ls-search-basic.is-success .mdi-check-circle {
		color: var(--ls-blue-focus, #93c5fd) !important;
	}

	.request-view-page .rv-trf-edit-modal .ls-field.is-disabled .ls-field__control,
	.request-view-page .rv-trf-view-modal .ls-field.is-disabled .ls-field__control {
		background: #f1f5f9;
	}

	.request-view-page .rv-trf-edit-modal .ls-search-basic .ls-field__control,
	.request-view-page .rv-trf-edit-modal .ls-combo .ls-field__control {
		border: 1px solid #e2e8f0 !important;
	}

	.request-view-page .rv-sample-row-edit-dialog {
		position: relative;
		overflow: hidden;
	}

	.request-view-page .rv-sample-row-edit-dialog:not(.is-ready) .rv-modal-header,
	.request-view-page .rv-sample-row-edit-dialog:not(.is-ready) .rv-modal-body,
	.request-view-page .rv-sample-row-edit-dialog:not(.is-ready) .rv-modal-footer {
		visibility: hidden;
	}

	/* View modal has no Select2 prep — never hide its chrome. */
	.request-view-page .rv-trf-view-modal .rv-sample-row-edit-dialog .rv-modal-header,
	.request-view-page .rv-trf-view-modal .rv-sample-row-edit-dialog .rv-modal-body,
	.request-view-page .rv-trf-view-modal .rv-sample-row-edit-dialog .rv-modal-footer {
		visibility: visible !important;
	}

	.request-view-page .rv-sample-row-edit-loading {
		position: absolute;
		inset: 0;
		display: flex;
		align-items: center;
		justify-content: center;
		background: rgba(255, 255, 255, 0.92);
		z-index: 2;
	}

	.request-view-page .rv-sample-row-edit-dialog.is-ready .rv-sample-row-edit-loading {
		display: none;
	}

	.request-view-page .rv-sample-row-select2-wrap .select2-container {
		width: 100% !important;
	}

	.request-view-page .rv-sample-row-edit-dialog .rv-modal-body {
		overflow-x: hidden;
	}

	.request-view-page .rv-qty-unit-wrap .form-control-sm {
		min-width: 0;
	}

	.request-view-page .rv-qty-unit-control {
		display: flex;
		align-items: stretch;
		gap: 0;
		padding: 0;
		overflow: visible;
	}

	.request-view-page .rv-qty-unit-control > .ls-field__input {
		flex: 1 1 auto;
		min-width: 0;
		border: 0 !important;
		border-radius: 0;
		box-shadow: none !important;
	}

	.request-view-page .rv-qty-unit-select2 {
		flex: 0 0 7.5rem;
		max-width: 42%;
		display: flex;
		align-items: stretch;
		padding: 0 !important;
		border-left: 1px solid var(--ls-border, #e2e8f0);
		background: #f8fafc;
	}

	.request-view-page .rv-qty-unit-select2 .select2-container {
		width: 100% !important;
		align-self: stretch;
	}

	.request-view-page .rv-qty-unit-select2 .select2-container--default .select2-selection--single {
		height: 100% !important;
		min-height: 32px !important;
		border: 0 !important;
		border-radius: 0 !important;
		background: transparent !important;
		display: flex;
		align-items: center;
	}

	.request-view-page .rv-qty-unit-select2 .select2-container--default .select2-selection--single .select2-selection__rendered {
		line-height: 1.2 !important;
		padding-left: 0.45rem !important;
		padding-right: 1.4rem !important;
		font-size: 0.78rem !important;
		color: var(--ls-ink, #1e293b) !important;
	}

	.request-view-page .rv-qty-unit-select2 .select2-container--default .select2-selection--single .select2-selection__arrow {
		height: 100% !important;
		top: 0 !important;
		right: 0.15rem !important;
	}

	.request-view-page .rv-trf-edit-modal .select2-dropdown.ls-select2-dropdown-search {
		min-width: 12rem;
	}

	.request-view-page .rv-test-requirements-checkboxes .form-check-label {
		font-size: 0.8125rem;
	}

	.request-view-page .rv-param-group-title {
		font-size: 0.8rem;
		font-weight: 700;
		color: #334155;
		margin-bottom: 6px;
	}

	.request-view-page .rv-param-table {
		font-size: 0.8125rem;
	}

	.request-view-page .rv-param-table thead th {
		font-size: 0.68rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0.03em;
		color: #64748b;
		background: #f8fafc;
		padding: 6px 8px;
	}

	.request-view-page .rv-param-table td {
		padding: 6px 8px;
	}

	.request-view-page .rv-richtext-readonly {
		font-size: 0.8125rem;
		line-height: 1.5;
		color: #1e293b;
		min-height: 80px;
		padding: 10px 12px;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		background: #f8fafc;
	}

	.request-view-page .rv-richtext-readonly p:last-child {
		margin-bottom: 0;
	}

	.request-view-page .batch-tabs-panel .tab-content {
		padding: 0;
		flex: 1 1 auto;
		display: flex;
		flex-direction: column;
		background: transparent;
	}

	.request-view-page .batch-tabs-panel .tab-content > .rv-tests-tab,
	.request-view-page .batch-tabs-panel .tab-content > .tab-pane,
	.request-view-page .batch-tabs-panel .tab-content > div {
		flex: 1 1 auto;
	}

	.request-view-page .batch-tabs-panel .tab-content > .rv-tests-tab {
		padding: 0;
	}

	.request-view-page .batch-tabs-panel .tab-pane-pad {
		padding: 14px 16px 16px;
	}

	.request-view-page .rft-additional-details__row {
		display: grid;
		grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.4fr) auto;
		gap: 0.5rem;
		align-items: center;
	}

	.request-view-page .rft-additional-details .rft-sample-section-label {
		margin: 0;
		padding: 0;
		border: none;
	}

</style>
