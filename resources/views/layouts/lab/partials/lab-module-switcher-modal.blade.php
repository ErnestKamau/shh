{{--
	Lab module switcher modal — keep module list in sync with resources/views/home.blade.php
	Opened by [data-open-module-switcher] on the lab sidebar foot.
--}}
@auth
<div id="lab-module-switcher-modal" class="lab-msw-overlay" role="dialog" aria-modal="true" aria-labelledby="lab-msw-title" hidden>
	<div class="lab-msw-dialog" role="document">
		<div class="lab-msw-head">
			<div>
				<h2 id="lab-msw-title" class="lab-msw-title">Switch module</h2>
				<p class="lab-msw-subtitle">Jump to another workspace</p>
			</div>
			<button type="button" class="lab-msw-close" data-lab-msw-close aria-label="Close">
				<i class="mdi mdi-close" aria-hidden="true"></i>
			</button>
		</div>

		<div class="lab-msw-grid">
			@if(isSystemModuleVisible('laboratory'))
				@can('laboratory.module.access')
				<a class="lab-msw-card" href="{{ route('dashboard-lab') }}">
					<span class="lab-msw-icon" style="background: linear-gradient(135deg, #4CAF50, #45a049);"><i class="mdi mdi-flask"></i></span>
					<span class="lab-msw-label">Laboratory</span>
				</a>
				@endcan
			@endif

			@if(isSystemModuleVisible('inventory'))
				@can('inventory.module.access')
				<a class="lab-msw-card" href="/inventory-home">
					<span class="lab-msw-icon" style="background: linear-gradient(135deg, #2196F3, #1976D2);"><i class="mdi mdi-package-variant"></i></span>
					<span class="lab-msw-label">Inventory</span>
				</a>
				@endcan
			@endif

			@if(isSystemModuleVisible('equipment'))
				@can('equipment.module.access')
				<a class="lab-msw-card" href="{{ route('equipment-dashboard') }}">
					<span class="lab-msw-icon" style="background: linear-gradient(135deg, #795548, #5D4037);"><i class="mdi mdi-tools"></i></span>
					<span class="lab-msw-label">Equipment</span>
				</a>
				@endcan
			@endif

			@if(isSystemModuleVisible('crm'))
				@can('crm.module.access')
				<a class="lab-msw-card" href="{{ route('crm-dashboard') }}">
					<span class="lab-msw-icon" style="background: linear-gradient(135deg, #00BCD4, #0097A7);"><i class="mdi mdi-account-multiple-outline"></i></span>
					<span class="lab-msw-label">CRM</span>
				</a>
				@endcan
			@endif

			@if(isSystemModuleVisible('personnel'))
				@can('personnel.module.access')
				<a class="lab-msw-card" href="/personnel-home">
					<span class="lab-msw-icon" style="background: linear-gradient(135deg, #F44336, #D32F2F);"><i class="mdi mdi-account-group"></i></span>
					<span class="lab-msw-label">Personnel</span>
				</a>
				@endcan
			@endif

			@if(isSystemModuleVisible('dms'))
				@can('dms.module.access')
				<a class="lab-msw-card" href="{{ route('dms.dashboard') }}">
					<span class="lab-msw-icon" style="background: linear-gradient(135deg, #673AB7, #512DA8);"><i class="mdi mdi-file-document-multiple"></i></span>
					<span class="lab-msw-label">Document Management</span>
				</a>
				@endcan
			@endif

			@if(isSystemModuleVisible('calendar'))
				@can('calendar.module.access')
				<a class="lab-msw-card" href="{{ route('system-planner.dashboard') }}">
					<span class="lab-msw-icon" style="background: linear-gradient(135deg, #FFC107, #FF8F00);"><i class="mdi mdi-calendar"></i></span>
					<span class="lab-msw-label">System Planner</span>
				</a>
				@endcan
			@endif

			@if(isSystemModuleVisible('matrix'))
				@can('matrix.module.access')
				<a class="lab-msw-card" href="{{ route('matrix.dashboard') }}">
					<span class="lab-msw-icon" style="background: linear-gradient(135deg, #9E9E9E, #616161);"><i class="mdi mdi-account-star-outline"></i></span>
					<span class="lab-msw-label">Skills Matrix</span>
				</a>
				@endcan
			@endif

			@if(isSystemModuleVisible('ai'))
				@can('ai.module.access')
				<a class="lab-msw-card" href="{{ route('imara-ai') }}">
					<span class="lab-msw-icon" style="background: linear-gradient(135deg, #FF9800, #F57C00);"><i class="mdi mdi-chip"></i></span>
					<span class="lab-msw-label">ImaraChat AI</span>
				</a>
				@endcan
			@endif

			@if(isSystemModuleVisible('ai_analytics'))
				@can('ai_analytics.module.access')
				<a class="lab-msw-card" href="{{ Route::has('mas.index') ? route('mas.index') : url('/mas') }}">
					<span class="lab-msw-icon" style="background: linear-gradient(135deg, #3F51B5, #1A237E);"><i class="mdi mdi-chart-line"></i></span>
					<span class="lab-msw-label">AI Analytics</span>
				</a>
				@endcan
			@endif

			@if(isSystemModuleVisible('registry'))
				@can('registry.module.access')
				<a class="lab-msw-card" href="{{ route('registry.dashboard') }}">
					<span class="lab-msw-icon" style="background: linear-gradient(135deg, #1565C0, #0D47A1);"><i class="mdi mdi-email-multiple-outline"></i></span>
					<span class="lab-msw-label">Registry</span>
				</a>
				@endcan
			@endif

			@if(isSystemModuleVisible('risk'))
				@can('risk.module.access')
				<a class="lab-msw-card" href="{{ route('risk.dashboard') }}">
					<span class="lab-msw-icon" style="background: linear-gradient(135deg, #F44336, #D32F2F);"><i class="mdi mdi-alert-octagon-outline"></i></span>
					<span class="lab-msw-label">Risk Management</span>
				</a>
				@endcan
			@endif

			@if(isSystemModuleVisible('audit'))
				@can('audit.module.access')
				<a class="lab-msw-card" href="{{ route('audit.dashboard') }}">
					<span class="lab-msw-icon" style="background: linear-gradient(135deg, #9C27B0, #7B1FA2);"><i class="mdi mdi-clipboard-check-outline"></i></span>
					<span class="lab-msw-label">Audit</span>
				</a>
				@endcan
			@endif

			@if(isSystemModuleVisible('tickets'))
				@can('tickets.module.access')
				<a class="lab-msw-card" href="{{ route('tickets.dashboard') }}">
					<span class="lab-msw-icon" style="background: linear-gradient(135deg, #E91E63, #C2185B);"><i class="mdi mdi-ticket"></i></span>
					<span class="lab-msw-label">Help Desk</span>
				</a>
				@endcan
			@endif

			@if(isSystemModuleVisible('settings'))
				@can('settings.module.access')
				<a class="lab-msw-card" href="/system-settings">
					<span class="lab-msw-icon" style="background: linear-gradient(135deg, #424242, #212121);"><i class="fas fa-cogs"></i></span>
					<span class="lab-msw-label">System Settings</span>
				</a>
				@endcan
			@endif

			<a class="lab-msw-card" href="{{ route('home') }}">
				<span class="lab-msw-icon" style="background: linear-gradient(135deg, #6d0a0e, #3b0a0e);"><i class="mdi mdi-view-grid"></i></span>
				<span class="lab-msw-label">All Apps</span>
			</a>
		</div>
	</div>
</div>

<style>
	.lab-msw-overlay {
		position: fixed;
		inset: 0;
		z-index: 2000;
		display: flex;
		align-items: center;
		justify-content: center;
		padding: 24px;
		background: rgba(15, 23, 42, 0.62);
		backdrop-filter: blur(4px);
		-webkit-backdrop-filter: blur(4px);
		opacity: 0;
		transition: opacity 0.18s ease;
	}
	.lab-msw-overlay[hidden] { display: none; }
	.lab-msw-overlay.lab-msw-open { opacity: 1; }

	.lab-msw-dialog {
		width: 100%;
		max-width: 640px;
		max-height: 85vh;
		overflow-y: auto;
		background: #fff;
		border: 1px solid #e2e8f0;
		border-radius: 14px;
		box-shadow: 0 24px 60px rgba(15, 23, 42, 0.28);
		transform: translateY(10px) scale(0.985);
		transition: transform 0.18s ease;
	}
	.lab-msw-overlay.lab-msw-open .lab-msw-dialog { transform: none; }

	.lab-msw-head {
		display: flex;
		align-items: flex-start;
		justify-content: space-between;
		padding: 1.1rem 1.25rem 0.85rem;
		border-bottom: 1px solid #e8eef4;
	}
	.lab-msw-title {
		margin: 0;
		color: #0f172a;
		font-size: 1.05rem;
		font-weight: 700;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
	}
	.lab-msw-subtitle {
		margin: 0.15rem 0 0;
		color: #64748b;
		font-size: 0.78rem;
	}
	.lab-msw-close {
		border: 1px solid #e2e8f0;
		background: #f8fafc;
		color: #475569;
		width: 34px;
		height: 34px;
		border-radius: 8px;
		font-size: 1.1rem;
		cursor: pointer;
		line-height: 1;
	}
	.lab-msw-close:hover {
		background: #eff6ff;
		color: #1e3a8a;
		border-color: #bfdbfe;
	}

	.lab-msw-grid {
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
		gap: 0.75rem;
		padding: 1.1rem 1.25rem 1.35rem;
	}
	.lab-msw-card {
		display: flex;
		flex-direction: column;
		align-items: center;
		gap: 0.55rem;
		padding: 0.9rem 0.55rem;
		border-radius: 12px;
		border: 1px solid #e2e8f0;
		background: #f8fafc;
		color: #0f172a;
		text-decoration: none;
		text-align: center;
		transition: transform 0.15s ease, border-color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
	}
	.lab-msw-card:hover {
		transform: translateY(-2px);
		border-color: #93c5fd;
		background: #eff6ff;
		color: #1e3a8a;
		box-shadow: 0 8px 18px rgba(59, 130, 246, 0.12);
		text-decoration: none;
	}
	.lab-msw-icon {
		width: 2.6rem;
		height: 2.6rem;
		border-radius: 10px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		color: #fff;
		font-size: 1.25rem;
	}
	.lab-msw-label {
		font-size: 0.72rem;
		font-weight: 600;
		line-height: 1.25;
		font-family: var(--ls-font-ui, "IBM Plex Sans", system-ui, sans-serif);
	}

	@media (max-width: 767.98px) {
		.lab-msw-dialog {
			width: calc(100vw - 1rem);
			max-height: calc(100vh - 1rem);
			margin: 0.5rem;
			border-radius: 14px;
		}

		.lab-msw-grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
			gap: 0.55rem;
			padding: 0.85rem;
			overflow-y: auto;
			-webkit-overflow-scrolling: touch;
		}

		.lab-msw-card {
			min-height: var(--touch-min, 44px);
			padding: 0.85rem 0.45rem;
		}

		.lab-msw-close {
			width: var(--touch-min, 44px);
			height: var(--touch-min, 44px);
		}
	}
</style>

<script>
	(function () {
		function bindLabModuleSwitcher() {
			var modal = document.getElementById('lab-module-switcher-modal');
			if (!modal || modal.dataset.bound === '1') {
				return;
			}
			modal.dataset.bound = '1';

			function openModal() {
				modal.hidden = false;
				requestAnimationFrame(function () {
					modal.classList.add('lab-msw-open');
				});
				document.body.style.overflow = 'hidden';
			}

			function closeModal() {
				modal.classList.remove('lab-msw-open');
				setTimeout(function () {
					modal.hidden = true;
					document.body.style.removeProperty('overflow');
				}, 180);
			}

			document.addEventListener('click', function (e) {
				if (e.target.closest('[data-open-module-switcher]')) {
					e.preventDefault();
					openModal();
					return;
				}
				if (e.target.closest('[data-lab-msw-close]')) {
					e.preventDefault();
					closeModal();
					return;
				}
				if (e.target === modal) {
					closeModal();
				}
			});

			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape' && !modal.hidden) {
					closeModal();
				}
			});
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', bindLabModuleSwitcher);
		} else {
			bindLabModuleSwitcher();
		}
	})();
</script>
@endauth
