{{-- Toast variant demos — wire to window.showImaraToast({ type, title, message, variant }) --}}
<div class="ls-toast-gallery">
	<p class="small text-muted mb-3">Four motion + icon variants for TRF, direct registration, and request view. Click to preview.</p>
	<div class="ls-toast-gallery__grid">
		<button type="button" class="ls-toast-gallery__demo ls-toast-gallery__demo--celebrate" @click="showImaraToast({ type: 'success', title: 'Request saved', message: 'Test request form submitted successfully.', variant: 'celebrate' })">
			<i class="mdi mdi-party-popper" aria-hidden="true"></i>
			<span>Celebrate</span>
			<small>Bounce in · success</small>
		</button>
		<button type="button" class="ls-toast-gallery__demo ls-toast-gallery__demo--shake" @click="showImaraToast({ type: 'error', title: 'Validation failed', message: 'Complete required fields in Customer details.', variant: 'shake' })">
			<i class="mdi mdi-alert-octagon-outline" aria-hidden="true"></i>
			<span>Shake</span>
			<small>Shake enter · error</small>
		</button>
		<button type="button" class="ls-toast-gallery__demo ls-toast-gallery__demo--pulse" @click="showImaraToast({ type: 'warning', title: 'Check sampling date', message: 'Sampling date is in the future.', variant: 'pulse' })">
			<i class="mdi mdi-shield-alert-outline" aria-hidden="true"></i>
			<span>Pulse</span>
			<small>Ring pulse · warning</small>
		</button>
		<button type="button" class="ls-toast-gallery__demo ls-toast-gallery__demo--glass" @click="showImaraToast({ type: 'info', title: 'Tests updated', message: '52 tests loaded for this sample type.', variant: 'glass' })">
			<i class="mdi mdi-information-outline" aria-hidden="true"></i>
			<span>Glass slide</span>
			<small>Slide + blur · info</small>
		</button>
	</div>
</div>
