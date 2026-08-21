{{--
	ls-card-collection — settings / contribution card (New Collection style).
--}}
<div class="ls-card-collection" x-data="{ privateOn: true, contrib: 'open' }">
	<div class="ls-card-collection__head">
		<div>
			<h4 class="ls-card-collection__title">{{ $title ?? 'New Collection' }}</h4>
		</div>
		<button type="button" class="ls-card-collection__close" aria-label="Close"><i class="mdi mdi-close"></i></button>
	</div>
	<div class="ls-card-collection__row">
		<div>
			<p class="ls-card-collection__row-title">Private</p>
			<p class="ls-card-collection__row-sub">Only viewable by people you invite.</p>
		</div>
		<button type="button" class="ls-toggle" :class="{ 'is-on': privateOn }" @click="privateOn = !privateOn" :aria-pressed="privateOn"></button>
	</div>
	<p class="ls-card-collection__section">Contribution Settings</p>
	<button type="button" class="ls-choice-card" :class="{ 'is-selected': contrib === 'controlled' }" @click="contrib = 'controlled'">
		<p class="ls-choice-card__title">Controlled</p>
		<p class="ls-choice-card__body">Only allow members you've set as contributors to add their content.</p>
	</button>
	<button type="button" class="ls-choice-card" :class="{ 'is-selected': contrib === 'open' }" @click="contrib = 'open'">
		<p class="ls-choice-card__title">Open</p>
		<p class="ls-choice-card__body">Allow all members to add content.</p>
	</button>
	<div class="ls-card-collection__foot">
		<button type="button" class="ls-btn">Cancel</button>
		<button type="button" class="ls-btn ls-btn--primary">Create Collection</button>
	</div>
</div>
