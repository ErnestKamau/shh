{{-- Global toast layer (session flash + Livewire notify/alert) with SweetAlert2 + DOM fallback --}}
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

        #app-toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 20000;
            max-width: 400px;
            pointer-events: none;
        }

        #app-toast-container .app-toast {
            pointer-events: auto;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.16);
        }
    </style>
    <script src="{{ asset('assets/js/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>

    <script>
        (function () {
            if (window.__appToastInitialized) {
                return;
            }
            window.__appToastInitialized = true;

            // sweetalert2.all.min.js exposes Sweetalert2; older code expects Swal.
            if (typeof window.Swal === 'undefined' && typeof window.Sweetalert2 !== 'undefined') {
                window.Swal = window.Sweetalert2;
            }

            function ensureSwal() {
                return typeof window.Swal !== 'undefined' || typeof window.Sweetalert2 !== 'undefined';
            }

            function getSwal() {
                return window.Swal || window.Sweetalert2 || null;
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

                // Livewire.on already unwraps CustomEvent.detail in v3, but some
                // callers still pass the event object or a one-item params array.
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

            function bootstrapAlertClass(type) {
                if (type === 'error') {
                    return 'danger';
                }
                if (type === 'question') {
                    return 'info';
                }
                return type;
            }

            function showDomToast(type, message) {
                let container = document.getElementById('app-toast-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'app-toast-container';
                    document.body.appendChild(container);
                }

                const toast = document.createElement('div');
                const alertType = bootstrapAlertClass(type);
                toast.className = 'alert alert-' + alertType + ' alert-dismissible fade show app-toast';
                toast.setAttribute('role', 'alert');
                toast.innerHTML =
                    '<span>' + String(message) + '</span>' +
                    '<button type="button" class="close" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span></button>';

                const close = function () {
                    toast.classList.remove('show');
                    setTimeout(function () {
                        if (toast.parentElement) {
                            toast.parentElement.removeChild(toast);
                        }
                    }, 150);
                };

                toast.querySelector('.close')?.addEventListener('click', close);
                container.appendChild(toast);
                setTimeout(close, toastTimerFor(type));
            }

            function getToast() {
                const SwalLib = getSwal();
                if (!SwalLib) {
                    return null;
                }

                return SwalLib.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    showCloseButton: true,
                    timerProgressBar: true,
                    didOpen: (toast) => {
                        toast.addEventListener('mouseenter', SwalLib.stopTimer);
                        toast.addEventListener('mouseleave', SwalLib.resumeTimer);
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
                    showDomToast(icon, message);
                    return;
                }

                try {
                    Toast.fire({
                        icon: icon,
                        title: message,
                        timer: opts.timer ?? toastTimerFor(icon),
                        ...opts,
                        toast: true,
                        showConfirmButton: false,
                    });
                } catch (error) {
                    console.warn('[toast] SweetAlert failed, using fallback', error);
                    showDomToast(icon, message);
                }
            };

            window.showAppConfirm = function (options) {
                const opts = options && typeof options === 'object' ? options : {};
                const SwalLib = getSwal();

                if (!SwalLib) {
                    return Promise.resolve(window.confirm(opts.text || opts.title || 'Are you sure?'));
                }

                return SwalLib.fire({
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

            // Livewire dispatches notify/alert as bubbling window CustomEvents (__livewire flag).
            // Listen directly so we do not depend on livewire:init timing.
            if (!window.__appToastLivewireBound) {
                window.__appToastLivewireBound = true;

                window.addEventListener('notify', function (event) {
                    if (!event || !event.__livewire) {
                        return;
                    }
                    handleLivewireNotification(event.detail);
                });
                window.addEventListener('alert', function (event) {
                    if (!event || !event.__livewire) {
                        return;
                    }
                    handleLivewireNotification(event.detail);
                });
            }

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
