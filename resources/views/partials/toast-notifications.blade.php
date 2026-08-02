{{-- Global SweetAlert2 toast layer (session flash + Livewire notify/alert) --}}
@once
    <link rel="stylesheet" href="{{ asset('assets/js/libs/sweetalert2/sweetalert2.min.css') }}">
    <style>
        /* Session flash banners are shown as toasts; keep validation $errors visible. */
        #message-section > .alert-success,
        #message-section > .alert-warning,
        #message-section > .alert-info,
        #message-section .alert.alert-callout {
            display: none !important;
        }

        .swal2-toast.swal2-popup {
            font-size: 0.95rem;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.18);
        }

        .swal2-popup.swal2-modal {
            border-radius: 10px;
        }

        .swal2-styled.swal2-confirm {
            background-color: #007bff !important;
        }

        .swal2-styled.swal2-deny,
        .swal2-styled.swal2-cancel {
            background-color: #6c757d !important;
        }
    </style>
    <script src="{{ asset('assets/js/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <script>
        (function () {
            if (window.__appToastInitialized) {
                return;
            }
            window.__appToastInitialized = true;

            function ensureSwal() {
                return typeof Swal !== 'undefined';
            }

            function onReady(callback) {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', callback);
                } else {
                    callback();
                }
            }

            function normalizeType(type) {
                const value = String(type || 'info').toLowerCase();
                if (value === 'danger' || value === 'failed' || value === 'fail') {
                    return 'error';
                }
                if (['success', 'error', 'warning', 'info', 'question'].includes(value)) {
                    return value;
                }
                return 'info';
            }

            function normalizePayload(raw) {
                let data = raw;

                if (Array.isArray(data)) {
                    data = data[0] ?? {};
                }

                if (data && typeof data === 'object' && data.detail !== undefined) {
                    data = Array.isArray(data.detail) ? (data.detail[0] ?? {}) : data.detail;
                }

                if (typeof data === 'string') {
                    return { type: 'info', message: data };
                }

                if (!data || typeof data !== 'object') {
                    return { type: 'info', message: '' };
                }

                return {
                    type: normalizeType(data.type ?? data.status ?? 'info'),
                    message: data.message ?? data.text ?? data.title ?? '',
                };
            }

            function toastTimerFor(type) {
                if (type === 'error') {
                    return 5500;
                }
                if (type === 'warning') {
                    return 4500;
                }
                return 3500;
            }

            function getToast() {
                if (!ensureSwal()) {
                    return null;
                }

                return Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    showCloseButton: true,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', Swal.stopTimer);
                        toast.addEventListener('mouseleave', Swal.resumeTimer);
                    },
                });
            }

            window.showAppToast = function (type, message, options) {
                if (!message) {
                    return;
                }

                const icon = normalizeType(type);
                const opts = options && typeof options === 'object' ? options : {};
                const Toast = getToast();

                if (!Toast) {
                    console.warn('[toast]', icon + ':', message);
                    return;
                }

                Toast.fire({
                    icon: icon,
                    title: message,
                    timer: opts.timer ?? toastTimerFor(icon),
                    ...opts,
                    toast: true,
                    showConfirmButton: false,
                });
            };

            window.showAppConfirm = function (options) {
                const opts = options && typeof options === 'object' ? options : {};

                if (!ensureSwal()) {
                    return Promise.resolve(window.confirm(opts.text || opts.title || 'Are you sure?'));
                }

                return Swal.fire({
                    icon: opts.icon || 'question',
                    title: opts.title || 'Are you sure?',
                    text: opts.text || '',
                    showCancelButton: opts.showCancelButton !== false,
                    confirmButtonText: opts.confirmButtonText || 'Confirm',
                    cancelButtonText: opts.cancelButtonText || 'Cancel',
                    reverseButtons: true,
                    focusCancel: true,
                    ...opts,
                    toast: false,
                }).then(function (result) {
                    return result.isConfirmed === true;
                });
            };

            // Backward-compatible alias used by Risk/Audit pages
            window.showToastNotification = function (message, type) {
                window.showAppToast(type || 'info', message);
            };

            function handleLivewireNotification(raw) {
                const payload = normalizePayload(raw);
                if (!payload.message) {
                    return;
                }
                window.showAppToast(payload.type, payload.message);
            }

            document.addEventListener('livewire:init', function () {
                if (window.__appToastLivewireBound) {
                    return;
                }
                window.__appToastLivewireBound = true;

                Livewire.on('notify', handleLivewireNotification);
                Livewire.on('alert', handleLivewireNotification);
            });

            @if (session()->has('success'))
                onReady(function () {
                    window.showAppToast('success', @json(session('success')));
                });
            @endif
            @if (session()->has('error'))
                onReady(function () {
                    window.showAppToast('error', @json(session('error')));
                });
            @endif
            @if (session()->has('warning'))
                onReady(function () {
                    window.showAppToast('warning', @json(session('warning')));
                });
            @endif
            @if (session()->has('info'))
                onReady(function () {
                    window.showAppToast('info', @json(session('info')));
                });
            @endif
        })();
    </script>
@endonce
