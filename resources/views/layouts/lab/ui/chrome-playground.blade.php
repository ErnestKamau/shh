@extends('layouts.lab.layout.app', ['select2' => false])

@section('title2')
<title>Chrome Playground | Lab</title>
@endsection

@section('content2')
<script>
	window.chromePlayground = function chromePlayground() {
		const sharedSidebar = {
			burgundy: '#6d0a0e',
			sidebarDepth: 90,
			glassOpacity: 16,
			blueAccent: '#8ac0ff',
			blueSoft: '#eff6ff',
		};

		return {
			scenario: 'B',
			sampleOpen: true,
			billingOpen: false,
			actionsOpen: false,
			approveOpen: false,
			rejectOpen: false,
			copied: false,
			frameStyle: {},
			trfColor: '#ffffff',
			formColor: 'rgba(255,255,255,0.92)',
			sepColor: 'rgba(255,255,255,0.55)',
			snapshot: '',
			scenarioLabel: '',

			init() {
				this.apply();
			},

			setScenario(key) {
				this.scenario = key;
				this.actionsOpen = false;
				this.approveOpen = false;
				this.rejectOpen = false;
				this.apply();
			},

			isBluePill() {
				return ['B', 'C', 'D', 'E', 'F', 'K'].includes(this.scenario);
			},

			isBurgundyInk() {
				return this.scenario === 'E' || this.scenario === 'H';
			},

			isApproveControl() {
				return ['F', 'G', 'H', 'I', 'J'].includes(this.scenario);
			},

			isCurrentRequestPill() {
				return this.scenario === 'G';
			},

			isPlainWhitePill() {
				return this.scenario === 'H';
			},

			isBlueWashPill() {
				return this.scenario === 'I' || this.scenario === 'J';
			},

			usesNavyInk() {
				return (this.isBluePill() || this.isPlainWhitePill() || this.isBlueWashPill()) && !this.isCurrentRequestPill();
			},

			usesAccSubCtas() {
				return (this.isBluePill() || this.isCurrentRequestPill() || this.isBlueWashPill()) && !this.isBurgundyInk();
			},

			apply() {
				const b = sharedSidebar.burgundy;
				const soft = sharedSidebar.blueSoft;
				const accent = sharedSidebar.blueAccent;
				const depth = sharedSidebar.sidebarDepth;
				const glass = sharedSidebar.glassOpacity / 100;
				const s = this.scenario;
				const bluePill = this.isBluePill();
				const burgundyInk = this.isBurgundyInk();
				const currentPill = this.isCurrentRequestPill();
				const plainWhite = this.isPlainWhitePill();
				const blueWash = this.isBlueWashPill();

				if (currentPill) {
					this.trfColor = '#ffffff';
					this.formColor = 'rgba(255, 255, 255, 0.92)';
					this.sepColor = 'rgba(255, 255, 255, 0.55)';
				} else if (this.usesNavyInk()) {
					this.trfColor = '#1e3a8a';
					this.formColor = '#1e3a8a';
					this.sepColor = '#94a3b8';
				} else {
					this.trfColor = '#ffffff';
					this.formColor = 'rgba(255, 255, 255, 0.92)';
					this.sepColor = 'rgba(255, 255, 255, 0.55)';
				}

				const labels = {
					A: 'A — Rich burgundy pill + deep burgundy sidebar',
					B: 'B — Quote-params blue pill + deep burgundy sidebar',
					C: 'C — Blue pill + burgundy sidebar with baby-blue wash',
					D: 'D — Blue pill + colorless slate sidebar',
					E: 'E — Blue pill (B) identity + burgundy CTAs / Actions',
					F: 'F — B pill/CTAs + green approver check (pending only)',
					G: 'G — Current request pill + F green check + B CTAs/Actions',
					H: 'H — White pill + burgundy CTAs; rail Client info wash only; plain main card',
					I: 'I — B blue pill + burgundy wash; main card without wash',
					J: 'J — Same as I + softer / less rich burgundy sidebar',
					K: 'K — Slate sidebar; burgundy demoted to signal (brand card + active-nav accent only)',
				};
				this.scenarioLabel = labels[s] || labels.B;

				let sidebarBg = `color-mix(in srgb, ${b} ${depth}%, #0f172a)`;
				let glassVal = `rgba(255, 255, 255, ${glass})`;
				let wash = '12%';
				let sidebarNote = 'deep burgundy (authorized Slice 1)';

				if (s === 'C') {
					sidebarBg = `linear-gradient(165deg, color-mix(in srgb, ${soft} 28%, ${b}) 0%, color-mix(in srgb, ${b} 82%, #0f172a) 42%, color-mix(in srgb, ${b} ${depth}%, #0f172a) 100%)`;
					glassVal = `color-mix(in srgb, ${soft} 22%, rgba(255, 255, 255, ${glass}))`;
					wash = '38%';
					sidebarNote = 'burgundy + baby-blue wash / blue-tinted glass';
				} else if (s === 'D') {
					sidebarBg = 'linear-gradient(180deg, #1e293b 0%, #0f172a 100%)';
					glassVal = `color-mix(in srgb, ${soft} 14%, rgba(255, 255, 255, ${glass}))`;
					wash = '18%';
					sidebarNote = 'colorless slate (#1e293b → #0f172a); baby-blue active bar only';
				} else if (s === 'J') {
					sidebarBg = `linear-gradient(180deg, color-mix(in srgb, ${b} 52%, #2a1418) 0%, color-mix(in srgb, ${b} 54%, #261216) 50%, color-mix(in srgb, ${b} 56%, #221014) 100%)`;
					glassVal = `color-mix(in srgb, ${soft} 14%, rgba(255, 255, 255, ${glass}))`;
					wash = '14%';
					sidebarNote = 'softer burgundy, tight gradient range (I chrome)';
				} else if (s === 'K') {
					sidebarBg = `linear-gradient(180deg, color-mix(in srgb, ${b} 22%, #1e293b) 0%, #1e293b 24%, #0f172a 100%)`;
					glassVal = `rgba(255, 255, 255, ${glass})`;
					wash = '6%';
					sidebarNote = 'slate base; burgundy confined to brand card + active-nav accent bar only';
				}

				let pill0 = `color-mix(in srgb, ${b} 92%, #1a0506)`;
				let pill1 = `color-mix(in srgb, ${b} 88%, #3b0a0e)`;
				let pill2 = `color-mix(in srgb, ${b} 82%, ${soft})`;
				let pillBorder = `color-mix(in srgb, ${b} 70%, #7f1d1d)`;
				let starsOpacity = '0.55';
				let pillNote = 'rich burgundy gradient';

				if (currentPill) {
					pill0 = `color-mix(in srgb, ${b} 85%, ${soft})`;
					pill1 = `color-mix(in srgb, ${b} 78%, #f8fafc)`;
					pill2 = `color-mix(in srgb, ${b} 70%, ${soft})`;
					pillBorder = `color-mix(in srgb, ${b} 55%, #dbeafe)`;
					pillNote = 'current request-view burgundy×blue (85→70) + white TRF/form';
				} else if (plainWhite) {
					pill0 = '#ffffff';
					pill1 = '#ffffff';
					pill2 = '#ffffff';
					pillBorder = '#e2e8f0';
					starsOpacity = '0';
					pillNote = 'plain white pill; Client info section wash; plain main card; burgundy CTAs/status';
				} else if (blueWash) {
					pill0 = '#eff6ff';
					pill1 = '#dbeafe';
					pill2 = '#e0f2fe';
					pillBorder = '#bfdbfe';
					starsOpacity = '0.18';
					pillNote = s === 'J'
						? 'I chrome + softer / less rich burgundy sidebar'
						: 'B blue pill + rail burgundy wash; main card plain white';
				} else if (bluePill) {
					pill0 = '#eff6ff';
					pill1 = '#dbeafe';
					pill2 = '#e0f2fe';
					pillBorder = '#bfdbfe';
					starsOpacity = '0.22';
					pillNote = 'quote-params hero (#eff6ff → #dbeafe → #e0f2fe)';
				}

				this.frameStyle = {
					'--cp-burgundy': b,
					'--cp-blue-accent': accent,
					'--cp-blue-soft': soft,
					'--cp-sidebar-bg': sidebarBg,
					'--cp-sidebar-wash': wash,
					'--cp-glass': glassVal,
					'--cp-rail-ink': '#1e3a8a',
					'--cp-client-title': 'calc(0.8rem + 4px)',
					'--cp-docs-title': 'calc(0.8rem + 2px)',
					'--cp-pill-0': pill0,
					'--cp-pill-1': pill1,
					'--cp-pill-2': pill2,
					'--cp-pill-border': pillBorder,
					'--cp-stars-opacity': starsOpacity,
				};

				this.snapshot = [
					`/* Chrome Playground — Scenario ${s} */`,
					`scenario: ${this.scenarioLabel}`,
					`--cp-burgundy: ${b};`,
					`--cp-sidebar: ${sidebarNote};`,
					`--cp-sidebar-wash: ${wash};`,
					`--cp-blue-accent: ${accent};`,
					`--cp-blue-soft: ${soft};`,
					`--cp-rail-ink: #1e3a8a;`,
					`--cp-pill: ${pillNote};`,
					burgundyInk
						? `--cp-pill-ink: B navy TRF/form/status; burgundy soft CTAs (doc icon, Record PO, Actions);`
						: this.isApproveControl()
							? `--cp-approve: green check + Acc/Sub CTAs (G controls);`
							: bluePill
								? `--cp-pill-ctas: Acc (#ecfdf5/#047857) + Sub (#f0f9ff/#0369a1);`
								: `--cp-pill-ctas: soft glass chips;`,
					`--cp-trf-ink: ${this.trfColor};`,
				].join('\n');
			},

			async copySnapshot() {
				try {
					await navigator.clipboard.writeText(this.snapshot);
					this.copied = true;
					setTimeout(() => { this.copied = false; }, 1600);
				} catch (e) {
					console.warn('Copy failed', e);
				}
			},
		};
	};
</script>
@php
	$items = [
		['link' => route('dashboard-lab'), 'name' => 'Dashboard', 'icon' => null],
		['link' => route('lab.ui.ls-gallery'), 'name' => 'LS UI Kit', 'icon' => null],
		['link' => route('lab.ui.chrome-playground'), 'name' => 'Chrome Playground', 'icon' => null],
	];
@endphp

<main
	class="container-fluid workflow-board-page lab-panel-theme workflow-theme lab-surface-theme ls-ui-kit cp-page"
	data-ls-type="plex"
	x-data="chromePlayground()"
	x-init="init()"
	:data-scenario="scenario"
>
	@include('layouts.lab.partials.lab-panel-theme-styles')
	@include('layouts.lab.partials.lab-surface-theme-styles')
	@include('layouts.lab.partials.ls-ui.ls-ui-tokens-and-styles')
	@include('layouts.lab.partials.ui.chrome-playground-styles')

	<x-bread-crumb :items="$items"></x-bread-crumb>

	<div class="batch-header-bar mb-3">
		<div class="batch-header-top">
			<div class="batch-title-group">
				<span class="batch-code-label">Chrome Playground</span>
				<span class="batch-stage-pill">
					<i class="mdi mdi-palette-swatch-outline" style="font-size:0.75rem;"></i>
					Scenarios A–K — compare before authorize
				</span>
			</div>
		</div>
	</div>

	<p class="text-muted small mb-3">
		H = plain white pill + burgundy status/CTAs; Client info wash only; plain main card.
		I = B blue pill + burgundy wash; main card without wash.
		J = I + softer / less rich burgundy sidebar.
		<a href="{{ route('lab.ui.ls-gallery') }}">LS UI Kit</a>
	</p>

	<div class="cp-layout">
		<aside class="cp-controls" aria-label="Scenario controls">
			<h2 class="cp-controls__title">Scenarios</h2>
			<p class="cp-controls__hint" x-text="scenarioLabel"></p>

			<div class="cp-scenario-toggle" role="tablist" aria-label="Scenario">
				<button
					type="button"
					class="cp-scenario-btn"
					:class="{ 'is-active': scenario === 'A' }"
					@click="setScenario('A')"
					role="tab"
					:aria-selected="scenario === 'A'"
				>
					<strong>A</strong>
					<span>Burgundy pill + burgundy nav</span>
				</button>
				<button
					type="button"
					class="cp-scenario-btn"
					:class="{ 'is-active': scenario === 'B' }"
					@click="setScenario('B')"
					role="tab"
					:aria-selected="scenario === 'B'"
				>
					<strong>B</strong>
					<span>Blue pill + burgundy nav</span>
				</button>
				<button
					type="button"
					class="cp-scenario-btn"
					:class="{ 'is-active': scenario === 'C' }"
					@click="setScenario('C')"
					role="tab"
					:aria-selected="scenario === 'C'"
				>
					<strong>C</strong>
					<span>Blue pill + blue-wash nav</span>
				</button>
				<button
					type="button"
					class="cp-scenario-btn"
					:class="{ 'is-active': scenario === 'D' }"
					@click="setScenario('D')"
					role="tab"
					:aria-selected="scenario === 'D'"
				>
					<strong>D</strong>
					<span>Blue pill + slate nav</span>
				</button>
				<button
					type="button"
					class="cp-scenario-btn"
					:class="{ 'is-active': scenario === 'E' }"
					@click="setScenario('E')"
					role="tab"
					:aria-selected="scenario === 'E'"
				>
					<strong>E</strong>
					<span>B identity + burgundy CTAs</span>
				</button>
				<button
					type="button"
					class="cp-scenario-btn"
					:class="{ 'is-active': scenario === 'F' }"
					@click="setScenario('F')"
					role="tab"
					:aria-selected="scenario === 'F'"
				>
					<strong>F</strong>
					<span>B CTAs + green approve</span>
				</button>
				<button
					type="button"
					class="cp-scenario-btn"
					:class="{ 'is-active': scenario === 'G' }"
					@click="setScenario('G')"
					role="tab"
					:aria-selected="scenario === 'G'"
				>
					<strong>G</strong>
					<span>Current pill + F controls</span>
				</button>
				<button
					type="button"
					class="cp-scenario-btn"
					:class="{ 'is-active': scenario === 'H' }"
					@click="setScenario('H')"
					role="tab"
					:aria-selected="scenario === 'H'"
				>
					<strong>H</strong>
					<span>White pill (no wash)</span>
				</button>
				<button
					type="button"
					class="cp-scenario-btn"
					:class="{ 'is-active': scenario === 'I' }"
					@click="setScenario('I')"
					role="tab"
					:aria-selected="scenario === 'I'"
				>
					<strong>I</strong>
					<span>Blue wash pill; plain main card</span>
				</button>
				<button
					type="button"
					class="cp-scenario-btn"
					:class="{ 'is-active': scenario === 'J' }"
					@click="setScenario('J')"
					role="tab"
					:aria-selected="scenario === 'J'"
				>
					<strong>J</strong>
					<span>I + softer burgundy nav</span>
				</button>
				<button
					type="button"
					class="cp-scenario-btn"
					:class="{ 'is-active': scenario === 'K' }"
					@click="setScenario('K')"
					role="tab"
					:aria-selected="scenario === 'K'"
				>
					<strong>K</strong>
					<span>Slate nav + burgundy signal only</span>
				</button>
			</div>

			<div class="cp-scenario-notes" x-show="scenario === 'A'">
				<ul>
					<li>Pill = Record PO–style rich burgundy gradient</li>
					<li>White TRF + form text; glass status badge</li>
					<li>Deep burgundy sidebar (Slice 1)</li>
				</ul>
			</div>
			<div class="cp-scenario-notes" x-show="scenario === 'B'" x-cloak>
				<ul>
					<li>Pill = quote-params hero</li>
					<li>Acc / Sub badge CTAs</li>
					<li>Deep burgundy sidebar (same as A)</li>
				</ul>
			</div>
			<div class="cp-scenario-notes" x-show="scenario === 'C'" x-cloak>
				<ul>
					<li>Same blue pill + Acc/Sub CTAs as B</li>
					<li>Burgundy sidebar with stronger <code>#eff6ff</code> wash</li>
					<li>Blue-tinted glass on brand / active rows</li>
				</ul>
			</div>
			<div class="cp-scenario-notes" x-show="scenario === 'D'" x-cloak>
				<ul>
					<li>Same blue pill + Acc/Sub CTAs as B</li>
					<li>Colorless slate sidebar (<code>#1e293b → #0f172a</code>)</li>
					<li>Baby-blue active bar keeps the link to the pill</li>
				</ul>
			</div>
			<div class="cp-scenario-notes" x-show="scenario === 'E'" x-cloak>
				<ul>
					<li>Same blue pill + deep burgundy sidebar as B</li>
					<li>TRF / form / sep / status use B navy + Sub badge theme</li>
					<li>Doc icon, Record PO, and Actions use soft burgundy chips</li>
				</ul>
			</div>
			<div class="cp-scenario-notes" x-show="scenario === 'F'" x-cloak>
				<ul>
					<li>Pill + Record PO + Actions = Scenario B</li>
					<li>Green checkmark (shake) + badge <code>1</code> only when pending + approver role</li>
					<li>Dropdown: Approve / Reject — hidden if not awaiting approval</li>
				</ul>
			</div>
			<div class="cp-scenario-notes" x-show="scenario === 'G'" x-cloak>
				<ul>
					<li>Pill + TRF / form text match current request-view page (burgundy pill, white ink)</li>
					<li>Identity shows TRF · form only (matches current request-view white ink)</li>
					<li>Green check badge, Record PO, Actions keep F / B styling</li>
				</ul>
			</div>
			<div class="cp-scenario-notes" x-show="scenario === 'H'" x-cloak>
				<ul>
					<li>Pill = plain white (no burgundy wash)</li>
					<li>TRF / form = B navy; status + CTAs = soft burgundy</li>
					<li>Rail: burgundy wash only on Client info section</li>
					<li>Main card (right of rail) = plain white, no wash</li>
					<li>Green approve control kept from G</li>
				</ul>
			</div>
			<div class="cp-scenario-notes" x-show="scenario === 'I'" x-cloak>
				<ul>
					<li>Pill = Scenario B baby-blue + burgundy wash</li>
					<li>TRF / form / sep / status = Scenario B navy</li>
					<li>Main card (right of rail) = plain white, no burgundy wash</li>
					<li>Controls = G (green approve + Acc/Sub CTAs)</li>
				</ul>
			</div>
			<div class="cp-scenario-notes" x-show="scenario === 'J'" x-cloak>
				<ul>
					<li>Same chrome as I (blue wash pill, plain main card, Acc/Sub + green approve)</li>
					<li>Sidebar burgundy dialed down — less rich than Slice 1, tight gradient (52→56%)</li>
					<li>Subtle top wash; stops stay close so it doesn’t band</li>
				</ul>
			</div>
			<div class="cp-scenario-notes" x-show="scenario === 'K'" x-cloak>
				<ul>
					<li>Sidebar base is slate (<code>#1e293b → #0f172a</code>), same as D — no burgundy fill</li>
					<li>Faint burgundy-tinted top band (first ~24%) only, so the rail isn't flat slate</li>
					<li>Brand card (logo block) carries full burgundy — the one place identity is "loud"</li>
					<li>Active nav row = burgundy left-accent bar instead of blue, ties active state back to brand</li>
					<li>Hover/footer stay neutral glass — burgundy never repeats across every row</li>
					<li>Pill / CTAs reuse Scenario B (blue identity, Acc/Sub badges)</li>
				</ul>
			</div>

			<div class="cp-control-row">
				<button type="button" class="btn btn-sm btn-primary" @click="copySnapshot()">Copy snapshot</button>
			</div>

			<pre class="cp-snapshot" x-text="snapshot"></pre>
			<p class="cp-copied" x-show="copied" x-transition>Copied to clipboard</p>
		</aside>

		<section class="cp-stage" aria-label="Chrome mock">
			<div class="cp-stage-frame" :style="frameStyle" :class="'cp-scenario-' + scenario">
				<aside class="cp-sidebar" aria-label="Mock sidebar">
					<div class="cp-sidebar__brand">
						<i class="mdi mdi-flask cp-sidebar__flask" aria-hidden="true"></i>
						<span class="cp-sidebar__brand-label">Lab Management</span>
					</div>

					<nav class="cp-sidebar__nav">
						<a href="#" class="cp-sidebar__link" @click.prevent>
							<i class="mdi mdi-desktop-mac-dashboard" aria-hidden="true"></i>
							<span>Dashboard</span>
						</a>

						<a
							href="#cp-sw-menu"
							class="cp-sidebar__link"
							:class="{ 'is-open': sampleOpen, 'is-active': sampleOpen }"
							@click.prevent="sampleOpen = !sampleOpen"
							:aria-expanded="sampleOpen"
						>
							<i class="mdi mdi-file-document-edit-outline" aria-hidden="true"></i>
							<span>Sample Workflow</span>
							<i class="mdi submenu-chevron" :class="sampleOpen ? 'mdi-chevron-up' : 'mdi-chevron-down'" aria-hidden="true"></i>
						</a>
						<div class="cp-sidebar__submenu collapse" :class="{ show: sampleOpen }" id="cp-sw-menu">
							<a href="#" class="cp-sidebar__sublink is-active" @click.prevent>
								<i class="mdi mdi-circle-medium" aria-hidden="true"></i> Request view (mock)
							</a>
							<a href="#" class="cp-sidebar__sublink" @click.prevent>
								<i class="mdi mdi-circle-medium" aria-hidden="true"></i> Samples Reception
							</a>
						</div>

						<a
							href="#cp-bill-menu"
							class="cp-sidebar__link"
							:class="{ 'is-open': billingOpen }"
							@click.prevent="billingOpen = !billingOpen"
							:aria-expanded="billingOpen"
						>
							<i class="mdi mdi-cash-multiple" aria-hidden="true"></i>
							<span>Billing</span>
							<i class="mdi submenu-chevron" :class="billingOpen ? 'mdi-chevron-up' : 'mdi-chevron-down'" aria-hidden="true"></i>
						</a>
						<div class="cp-sidebar__submenu collapse" :class="{ show: billingOpen }" id="cp-bill-menu">
							<a href="#" class="cp-sidebar__sublink" @click.prevent>
								<i class="mdi mdi-circle-medium" aria-hidden="true"></i> Quotations
							</a>
							<a href="#" class="cp-sidebar__sublink" @click.prevent>
								<i class="mdi mdi-circle-medium" aria-hidden="true"></i> Invoices
							</a>
						</div>

						<a href="#" class="cp-sidebar__link" @click.prevent>
							<i class="mdi mdi-flask-outline" aria-hidden="true"></i>
							<span>Analytes</span>
						</a>
					</nav>

					<div class="cp-sidebar__foot">
						<button type="button" class="cp-sidebar__signout" disabled title="Mock only">
							<i class="mdi mdi-power" aria-hidden="true"></i> Sign out
						</button>
						<button type="button" class="cp-sidebar__switch" disabled title="Mock only — shell/modal next">
							<i class="mdi mdi-view-grid-plus-outline" aria-hidden="true"></i>
						</button>
					</div>
				</aside>

				<div class="cp-main">
					<div
						class="cp-pill"
						:class="{
							'cp-pill--blue': isBluePill() && !isCurrentRequestPill(),
							'cp-pill--burgundy': (!isBluePill() && !isPlainWhitePill() && !isBlueWashPill()) || isCurrentRequestPill(),
							'cp-pill--current': isCurrentRequestPill(),
							'cp-pill--plain-white': isPlainWhitePill(),
							'cp-pill--wash-blue': isBlueWashPill(),
						}"
					>
						<div class="cp-pill__stars" aria-hidden="true"></div>
						<div class="cp-pill__identity">
							<span class="cp-pill__trf" :style="{ color: trfColor }">TRFW008/26</span>
							<span class="cp-pill__sep" :style="{ color: sepColor }">·</span>
							<span class="cp-pill__form" :style="{ color: formColor }">Test Request Form - Water</span>
							<span
								class="cp-pill__stage"
								:class="{
									'cp-pill__stage--burgundy': isPlainWhitePill(),
									'cp-pill__stage--sub': usesNavyInk() && !isPlainWhitePill(),
								}"
								x-show="!isCurrentRequestPill()"
								x-text="isApproveControl() ? 'Awaiting quotation approval' : 'Quotation Accepted'"
							></span>
						</div>
						<div class="cp-pill__actions">
							<template x-if="isApproveControl()">
								<div class="cp-approve" @click.outside="approveOpen = false; rejectOpen = false">
									<button
										type="button"
										class="cp-approve__btn"
										@click="approveOpen = !approveOpen; rejectOpen = false"
										:aria-expanded="approveOpen"
										title="Quotation awaiting your approval"
									>
										<i class="mdi mdi-check-circle" aria-hidden="true"></i>
										<span class="cp-approve__badge">1</span>
									</button>
									<div class="cp-approve__menu" x-show="approveOpen" x-cloak>
										<div class="cp-approve__meta">QT-2026-008 · Pending your approval</div>
										<button type="button" class="cp-approve__item cp-approve__item--ok" @click="approveOpen = false">
											<i class="mdi mdi-check-decagram" aria-hidden="true"></i>
											Approve quotation
										</button>
										<button type="button" class="cp-approve__item cp-approve__item--danger" @click="rejectOpen = !rejectOpen">
											<i class="mdi mdi-close-circle-outline" aria-hidden="true"></i>
											Reject (reason required)
										</button>
										<div class="cp-approve__panel" x-show="rejectOpen" x-cloak>
											<label class="cp-approve__label">Rejection reason</label>
											<textarea class="cp-approve__textarea" rows="2" placeholder="Explain why this quotation is being returned"></textarea>
											<button type="button" class="cp-approve__confirm" @click="approveOpen = false; rejectOpen = false">Confirm reject</button>
										</div>
									</div>
								</div>
							</template>
							<button
								type="button"
								class="cp-pill__chip"
								:class="{
									'cp-badge--acc': usesAccSubCtas(),
									'cp-badge--burgundy-soft': isBurgundyInk(),
								}"
								x-show="!isApproveControl()"
								disabled
								title="Icon chip"
							>
								<i class="mdi mdi-file-document-outline" aria-hidden="true"></i>
							</button>
							<button
								type="button"
								class="cp-pill__cta"
								:class="{
									'cp-badge--sub': usesAccSubCtas(),
									'cp-badge--burgundy': isBurgundyInk(),
								}"
								disabled
							>
								<i class="mdi mdi-shopping-outline" aria-hidden="true"></i>
								Record PO
							</button>
							<div class="cp-pill__dropdown">
								<button
									type="button"
									class="cp-pill__chip"
									:class="{
										'cp-badge--acc': usesAccSubCtas(),
										'cp-badge--burgundy-soft': isBurgundyInk(),
									}"
									@click="actionsOpen = !actionsOpen"
									:aria-expanded="actionsOpen"
								>
									… Actions
									<i class="mdi mdi-chevron-down" aria-hidden="true"></i>
								</button>
								<div class="cp-pill__menu" x-show="actionsOpen" x-cloak @click.outside="actionsOpen = false">
									<button type="button" class="cp-pill__menu-item" :class="isBurgundyInk() ? 'cp-badge--burgundy-text' : (usesAccSubCtas() ? 'cp-badge--sub-text' : '')">
										<i class="mdi mdi-file-chart-outline" aria-hidden="true"></i> Process enquiry
									</button>
									<button type="button" class="cp-pill__menu-item" :class="isBurgundyInk() ? 'cp-badge--burgundy-text' : (usesAccSubCtas() ? 'cp-badge--acc-text' : '')">
										<i class="mdi mdi-check-decagram" aria-hidden="true"></i> Accept quotation
									</button>
									<button type="button" class="cp-pill__menu-item" :class="isBurgundyInk() ? 'cp-badge--burgundy-text' : ''">
										<i class="mdi mdi-package-down" aria-hidden="true"></i> Receive samples
									</button>
								</div>
							</div>
						</div>
					</div>

					<div class="cp-console">
						<aside
							class="cp-rail"
							:class="{ 'cp-rail--section-wash': isPlainWhitePill() }"
						>
							<section class="cp-rail__section cp-rail__section--client">
								<div class="cp-rail__heading">
									<h3 class="cp-rail__title cp-rail__title--client">
										<i class="mdi mdi-account-tie-outline" aria-hidden="true"></i>
										Client info
									</h3>
									<div class="cp-rail__icons">
										<button type="button" class="cp-rail__icon-btn" disabled aria-label="View"><i class="mdi mdi-eye-outline"></i></button>
										<button type="button" class="cp-rail__icon-btn" disabled aria-label="Edit"><i class="mdi mdi-pencil-outline"></i></button>
									</div>
								</div>
								<dl class="cp-rail__fields">
									<div class="cp-rail__field">
										<dt>Client</dt>
										<dd>Amspec Santos</dd>
									</div>
									<div class="cp-rail__field">
										<dt>Company unit</dt>
										<dd>Santos Beef</dd>
									</div>
									<div class="cp-rail__field">
										<dt>Contact person</dt>
										<dd>
											ERNEST KAMAU
											<span class="cp-rail__email">
												<i class="mdi mdi-email-outline" aria-hidden="true"></i>
												ernest@example.com
											</span>
										</dd>
									</div>
								</dl>
							</section>
							<section class="cp-rail__section">
								<h3 class="cp-rail__title">
									<i class="mdi mdi-file-document-multiple-outline" aria-hidden="true"></i>
									Documents
								</h3>
								<div class="cp-rail__doc">TRF PDF</div>
								<div class="cp-rail__doc">Quotation</div>
							</section>
							<section class="cp-rail__section">
								<h3 class="cp-rail__title">
									<i class="mdi mdi-file-document-outline" aria-hidden="true"></i>
									References
								</h3>
								<p class="cp-rail__empty">No job / sample refs yet</p>
							</section>
						</aside>

						<div class="cp-canvas">
							<div class="cp-tabs">
								<span class="cp-tab is-active">Tests (1)</span>
								<span class="cp-tab">Sample collection (8)</span>
								<span class="cp-tab">Notes (0)</span>
								<span class="cp-tab">Attachments (2)</span>
							</div>
							<div class="cp-canvas__body">
								<p class="mb-2 small font-weight-bold" style="color:#1e3a8a; letter-spacing:0.03em; text-transform:uppercase;">
									Sample collection data
								</p>
								<p class="mb-0 text-muted small">
									Canvas placeholder. Compare C (blue wash) vs D (slate) sidebars with the same blue pill.
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
</main>
@endsection
