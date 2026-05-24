        /* Hero & badges */
        .registry-request-show .rr-hero {
            background: #fff;
            border: 1px solid var(--rr-slate-200);
            border-radius: var(--rr-radius);
            box-shadow: var(--rr-shadow);
            padding: 1.5rem 1.75rem;
            position: relative;
            overflow: hidden;
        }
        .registry-request-show .rr-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #2563eb, #3b82f6, #60a5fa);
        }
        .registry-request-show .rr-hero__top {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }
        .registry-request-show .rr-hero__identity {
            display: flex;
            gap: 1rem;
            align-items: flex-start;
            min-width: 0;
            flex: 1;
        }
        .registry-request-show .rr-hero__icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1px solid #bfdbfe;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.65rem;
            flex-shrink: 0;
        }
        .registry-request-show .rr-hero__eyebrow {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--rr-slate-500);
            margin: 0 0 0.2rem;
        }
        .registry-request-show .rr-hero__ref {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--rr-slate-800);
            margin: 0 0 0.25rem;
            line-height: 1.2;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            letter-spacing: -0.02em;
        }
        .registry-request-show .rr-hero__subject {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--rr-slate-700);
            margin: 0;
            line-height: 1.4;
        }
        .registry-request-show .rr-hero__badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            align-items: center;
        }
        /* rr-badge status/priority styles: layouts.registry.partials.status-badge-styles */
        .registry-request-show .rr-hero__description {
            background: var(--rr-slate-50);
            border: 1px solid var(--rr-slate-200);
            border-radius: var(--rr-radius-sm);
            padding: 1rem 1.15rem;
            margin-bottom: 1.25rem;
        }
        .registry-request-show .rr-hero__description.mb-0 {
            margin-bottom: 0;
        }

        /* Request information (standalone section) */
        .registry-request-show .rr-info-section {
            background: #fff;
            border: 1px solid var(--rr-slate-200);
            border-radius: var(--rr-radius);
            box-shadow: var(--rr-shadow);
            overflow: hidden;
        }
        .registry-request-show .rr-info-section__head {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, var(--rr-slate-50) 0%, var(--rr-primary-soft) 100%);
            border-bottom: 1px solid var(--rr-slate-200);
        }
        .registry-request-show .rr-info-section__icon {
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: #fff;
            border: 1px solid var(--rr-slate-200);
            color: var(--rr-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        .registry-request-show .rr-info-section__title {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--rr-slate-800);
            margin: 0;
        }
        .registry-request-show .rr-info-section__sub {
            font-size: 0.78rem;
            color: var(--rr-slate-500);
            margin: 0.1rem 0 0;
        }
        .registry-request-show .rr-info-section__body {
            padding: 1.25rem;
        }
        .registry-request-show .rr-hero__description-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--rr-slate-500);
            margin: 0 0 0.35rem;
        }
        .registry-request-show .rr-hero__description-text {
            margin: 0;
            color: var(--rr-slate-700);
            font-size: 0.9rem;
            line-height: 1.55;
            white-space: pre-wrap;
        }
        .registry-request-show .rr-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 0.75rem;
        }
        .registry-request-show .rr-meta-item {
            background: var(--rr-slate-50);
            border: 1px solid var(--rr-slate-200);
            border-radius: var(--rr-radius-sm);
            padding: 0.75rem 1rem;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .registry-request-show .rr-meta-item:hover {
            border-color: #bfdbfe;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.06);
        }
        .registry-request-show .rr-meta-label {
            display: block;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--rr-slate-500);
            margin-bottom: 0.25rem;
        }
        .registry-request-show .rr-meta-value {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--rr-slate-800);
            word-break: break-word;
        }

        /* Timeline */
        .registry-request-show .rr-timeline {
            list-style: none;
            margin: 0;
            padding: 1.25rem 1.25rem 1.25rem 1.5rem;
        }
        .registry-request-show .rr-timeline__item {
            display: flex;
            gap: 1rem;
            position: relative;
            padding-bottom: 1.35rem;
        }
        .registry-request-show .rr-timeline__item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 36px;
            bottom: 0;
            width: 2px;
            background: var(--rr-slate-200);
        }
        .registry-request-show .rr-timeline__item:last-child { padding-bottom: 0; }
        .registry-request-show .rr-timeline__dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 1rem;
            z-index: 1;
            border: 2px solid #fff;
            box-shadow: 0 0 0 1px var(--rr-slate-200);
        }
        .registry-request-show .rr-timeline__dot--success { background: #d1fae5; color: #047857; }
        .registry-request-show .rr-timeline__dot--warning { background: #fef3c7; color: #b45309; }
        .registry-request-show .rr-timeline__dot--info { background: #dbeafe; color: #1d4ed8; }
        .registry-request-show .rr-timeline__dot--neutral { background: #f1f5f9; color: #475569; }
        .registry-request-show .rr-timeline__content { flex: 1; min-width: 0; padding-top: 0.15rem; }
        .registry-request-show .rr-timeline__header {
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.35rem;
            margin-bottom: 0.25rem;
        }
        .registry-request-show .rr-timeline__type {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--rr-slate-800);
        }
        .registry-request-show .rr-timeline__time {
            font-size: 0.75rem;
            color: var(--rr-slate-500);
        }
        .registry-request-show .rr-timeline__stages {
            margin: 0 0 0.35rem;
            font-size: 0.82rem;
            color: var(--rr-slate-500);
            display: flex;
            align-items: center;
            gap: 0.35rem;
            flex-wrap: wrap;
        }
        .registry-request-show .rr-timeline__stages .mdi-arrow-right {
            font-size: 0.9rem;
            color: var(--rr-slate-400);
        }
        .registry-request-show .rr-timeline__actor {
            margin: 0 0 0.35rem;
            font-size: 0.8rem;
            color: var(--rr-slate-500);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .registry-request-show .rr-timeline__comment {
            margin: 0;
            padding: 0.65rem 0.85rem;
            background: var(--rr-slate-50);
            border-left: 3px solid var(--rr-primary);
            border-radius: 0 var(--rr-radius-sm) var(--rr-radius-sm) 0;
            font-size: 0.85rem;
            color: var(--rr-slate-700);
            font-style: normal;
        }

        /* Assignments */
        .registry-request-show .rr-assign-form .form-control {
            border-radius: var(--rr-radius-sm);
            border-color: var(--rr-slate-200);
            font-size: 0.875rem;
        }
        .registry-request-show .rr-assign-form .form-control:focus {
            border-color: var(--rr-primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }
        .registry-request-show .rr-btn-assign {
            border-radius: var(--rr-radius-sm);
            font-weight: 600;
            font-size: 0.8125rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .registry-request-show .rr-assign-list {
            list-style: none;
            margin: 0;
            padding: 0;
            border-top: 1px solid var(--rr-slate-200);
        }
        .registry-request-show .rr-assign-list__item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--rr-slate-100);
        }
        .registry-request-show .rr-assign-list__item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .registry-request-show .rr-assign-list__avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            color: #2563eb;
            font-weight: 700;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .registry-request-show .rr-assign-list__info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .registry-request-show .rr-assign-list__name {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--rr-slate-800);
        }
        .registry-request-show .rr-assign-list__role {
            font-size: 0.75rem;
            color: var(--rr-slate-500);
        }
        .registry-request-show .rr-assign-empty {
            font-size: 0.85rem;
            color: var(--rr-slate-500);
            padding-top: 0.5rem;
            border-top: 1px solid var(--rr-slate-200);
        }

        /* Documents */
        .registry-request-show .rr-upload-zone__label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            padding: 1.25rem;
            border: 2px dashed var(--rr-slate-200);
            border-radius: var(--rr-radius-sm);
            background: var(--rr-slate-50);
            cursor: pointer;
            margin: 0;
            font-size: 0.85rem;
            color: var(--rr-slate-500);
            transition: border-color 0.15s ease, background 0.15s ease;
        }
        .registry-request-show .rr-upload-zone__label:hover {
            border-color: #93c5fd;
            background: var(--rr-primary-soft);
            color: var(--rr-primary);
        }
        .registry-request-show .rr-upload-zone__label .mdi { font-size: 1.75rem; }
        .registry-request-show .rr-upload-zone__input {
            position: absolute;
            width: 0;
            height: 0;
            opacity: 0;
            overflow: hidden;
        }
        .registry-request-show .rr-btn-upload {
            border-radius: var(--rr-radius-sm);
            font-weight: 600;
            font-size: 0.8125rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }
        .registry-request-show .rr-doc-list {
            list-style: none;
            margin: 0;
            padding: 0;
            border-top: 1px solid var(--rr-slate-200);
        }
        .registry-request-show .rr-doc-list__item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--rr-slate-100);
        }
        .registry-request-show .rr-doc-list__item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .registry-request-show .rr-doc-list__icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: #f1f5f9;
            color: #475569;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        .registry-request-show .rr-doc-list__info {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }
        .registry-request-show .rr-doc-list__name {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--rr-slate-800);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .registry-request-show .rr-doc-list__meta {
            font-size: 0.72rem;
            color: var(--rr-slate-500);
        }
        .registry-request-show .rr-doc-download {
            border-radius: 8px;
            border: 1px solid var(--rr-slate-200);
            color: var(--rr-slate-700);
            background: #fff;
            padding: 0.35rem 0.5rem;
            line-height: 1;
        }
        .registry-request-show .rr-doc-download:hover {
            background: var(--rr-primary-soft);
            border-color: #93c5fd;
            color: var(--rr-primary);
        }
        .registry-request-show .rr-doc-empty {
            font-size: 0.85rem;
            color: var(--rr-slate-500);
            padding-top: 0.5rem;
            border-top: 1px solid var(--rr-slate-200);
        }

        /* Audit trail */
        .registry-request-show .rr-audit-table-wrap { margin: 0; }
        .registry-request-show .rr-audit-table { font-size: 0.85rem; }
        .registry-request-show .rr-audit-table thead th {
            background: var(--rr-slate-50);
            border-bottom: 1px solid var(--rr-slate-200);
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--rr-slate-500);
            padding: 0.75rem 1.25rem;
            white-space: nowrap;
        }
        .registry-request-show .rr-audit-table tbody td {
            padding: 0.85rem 1.25rem;
            vertical-align: middle;
            border-color: var(--rr-slate-100);
            color: var(--rr-slate-700);
        }
        .registry-request-show .rr-audit-table tbody tr:hover {
            background: var(--rr-slate-50);
        }
        .registry-request-show .rr-audit-table__when {
            white-space: nowrap;
            color: var(--rr-slate-500);
            font-size: 0.8rem;
        }
        .registry-request-show .rr-audit-action {
            font-weight: 600;
            color: var(--rr-slate-800);
        }
        .registry-request-show .rr-audit-table__comment {
            max-width: 220px;
            word-break: break-word;
            color: var(--rr-slate-500);
            font-size: 0.82rem;
        }
        .registry-request-show .rr-audit-pagination {
            border-color: var(--rr-slate-200) !important;
            background: var(--rr-slate-50);
        }

        /* Shared empty state */
        .registry-request-show .rr-empty-state {
            padding: 2.5rem 1.25rem;
            text-align: center;
        }
        .registry-request-show .rr-empty-state__icon {
            width: 48px;
            height: 48px;
            margin: 0 auto 0.75rem;
            border-radius: 14px;
            background: var(--rr-primary-soft);
            color: var(--rr-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .registry-request-show .rr-empty-state__text {
            margin: 0;
            color: var(--rr-slate-500);
            font-size: 0.875rem;
        }
