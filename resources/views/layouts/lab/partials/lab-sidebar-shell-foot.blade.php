{{-- Lab sidebar shell foot — Sign out + Switch module (keeps existing nav collapse intact) --}}
<div class="lab-sidebar-foot" aria-label="Sidebar actions">
	<div class="lab-sidebar-foot__actions">
		<button
			type="button"
			class="lab-sidebar-signout"
			onclick="(function(){ var f = document.getElementById('logout-form'); if (f) { f.submit(); } else { window.location.href = '{{ url('/logout') }}'; } })();"
		>
			<i class="mdi mdi-power" aria-hidden="true"></i>
			<span>Sign out</span>
		</button>
		<button
			type="button"
			class="lab-sidebar-switch"
			data-open-module-switcher
			aria-label="Switch module"
			title="Switch module"
		>
			<i class="mdi mdi-view-grid-plus-outline" aria-hidden="true"></i>
			<span class="lab-sidebar-switch__tip">Switch module</span>
		</button>
	</div>
	<div class="lab-sidebar-foot__copy copyright-lims">
		Copyright {{ date('Y') }} <span class="text-red">Imara LIMS</span>
	</div>
</div>
