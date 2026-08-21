{{--
	ls-hierarchy-card — avatar, bold title, muted meta, body, footer actions.
	Props: $initial, $title, $meta, $body, $likes
--}}
<div class="ls-hierarchy-card">
	<div class="ls-hierarchy-card__top">
		<span class="ls-hierarchy-card__avatar">{{ $initial ?? 'C' }}</span>
		<div>
			<span class="ls-hierarchy-card__title">{{ $title ?? 'Charlie George' }}</span>
			<span class="ls-hierarchy-card__meta">{{ $meta ?? '4h ago' }}</span>
		</div>
	</div>
	<p class="ls-hierarchy-card__body">
		{{ $body ?? 'Clear hierarchy: bold name, muted timestamp, readable body, quieter footer actions.' }}
	</p>
	<div class="ls-hierarchy-card__footer">
		<span><i class="mdi mdi-thumb-up-outline"></i> {{ $likes ?? 34 }}</span>
		<span>Reply</span>
	</div>
</div>
