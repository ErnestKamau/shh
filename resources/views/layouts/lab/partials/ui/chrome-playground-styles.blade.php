{{-- Chrome playground — Scenarios A–D (pill + sidebar variants) --}}
<style>
	.cp-page {
		--cp-burgundy: #6d0a0e;
		--cp-blue-accent: #8ac0ff;
		--cp-blue-soft: #eff6ff;
		--cp-sidebar-bg: color-mix(in srgb, #6d0a0e 90%, #0f172a);
		--cp-sidebar-wash: 12%;
		--cp-glass: rgba(255, 255, 255, 0.16);
		--cp-rail-ink: #1e3a8a;
		--cp-pill-0: color-mix(in srgb, #6d0a0e 92%, #1a0506);
		--cp-pill-1: color-mix(in srgb, #6d0a0e 88%, #3b0a0e);
		--cp-pill-2: color-mix(in srgb, #6d0a0e 82%, #eff6ff);
		--cp-pill-border: color-mix(in srgb, #6d0a0e 70%, #7f1d1d);
		--cp-stars-opacity: 0.55;
		--cp-client-title: calc(0.8rem + 4px);
		--cp-docs-title: calc(0.8rem + 2px);
	}

	[x-cloak] { display: none !important; }

	.cp-layout {
		display: grid;
		grid-template-columns: minmax(240px, 300px) 1fr;
		gap: 1rem;
		align-items: start;
		margin-bottom: 2rem;
	}

	@media (max-width: 991.98px) {
		.cp-layout { grid-template-columns: 1fr; }
	}

	.cp-controls {
		position: sticky;
		top: 0.75rem;
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 12px;
		padding: 1rem;
		box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
		max-height: calc(100vh - 2rem);
		overflow: auto;
	}

	.cp-controls__title {
		margin: 0 0 0.25rem;
		font-size: 0.95rem;
		font-weight: 700;
		color: #0f172a;
	}

	.cp-controls__hint {
		font-size: 0.72rem;
		color: #64748b;
		margin-bottom: 0.85rem;
		min-height: 2.2em;
	}

	.cp-scenario-toggle {
		display: grid;
		grid-template-columns: 1fr 1fr;
		gap: 0.45rem;
		margin-bottom: 0.85rem;
	}

	.cp-scenario-btn {
		display: flex;
		flex-direction: column;
		align-items: flex-start;
		gap: 0.15rem;
		padding: 0.55rem 0.65rem;
		border-radius: 10px;
		border: 1px solid #e2e8f0;
		background: #f8fafc;
		text-align: left;
		cursor: pointer;
		transition: border-color 0.15s, background 0.15s, box-shadow 0.15s;
	}

	.cp-scenario-btn strong {
		font-size: 0.85rem;
		color: #0f172a;
	}

	.cp-scenario-btn span {
		font-size: 0.65rem;
		color: #64748b;
		font-weight: 500;
		line-height: 1.3;
	}

	.cp-scenario-btn.is-active {
		background: #eff6ff;
		border-color: #93c5fd;
		box-shadow: 0 0 0 3px color-mix(in srgb, #93c5fd 35%, transparent);
	}

	.cp-scenario-btn.is-active strong { color: #1e3a8a; }

	.cp-scenario-notes {
		margin: 0 0 0.75rem;
		padding: 0.55rem 0.65rem;
		background: #f8fafc;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
	}

	.cp-scenario-notes ul {
		margin: 0;
		padding-left: 1.1rem;
		font-size: 0.68rem;
		color: #475569;
		line-height: 1.45;
	}

	.cp-scenario-notes code {
		font-size: 0.6rem;
		color: #0369a1;
	}

	.cp-control-row {
		display: flex;
		flex-wrap: wrap;
		gap: 0.4rem;
		margin: 0.5rem 0 0.75rem;
	}

	.cp-snapshot {
		margin: 0;
		padding: 0.65rem 0.7rem;
		background: #0f172a;
		color: #e2e8f0;
		border-radius: 8px;
		font-size: 0.65rem;
		line-height: 1.45;
		overflow: auto;
		max-height: 14rem;
		white-space: pre-wrap;
	}

	.cp-copied {
		margin: 0.4rem 0 0;
		font-size: 0.7rem;
		font-weight: 600;
		color: #15803d;
	}

	.cp-stage-frame {
		display: grid;
		grid-template-columns: 220px 1fr;
		min-height: 34rem;
		border-radius: 14px;
		overflow: visible;
		border: 1px solid #cbd5e1;
		box-shadow: 0 8px 28px rgba(15, 23, 42, 0.1);
		background: #f1f5f9;
	}

	@media (max-width: 767.98px) {
		.cp-stage-frame { grid-template-columns: 1fr; }
		.cp-sidebar { border-radius: 14px 14px 0 0; }
	}

	.cp-sidebar {
		display: flex;
		flex-direction: column;
		background: var(--cp-sidebar-bg);
		color: #fff;
		min-height: 100%;
		position: relative;
		border-radius: 14px 0 0 14px;
		overflow: hidden;
	}

	.cp-sidebar::before {
		content: '';
		position: absolute;
		inset: 0;
		pointer-events: none;
		z-index: 0;
		background: linear-gradient(
			180deg,
			color-mix(in srgb, var(--cp-blue-soft) var(--cp-sidebar-wash), transparent) 0%,
			transparent 42%
		);
	}

	.cp-scenario-C .cp-sidebar::after {
		content: '';
		position: absolute;
		inset: 0;
		pointer-events: none;
		z-index: 0;
		background:
			radial-gradient(ellipse 120% 55% at 50% -10%, color-mix(in srgb, var(--cp-blue-soft) 45%, transparent), transparent 70%),
			linear-gradient(90deg, color-mix(in srgb, var(--cp-blue-accent) 18%, transparent), transparent 28%);
		opacity: 0.9;
	}

	.cp-scenario-C .cp-sidebar__brand {
		border-color: color-mix(in srgb, var(--cp-blue-accent) 35%, rgba(255, 255, 255, 0.2));
		box-shadow:
			inset 0 1px 0 color-mix(in srgb, var(--cp-blue-soft) 55%, transparent),
			0 4px 14px rgba(0, 0, 0, 0.16);
	}

	.cp-scenario-C .cp-sidebar__link.is-active,
	.cp-scenario-C .cp-sidebar__link.is-open,
	.cp-scenario-C .cp-sidebar__sublink.is-active {
		border-color: color-mix(in srgb, var(--cp-blue-accent) 40%, transparent);
		background: color-mix(in srgb, var(--cp-blue-soft) 18%, var(--cp-glass));
	}

	.cp-scenario-D .cp-sidebar::after {
		content: '';
		position: absolute;
		inset: 0;
		pointer-events: none;
		z-index: 0;
		background: radial-gradient(ellipse 100% 40% at 0% 0%, color-mix(in srgb, var(--cp-blue-soft) 22%, transparent), transparent 65%);
	}

	.cp-scenario-D .cp-sidebar__brand {
		border-color: rgba(255, 255, 255, 0.14);
	}

	/* K — slate base; burgundy demoted to a signal: brand card + active-nav accent only */
	.cp-scenario-K .cp-sidebar__brand {
		background: linear-gradient(135deg, color-mix(in srgb, var(--cp-burgundy) 58%, var(--cp-glass)) 0%, color-mix(in srgb, var(--cp-burgundy) 32%, rgba(255, 255, 255, 0.04)) 100%);
		border-color: color-mix(in srgb, var(--cp-burgundy) 42%, rgba(255, 255, 255, 0.22));
		box-shadow:
			inset 0 1px 0 rgba(255, 255, 255, 0.18),
			0 4px 14px rgba(0, 0, 0, 0.2),
			0 0 0 1px color-mix(in srgb, var(--cp-burgundy) 28%, transparent);
	}

	.cp-scenario-K .cp-sidebar__link:hover {
		background: rgba(255, 255, 255, 0.06);
	}

	.cp-scenario-K .cp-sidebar__link.is-active,
	.cp-scenario-K .cp-sidebar__link.is-open,
	.cp-scenario-K .cp-sidebar__sublink.is-active {
		background: color-mix(in srgb, var(--cp-burgundy) 16%, var(--cp-glass));
		border-color: color-mix(in srgb, var(--cp-burgundy) 30%, rgba(255, 255, 255, 0.14));
		box-shadow: inset 3px 0 0 0 var(--cp-burgundy);
	}

	.cp-sidebar__brand {
		position: relative;
		z-index: 1;
		margin: 0.65rem 0.55rem 0.5rem;
		padding: 0.85rem 0.65rem;
		text-align: center;
		border-radius: 12px;
		border: 1px solid rgba(255, 255, 255, 0.22);
		background: linear-gradient(135deg, var(--cp-glass) 0%, rgba(255, 255, 255, 0.04) 100%);
		backdrop-filter: blur(10px);
		-webkit-backdrop-filter: blur(10px);
		box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.18), 0 4px 14px rgba(0, 0, 0, 0.18);
	}

	.cp-sidebar__flask {
		display: block;
		font-size: 3.5rem;
		line-height: 1;
		color: #fff;
		text-shadow: 0 0 20px rgba(255, 255, 255, 0.45);
		margin-bottom: 0.35rem;
	}

	.cp-sidebar__brand-label {
		display: block;
		font-size: 0.78rem;
		font-weight: 700;
		letter-spacing: 0.06em;
		text-transform: uppercase;
		color: #fff;
	}

	.cp-sidebar__nav {
		position: relative;
		z-index: 1;
		flex: 1 1 auto;
		padding: 0.35rem 0.45rem 0.75rem;
		overflow: auto;
	}

	.cp-sidebar__link,
	.cp-sidebar__sublink {
		display: flex;
		align-items: center;
		gap: 0.55rem;
		padding: 0.55rem 0.65rem;
		margin: 0.15rem 0;
		border-radius: 8px;
		color: rgba(255, 255, 255, 0.78);
		text-decoration: none;
		font-size: 0.8rem;
		font-weight: 500;
		border: 1px solid transparent;
		transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
	}

	.cp-sidebar__link .mdi:first-child {
		font-size: 1.05rem;
		width: 1.15rem;
		text-align: center;
	}

	.cp-sidebar__link .submenu-chevron {
		margin-left: auto;
		opacity: 0.7;
		font-size: 0.95rem;
	}

	.cp-sidebar__link:hover {
		color: #fff;
		background: rgba(255, 255, 255, 0.06);
	}

	.cp-sidebar__link.is-active,
	.cp-sidebar__link.is-open {
		color: #fff;
		background: var(--cp-glass);
		backdrop-filter: blur(8px);
		-webkit-backdrop-filter: blur(8px);
		border-color: rgba(255, 255, 255, 0.14);
		box-shadow: inset 3px 0 0 0 var(--cp-blue-accent);
	}

	.cp-sidebar__submenu { padding-left: 0.35rem; }

	.cp-sidebar__sublink {
		font-size: 0.75rem;
		padding: 0.4rem 0.55rem 0.4rem 0.85rem;
		color: rgba(255, 255, 255, 0.7);
	}

	.cp-sidebar__sublink:hover,
	.cp-sidebar__sublink.is-active {
		color: #fff;
		background: rgba(255, 255, 255, 0.07);
	}

	.cp-sidebar__sublink.is-active {
		box-shadow: inset 3px 0 0 0 var(--cp-blue-accent);
		background: var(--cp-glass);
	}

	.cp-sidebar__foot {
		position: relative;
		z-index: 1;
		display: flex;
		align-items: center;
		gap: 0.4rem;
		padding: 0.65rem 0.55rem 0.75rem;
		border-top: 1px solid rgba(255, 255, 255, 0.1);
		background: rgba(0, 0, 0, 0.12);
	}

	.cp-sidebar__signout,
	.cp-sidebar__switch {
		border: 1px solid rgba(255, 255, 255, 0.2);
		background: var(--cp-glass);
		color: #fff;
		border-radius: 8px;
		font-size: 0.72rem;
		font-weight: 600;
		padding: 0.4rem 0.55rem;
		display: inline-flex;
		align-items: center;
		gap: 0.3rem;
		opacity: 0.85;
		cursor: not-allowed;
	}

	.cp-sidebar__switch {
		margin-left: auto;
		padding: 0.4rem 0.5rem;
	}

	.cp-main {
		padding: 0.85rem;
		display: flex;
		flex-direction: column;
		gap: 0.75rem;
		min-width: 0;
		overflow: visible;
	}

	.cp-pill {
		position: relative;
		isolation: isolate;
		overflow: visible;
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		justify-content: space-between;
		gap: 0.65rem;
		padding: 0.75rem 0.95rem;
		border-radius: 12px;
		background: linear-gradient(135deg, var(--cp-pill-0) 0%, var(--cp-pill-1) 55%, var(--cp-pill-2) 100%);
		border: 1px solid var(--cp-pill-border);
		box-shadow: 0 2px 10px color-mix(in srgb, var(--cp-burgundy) 28%, transparent);
		z-index: 5;
	}

	.cp-pill__stars {
		position: absolute;
		right: 0;
		bottom: 0;
		width: min(52%, 18rem);
		height: min(85%, 6rem);
		pointer-events: none;
		z-index: 0;
		opacity: var(--cp-stars-opacity);
		border-radius: 0 0 12px 0;
		overflow: hidden;
		background-image:
			radial-gradient(1.4px 1.4px at 12% 78%, rgba(255, 255, 255, 0.95), transparent 60%),
			radial-gradient(1px 1px at 28% 58%, rgba(255, 255, 255, 0.75), transparent 60%),
			radial-gradient(1.6px 1.6px at 44% 88%, rgba(255, 255, 255, 0.9), transparent 60%),
			radial-gradient(1px 1px at 62% 62%, rgba(255, 255, 255, 0.65), transparent 60%),
			radial-gradient(1.3px 1.3px at 78% 82%, rgba(255, 255, 255, 0.85), transparent 60%),
			radial-gradient(1px 1px at 90% 70%, rgba(255, 255, 255, 0.7), transparent 60%);
		mask-image: linear-gradient(to top left, #000 15%, transparent 72%);
		-webkit-mask-image: linear-gradient(to top left, #000 15%, transparent 72%);
	}

	.cp-pill--blue {
		box-shadow: 0 8px 20px rgb(59 130 246 / 0.08);
	}

	/* H — plain white pill (no burgundy wash) */
	.cp-pill--plain-white {
		background: #fff;
		border-color: #e2e8f0;
		box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
	}

	/* I — B baby-blue + burgundy wash */
	.cp-pill--wash-blue {
		background:
			linear-gradient(180deg, rgb(139 21 56 / 0.06) 0%, transparent 100%),
			linear-gradient(135deg, #eff6ff 0%, #dbeafe 55%, #e0f2fe 100%);
		border-color: #bfdbfe;
		box-shadow: 0 8px 20px rgb(59 130 246 / 0.08);
	}

	.cp-pill__identity,
	.cp-pill__actions {
		position: relative;
		z-index: 1;
	}

	.cp-pill__identity {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 0.35rem 0.45rem;
	}

	.cp-pill__trf {
		font-family: var(--ls-font-mono, "IBM Plex Mono", ui-monospace, monospace);
		font-size: 1.05rem;
		font-weight: 600;
		letter-spacing: -0.01em;
	}

	.cp-pill__form {
		font-size: 0.875rem;
		font-weight: 400;
	}

	.cp-pill__stage {
		margin-left: 0.15rem;
		background: color-mix(in srgb, var(--cp-blue-accent) 28%, transparent);
		color: #fff;
		border: 1px solid color-mix(in srgb, var(--cp-blue-accent) 55%, rgba(255, 255, 255, 0.35));
		backdrop-filter: blur(10px);
		-webkit-backdrop-filter: blur(10px);
		box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.25);
		border-radius: 999px;
		padding: 0.15rem 0.55rem;
		font-size: 0.7rem;
		font-weight: 500;
	}

	.cp-pill__stage--sub {
		background: #f0f9ff;
		border-color: #bae6fd;
		color: #0369a1;
		backdrop-filter: none;
		box-shadow: none;
		font-weight: 700;
		letter-spacing: 0.04em;
		text-transform: uppercase;
		font-size: 0.65rem;
	}

	/* E — burgundy ink on blue pill */
	.cp-pill__stage--burgundy {
		background: transparent;
		border-color: color-mix(in srgb, var(--cp-burgundy) 35%, transparent);
		color: var(--cp-burgundy);
		backdrop-filter: none;
		box-shadow: none;
		font-weight: 700;
		letter-spacing: 0.04em;
		text-transform: uppercase;
		font-size: 0.65rem;
	}

	.cp-badge--burgundy {
		background: color-mix(in srgb, var(--cp-burgundy) 10%, #fff) !important;
		border-color: color-mix(in srgb, var(--cp-burgundy) 32%, #e2e8f0) !important;
		color: var(--cp-burgundy) !important;
		font-weight: 700;
		letter-spacing: 0.02em;
		backdrop-filter: none;
	}

	.cp-badge--burgundy-soft {
		background: color-mix(in srgb, var(--cp-burgundy) 6%, #fff) !important;
		border-color: color-mix(in srgb, var(--cp-burgundy) 22%, #e2e8f0) !important;
		color: var(--cp-burgundy) !important;
		font-weight: 600;
		backdrop-filter: none;
	}

	.cp-badge--burgundy-text { color: var(--cp-burgundy) !important; }
	.cp-badge--burgundy-text .mdi { color: var(--cp-burgundy) !important; }

	/* F — green approver control */
	.cp-approve {
		position: relative;
		z-index: 25;
	}

	.cp-approve__btn {
		position: relative;
		width: 2.15rem;
		height: 2.15rem;
		padding: 0;
		border-radius: 8px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: #ecfdf5;
		border: 1px solid #a7f3d0;
		color: #047857;
		cursor: pointer;
	}

	.cp-approve__btn .mdi {
		font-size: 1.25rem;
		color: #047857;
		animation: cp-approve-shake 1.35s ease-in-out infinite;
		transform-origin: 50% 55%;
	}

	.cp-approve__badge {
		position: absolute;
		top: -4px;
		right: -4px;
		min-width: 1.05rem;
		height: 1.05rem;
		padding: 0 0.25rem;
		border-radius: 999px;
		background: #047857;
		color: #fff;
		font-size: 0.65rem;
		font-weight: 700;
		line-height: 1.05rem;
		text-align: center;
	}

	@keyframes cp-approve-shake {
		0%, 100% { transform: rotate(0deg) scale(1); }
		12% { transform: rotate(12deg) scale(1.05); }
		24% { transform: rotate(-10deg) scale(1.05); }
		36% { transform: rotate(8deg) scale(1.04); }
		48% { transform: rotate(-6deg) scale(1.03); }
		60% { transform: rotate(3deg) scale(1.02); }
		72% { transform: rotate(0deg) scale(1); }
	}

	.cp-approve__menu {
		position: absolute;
		top: calc(100% + 0.35rem);
		right: 0;
		min-width: 16rem;
		padding: 0.35rem 0;
		background: #fff;
		border: 1px solid #dbe5f0;
		border-radius: 10px;
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.16);
		z-index: 40;
	}

	.cp-approve__meta {
		padding: 0.35rem 0.9rem 0.45rem;
		font-size: 0.72rem;
		color: #64748b;
	}

	.cp-approve__item {
		display: flex;
		align-items: center;
		gap: 0.45rem;
		width: 100%;
		padding: 0.5rem 0.85rem;
		border: none;
		background: transparent;
		font-size: 0.8rem;
		font-weight: 500;
		color: #334155;
		text-align: left;
		cursor: pointer;
	}

	.cp-approve__item:hover { background: #f1f5f9; }
	.cp-approve__item--ok { color: #047857; }
	.cp-approve__item--ok .mdi { color: #047857; }
	.cp-approve__item--danger { color: #b91c1c; }
	.cp-approve__item--danger .mdi { color: #b91c1c; }

	.cp-approve__panel {
		padding: 0.55rem 0.85rem 0.7rem;
		border-top: 1px solid #e8eef4;
	}

	.cp-approve__label {
		display: block;
		font-size: 0.68rem;
		font-weight: 700;
		color: #475569;
		margin-bottom: 0.25rem;
	}

	.cp-approve__textarea {
		width: 100%;
		font-size: 0.75rem;
		border: 1px solid #e2e8f0;
		border-radius: 6px;
		padding: 0.35rem 0.45rem;
		margin-bottom: 0.4rem;
		resize: vertical;
	}

	.cp-approve__confirm {
		font-size: 0.72rem;
		font-weight: 600;
		padding: 0.3rem 0.55rem;
		border-radius: 6px;
		border: 1px solid #fecaca;
		background: #fff;
		color: #b91c1c;
		cursor: pointer;
	}

	.cp-pill__actions {
		display: flex;
		flex-wrap: wrap;
		align-items: center;
		gap: 0.4rem;
	}

	.cp-pill__chip,
	.cp-pill__cta {
		display: inline-flex;
		align-items: center;
		gap: 0.3rem;
		border-radius: 8px;
		font-size: 0.78rem;
		font-weight: 500;
		padding: 0.35rem 0.7rem;
		background: color-mix(in srgb, #eff6ff 82%, transparent);
		border: 1px solid color-mix(in srgb, #8ac0ff 45%, rgba(255, 255, 255, 0.35));
		color: #1e3a8a;
		cursor: pointer;
		backdrop-filter: blur(8px);
	}

	.cp-pill__cta { font-weight: 600; }

	.cp-badge--acc {
		background: #ecfdf5 !important;
		border-color: #a7f3d0 !important;
		color: #047857 !important;
		font-weight: 700;
		letter-spacing: 0.02em;
		backdrop-filter: none;
	}

	.cp-badge--sub {
		background: #f0f9ff !important;
		border-color: #bae6fd !important;
		color: #0369a1 !important;
		font-weight: 700;
		letter-spacing: 0.02em;
		backdrop-filter: none;
	}

	.cp-badge--acc-text { color: #047857 !important; }
	.cp-badge--acc-text .mdi { color: #047857 !important; }
	.cp-badge--sub-text { color: #0369a1 !important; }
	.cp-badge--sub-text .mdi { color: #0369a1 !important; }

	.cp-pill__dropdown {
		position: relative;
		z-index: 20;
	}

	.cp-pill__menu {
		position: absolute;
		top: calc(100% + 0.35rem);
		right: 0;
		min-width: 14rem;
		padding: 0.35rem 0;
		background: #fff;
		border: 1px solid #dbe5f0;
		border-radius: 10px;
		box-shadow: 0 12px 28px rgba(15, 23, 42, 0.16);
		z-index: 30;
	}

	.cp-pill__menu-item {
		display: flex;
		align-items: center;
		gap: 0.45rem;
		width: 100%;
		padding: 0.5rem 0.85rem;
		border: none;
		background: transparent;
		font-size: 0.8rem;
		font-weight: 500;
		color: #334155;
		text-align: left;
		cursor: pointer;
	}

	.cp-pill__menu-item:hover {
		background: #eff6ff;
		color: #1e3a8a;
	}

	.cp-pill__menu-item .mdi {
		width: 1.1rem;
		text-align: center;
		color: #64748b;
	}

	.cp-console {
		display: grid;
		grid-template-columns: minmax(200px, 260px) 1fr;
		gap: 0.75rem;
		min-height: 18rem;
	}

	@media (max-width: 767.98px) {
		.cp-console { grid-template-columns: 1fr; }
	}

	.cp-rail {
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 10px;
		overflow: hidden;
		box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
	}

	/* Default: soft wash on whole rail (matches production rail panel) */
	.cp-rail:not(.cp-rail--section-wash) {
		background:
			linear-gradient(180deg, rgb(139 21 56 / 0.06) 0%, transparent 100%),
			#fff;
	}

	.cp-rail__section {
		padding: 0.9rem 0.95rem;
		border-bottom: 1px solid #e2e8f0;
	}

	.cp-rail__section:last-child { border-bottom: none; }

	/* H — wash only on Client info (first section) */
	.cp-rail--section-wash .cp-rail__section--client {
		background: linear-gradient(180deg, rgb(139 21 56 / 0.06) 0%, transparent 100%);
	}

	.cp-rail__heading {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 0.5rem;
		margin-bottom: 0.85rem;
	}

	.cp-rail__title {
		display: flex;
		align-items: center;
		gap: 0.4rem;
		margin: 0 0 0.65rem;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
		font-size: var(--cp-docs-title);
		font-weight: 700;
		letter-spacing: 0.03em;
		text-transform: uppercase;
		color: var(--cp-rail-ink);
	}

	.cp-rail__title--client {
		margin-bottom: 0;
		font-size: var(--cp-client-title);
	}

	.cp-rail__title .mdi,
	.cp-rail__title--client .mdi {
		font-size: 1.15em;
		color: var(--cp-rail-ink);
	}

	.cp-rail__icons { display: inline-flex; gap: 0.3rem; }

	.cp-rail__icon-btn {
		width: 1.35rem;
		height: 1.35rem;
		padding: 0;
		border-radius: 5px;
		border: 1px solid color-mix(in srgb, var(--cp-rail-ink) 20%, #e2e8f0);
		background: #f8fafc;
		color: var(--cp-rail-ink);
		font-size: 0.8rem;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		cursor: not-allowed;
	}

	.cp-rail__fields {
		margin: 0;
		display: flex;
		flex-direction: column;
		gap: 0.75rem;
	}

	.cp-rail__field dt {
		margin: 0 0 0.1rem;
		font-size: 0.875rem;
		font-weight: 500;
		color: #1e293b;
	}

	.cp-rail__field dd {
		margin: 0;
		font-size: 0.7rem;
		font-weight: 500;
		color: #64748b;
	}

	.cp-rail__email {
		display: flex;
		align-items: center;
		gap: 0.25rem;
		margin-top: 0.2rem;
		font-size: 0.62rem;
		color: #94a3b8;
	}

	.cp-rail__doc {
		padding: 0.45rem 0.55rem;
		border-radius: 8px;
		border: 1px solid #e2e8f0;
		background: #f8fafc;
		font-size: 0.8125rem;
		font-weight: 500;
		margin-bottom: 0.35rem;
	}

	.cp-rail__empty {
		margin: 0;
		font-size: 0.7rem;
		color: #64748b;
	}

	.cp-canvas {
		background:
			linear-gradient(180deg, rgb(139 21 56 / 0.06) 0%, transparent 100%),
			#fff;
		border: 1px solid #e2e8f0;
		border-radius: 10px;
		overflow: hidden;
		box-shadow: 0 1px 3px rgba(15, 23, 42, 0.05);
	}

	/* H / I / J — main card right of rail: no burgundy wash */
	.cp-stage-frame[data-scenario="H"] .cp-canvas,
	.cp-stage-frame[data-scenario="I"] .cp-canvas,
	.cp-stage-frame[data-scenario="J"] .cp-canvas,
	.cp-scenario-H .cp-canvas,
	.cp-scenario-I .cp-canvas,
	.cp-scenario-J .cp-canvas {
		background: #fff;
	}

	.cp-tabs {
		display: flex;
		flex-wrap: wrap;
		gap: 0.15rem;
		padding: 0.45rem 0.65rem 0;
		border-bottom: 1px solid #e2e8f0;
		background: transparent;
	}

	.cp-tab {
		padding: 0.45rem 0.7rem;
		font-size: 0.78rem;
		font-weight: 500;
		color: #64748b;
		border-bottom: 2px solid transparent;
	}

	.cp-tab.is-active {
		color: #1e3a8a;
		font-weight: 600;
		border-bottom-color: #1e3a8a;
	}

	.cp-canvas__body { padding: 1rem; }
</style>
