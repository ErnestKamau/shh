<div class="nav-item dropdown imara-system-bell"
     x-data="{ open: @entangle('open') }"
     @click.outside="open = false">
    <style>
        .imara-system-bell .imara-system-bell__btn {
            position: relative;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 8px;
            border: 1px solid #dbe5f0;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #475569;
        }
        .imara-system-bell .imara-system-bell__btn.is-ringing .mdi {
            animation: imara-system-bell-ring 1.1s ease-in-out infinite;
            transform-origin: top center;
            color: var(--workflow-accent, #7a1f3d);
        }
        @keyframes imara-system-bell-ring {
            0%, 100% { transform: rotate(0deg); }
            10% { transform: rotate(14deg); }
            20% { transform: rotate(-12deg); }
            30% { transform: rotate(10deg); }
            40% { transform: rotate(-8deg); }
            50% { transform: rotate(4deg); }
            60% { transform: rotate(0deg); }
        }
        .imara-system-bell .imara-system-bell__badge {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 1.1rem;
            height: 1.1rem;
            padding: 0 0.28rem;
            border-radius: 999px;
            background: var(--workflow-accent, #7a1f3d);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            line-height: 1.1rem;
            text-align: center;
        }
        .imara-system-bell .imara-system-bell__menu {
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 0.4rem;
            width: min(22rem, calc(100vw - 2rem));
            background: #fff;
            border: 1px solid #dbe5f0;
            border-radius: 12px;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.14);
            z-index: 1080;
            overflow: hidden;
        }
        .imara-system-bell .imara-system-bell__header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0.9rem;
            border-bottom: 1px solid #e8eef4;
        }
        .imara-system-bell .imara-system-bell__header strong {
            color: #1e3a8a;
            font-size: 0.85rem;
        }
        .imara-system-bell .imara-system-bell__item {
            display: block;
            width: 100%;
            text-align: left;
            border: 0;
            background: transparent;
            padding: 0.75rem 0.9rem;
            border-bottom: 1px solid #f1f5f9;
            cursor: pointer;
        }
        .imara-system-bell .imara-system-bell__item:hover {
            background: #f8fafc;
        }
        .imara-system-bell .imara-system-bell__item-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: #0f172a;
            margin: 0 0 0.15rem;
        }
        .imara-system-bell .imara-system-bell__item-msg {
            font-size: 0.75rem;
            color: #64748b;
            margin: 0;
        }
        .imara-system-bell .imara-system-bell__empty {
            padding: 1.25rem 0.9rem;
            text-align: center;
            color: #64748b;
            font-size: 0.8rem;
        }
    </style>

    <button type="button"
        class="imara-system-bell__btn {{ $unreadCount > 0 ? 'is-ringing' : '' }}"
        @click="open = !open"
        :aria-expanded="open"
        title="Notifications"
        aria-label="Notifications">
        <i class="mdi {{ $unreadCount > 0 ? 'mdi-bell-ring' : 'mdi-bell-outline' }}" aria-hidden="true"></i>
        @if($unreadCount > 0)
            <span class="imara-system-bell__badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </button>

    <div class="imara-system-bell__menu" x-show="open" x-cloak @click.stop>
        <div class="imara-system-bell__header">
            <strong>Notifications</strong>
            @if($unreadCount > 0)
                <button type="button" class="btn btn-link btn-sm p-0" wire:click="markAllRead">Mark all read</button>
            @endif
        </div>

        @forelse($notifications as $notification)
            <button type="button"
                class="imara-system-bell__item"
                wire:click="openNotification('{{ $notification->id }}')">
                <p class="imara-system-bell__item-title">{{ $notification->title }}</p>
                <p class="imara-system-bell__item-msg">{{ $notification->message }}</p>
            </button>
        @empty
            <div class="imara-system-bell__empty">No notifications waiting for you.</div>
        @endforelse
    </div>
</div>
