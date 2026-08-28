<div class="nav-item dropdown imara-system-bell"
     wire:poll.30s
     x-data="{ open: @entangle('open') }"
     @click.outside="open = false">
    <style>
        .imara-system-bell {
            --ls-ink: var(--workflow-secondary, #1e293b);
            --ls-muted: var(--workflow-muted, #64748b);
            --ls-border: var(--workflow-border, #e2e8f0);
            --ls-accent: var(--workflow-accent, #8b1e2d);
        }
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
        .imara-system-bell .imara-system-bell__btn.is-alerting {
            border-color: color-mix(in srgb, var(--workflow-accent, #c41e3a) 35%, #dbe5f0);
            background: color-mix(in srgb, var(--workflow-accent, #c41e3a) 8%, #fff);
        }
        .imara-system-bell .imara-system-bell__btn.is-alerting .mdi {
            color: var(--workflow-accent, #c41e3a);
            animation: imara-system-bell-ring 1.1s ease-in-out infinite;
            transform-origin: top center;
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
        @keyframes imara-system-bell-badge-vibrate {
            0%, 100% { transform: translate(0, 0) scale(1); }
            15% { transform: translate(-1px, -1px) scale(1.08); }
            30% { transform: translate(1px, 1px) scale(1.12); }
            45% { transform: translate(-1px, 1px) scale(1.08); }
            60% { transform: translate(1px, -1px) scale(1.1); }
            75% { transform: translate(0, 0) scale(1.05); }
        }
        .imara-system-bell .imara-system-bell__badge {
            position: absolute;
            top: -6px;
            right: -6px;
            min-width: 1.2rem;
            height: 1.2rem;
            padding: 0 0.32rem;
            border-radius: 999px;
            background: var(--workflow-accent, #c41e3a);
            color: #fff;
            font-size: 0.68rem;
            font-weight: 700;
            line-height: 1.2rem;
            text-align: center;
            border: 2px solid #fff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.2);
        }
        .imara-system-bell .imara-system-bell__btn.is-alerting .imara-system-bell__badge {
            animation: imara-system-bell-badge-vibrate 0.9s ease-in-out infinite;
        }
        .imara-system-bell .imara-system-bell__menu {
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 0.4rem;
            width: min(26rem, calc(100vw - 2rem));
            background: transparent;
            border: 0;
            box-shadow: none;
            z-index: 1080;
            overflow: visible;
        }
        .imara-system-bell .imara-system-bell__menu .ls-card-notify {
            max-width: none;
            width: 100%;
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.14);
        }
        .imara-system-bell .ls-card-notify {
            border: 1px solid var(--ls-border);
            border-radius: 16px;
            background: #fff;
            padding: 1rem;
        }
        .imara-system-bell .ls-card-notify__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }
        .imara-system-bell .ls-card-notify__title {
            font-weight: 700;
            font-size: 0.9rem;
            margin: 0;
            color: var(--ls-ink);
        }
        .imara-system-bell .ls-card-notify__see {
            font-size: 0.72rem;
            color: var(--ls-muted);
            text-decoration: none;
        }
        .imara-system-bell .ls-seg {
            display: flex;
            background: #f1f5f9;
            border-radius: 999px;
            padding: 0.2rem;
            margin-bottom: 0.85rem;
        }
        .imara-system-bell .ls-seg button {
            flex: 1;
            border: none;
            background: transparent;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--ls-muted);
            padding: 0.35rem 0.5rem;
            cursor: pointer;
        }
        .imara-system-bell .ls-seg button.is-active {
            background: #fff;
            color: var(--ls-ink);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        }
        .imara-system-bell .ls-notify-item {
            display: flex;
            gap: 0.65rem;
            padding: 0.55rem 0;
            border-bottom: 1px solid #f1f5f9;
            width: 100%;
            text-align: left;
        }
        .imara-system-bell .ls-notify-item:last-child {
            border-bottom: none;
        }
        .imara-system-bell .ls-notify-item--action {
            border-left: 0;
            border-right: 0;
            border-top: 0;
            background: transparent;
            cursor: pointer;
            font: inherit;
            color: inherit;
            border-radius: 0;
        }
        .imara-system-bell .ls-notify-item--action:hover {
            background: #f8fafc;
        }
        .imara-system-bell .ls-notify-item__icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #f1f5f9;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--ls-muted);
            flex-shrink: 0;
        }
        .imara-system-bell .ls-notify-item__title {
            font-size: 0.78rem;
            font-weight: 700;
            margin: 0;
            color: var(--ls-ink);
        }
        .imara-system-bell .ls-notify-item.is-unread .ls-notify-item__title::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--ls-accent);
            margin-right: 0.35rem;
            vertical-align: middle;
        }
        .imara-system-bell .ls-notify-item__meta {
            float: right;
            font-size: 0.65rem;
            color: var(--ls-muted);
            font-weight: 500;
        }
        .imara-system-bell .ls-notify-item__body {
            font-size: 0.72rem;
            color: var(--ls-muted);
            margin: 0.2rem 0 0;
            clear: both;
            white-space: pre-line;
            max-height: 7.5rem;
            overflow-y: auto;
        }
        .imara-system-bell .ls-notify-empty {
            padding: 1rem 0.25rem;
            text-align: center;
            color: var(--ls-muted);
            font-size: 0.78rem;
        }
        .imara-system-bell .ls-card-notify__see {
            border: 0;
            background: transparent;
            padding: 0;
            cursor: pointer;
            font-size: 0.72rem;
            color: var(--ls-muted);
            text-decoration: none;
        }
        .imara-system-bell .ls-card-notify__see:hover {
            color: var(--ls-ink);
            text-decoration: underline;
        }
        .imara-system-bell [x-cloak] {
            display: none !important;
        }
    </style>

    <button type="button"
        class="imara-system-bell__btn {{ $shouldAlert ? 'is-alerting' : '' }}"
        @click="open = !open"
        :aria-expanded="open"
        title="Lab Notification Center{{ $attentionCount > 0 ? ' ('.$attentionCount.' pending)' : '' }}"
        aria-label="Lab Notification Center{{ $attentionCount > 0 ? ', '.$attentionCount.' unread' : '' }}">
        <i class="mdi {{ $shouldAlert ? 'mdi-bell-ring' : 'mdi-bell-outline' }}" aria-hidden="true"></i>
        @if($attentionCount > 0)
            <span class="imara-system-bell__badge">{{ $attentionCount > 99 ? '99+' : $attentionCount }}</span>
        @endif
    </button>

    <div class="imara-system-bell__menu" x-show="open" x-cloak @click.stop>
        @include('layouts.lab.partials.ls-ui.cards.ls-card-notifications', [
            'title' => 'Lab Notification Center',
            'notificationGroups' => $notificationGroups,
            'interactive' => true,
            'unreadCount' => $unreadCount,
        ])
    </div>
</div>
