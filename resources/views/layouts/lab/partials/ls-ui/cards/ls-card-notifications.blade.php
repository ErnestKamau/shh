{{--
	ls-card-notifications — notification center card with segmented tabs.

	Props:
	- $title (string)
	- $notificationGroups (optional array{today, week, earlier} of LabUserNotification collections)
	  When omitted, demo content is shown (gallery).
	- $interactive (bool) — wire:click open + mark-all-read when true
	- $unreadCount (int)
--}}
@php
	$interactive = (bool) ($interactive ?? false);
	$unreadCount = (int) ($unreadCount ?? 0);
	$hasLiveGroups = isset($notificationGroups) && is_array($notificationGroups);
	$groups = $hasLiveGroups ? $notificationGroups : null;
	$notifyIcons = app(\App\Services\Lab\LabSystemNotificationService::class);
	$defaultTab = 'today';
	if ($hasLiveGroups) {
		if (($groups['today'] ?? collect())->isEmpty() && ($groups['week'] ?? collect())->isNotEmpty()) {
			$defaultTab = 'week';
		} elseif (
			($groups['today'] ?? collect())->isEmpty()
			&& ($groups['week'] ?? collect())->isEmpty()
			&& ($groups['earlier'] ?? collect())->isNotEmpty()
		) {
			$defaultTab = 'earlier';
		}
	}
@endphp
<div class="ls-card-notify" x-data="{ tab: '{{ $defaultTab }}' }">
	<div class="ls-card-notify__head">
		<h4 class="ls-card-notify__title">{{ $title ?? 'Lab Notification Center' }}</h4>
		@if($interactive)
			@if($unreadCount > 0)
				<button type="button" class="ls-card-notify__see" wire:click="markAllRead">Mark all read</button>
			@endif
		@else
			<a href="#" class="ls-card-notify__see" onclick="return false;">See All</a>
		@endif
	</div>
	<div class="ls-seg" role="tablist">
		<button type="button" :class="{ 'is-active': tab === 'today' }" @click="tab = 'today'">Today</button>
		<button type="button" :class="{ 'is-active': tab === 'week' }" @click="tab = 'week'">This Week</button>
		<button type="button" :class="{ 'is-active': tab === 'earlier' }" @click="tab = 'earlier'">Earlier</button>
	</div>

	@if($hasLiveGroups)
		@foreach(['today', 'week', 'earlier'] as $tabKey)
			<div x-show="tab === '{{ $tabKey }}'" @if($tabKey !== $defaultTab) x-cloak @endif>
				@forelse(($groups[$tabKey] ?? collect()) as $notification)
					@php
						$icon = $notifyIcons->iconForType($notification->notification_type ?? null);
						$isUnread = ! (bool) ($notification->is_read ?? false);
						$meta = $notification->created_at?->diffForHumans(short: true) ?? '';
					@endphp
					@if($interactive)
						<button type="button"
							class="ls-notify-item ls-notify-item--action {{ $isUnread ? 'is-unread' : '' }}"
							wire:key="ls-notify-{{ $notification->id }}"
							wire:click="openNotification('{{ $notification->id }}')">
							<span class="ls-notify-item__icon"><i class="mdi {{ $icon }}" aria-hidden="true"></i></span>
							<div>
								<p class="ls-notify-item__title">
									{{ $notification->title }}
									@if($meta !== '')
										<span class="ls-notify-item__meta">{{ $meta }}</span>
									@endif
								</p>
								<p class="ls-notify-item__body">{{ $notification->message }}</p>
							</div>
						</button>
					@else
						<div class="ls-notify-item {{ $isUnread ? 'is-unread' : '' }}" wire:key="ls-notify-{{ $notification->id }}">
							<span class="ls-notify-item__icon"><i class="mdi {{ $icon }}" aria-hidden="true"></i></span>
							<div>
								<p class="ls-notify-item__title">
									{{ $notification->title }}
									@if($meta !== '')
										<span class="ls-notify-item__meta">{{ $meta }}</span>
									@endif
								</p>
								<p class="ls-notify-item__body">{{ $notification->message }}</p>
							</div>
						</div>
					@endif
				@empty
					<div class="ls-notify-empty">No notifications in this period.</div>
				@endforelse
			</div>
		@endforeach
	@else
		{{-- Gallery / demo content --}}
		<div x-show="tab === 'today'">
			<div class="ls-notify-item is-unread">
				<span class="ls-notify-item__icon"><i class="mdi mdi-lightbulb-outline"></i></span>
				<div>
					<p class="ls-notify-item__title">Quotation ready for review <span class="ls-notify-item__meta">1h ago</span></p>
					<p class="ls-notify-item__body">TRFF038/26 awaiting customer approval.</p>
				</div>
			</div>
			<div class="ls-notify-item is-unread">
				<span class="ls-notify-item__icon"><i class="mdi mdi-chart-line"></i></span>
				<div>
					<p class="ls-notify-item__title">Integrity check queued <span class="ls-notify-item__meta">3h ago</span></p>
					<p class="ls-notify-item__body">4 samples moved to Sample Integrity Check.</p>
				</div>
			</div>
			<div class="ls-notify-item is-unread">
				<span class="ls-notify-item__icon"><i class="mdi mdi-wrench-outline"></i></span>
				<div>
					<p class="ls-notify-item__title">Instrument calibration due <span class="ls-notify-item__meta">5h ago</span></p>
					<p class="ls-notify-item__body">HPLC-02 scheduled for next shift.</p>
				</div>
			</div>
		</div>
		<div x-show="tab === 'week'" x-cloak>
			<div class="ls-notify-item is-unread">
				<span class="ls-notify-item__icon"><i class="mdi mdi-email-outline"></i></span>
				<div>
					<p class="ls-notify-item__title">Customer reply received <span class="ls-notify-item__meta">2d ago</span></p>
					<p class="ls-notify-item__body">Additional info provided for TRFW008/26.</p>
				</div>
			</div>
		</div>
		<div x-show="tab === 'earlier'" x-cloak>
			<div class="ls-notify-item">
				<span class="ls-notify-item__icon"><i class="mdi mdi-check-circle-outline"></i></span>
				<div>
					<p class="ls-notify-item__title">Batch released <span class="ls-notify-item__meta">Last week</span></p>
					<p class="ls-notify-item__body">12 samples completed report approval.</p>
				</div>
			</div>
		</div>
	@endif
</div>
