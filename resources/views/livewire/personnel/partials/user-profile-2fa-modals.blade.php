<!-- 2FA Modal -->
<div class="modal fade" id="twoFactorModal" tabindex="-1" role="dialog" aria-labelledby="twoFactorModalLabel" aria-hidden="true" wire:ignore.self>
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="border-radius: 18px">
      <div class="modal-header">
        <h5 class="modal-title" id="twoFactorModalLabel"><i class="mdi mdi-shield-key-outline text-primary"></i> Two-Factor Authentication</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="twofa-alert" class="alert d-none" role="alert"></div>

        <div id="twofa-enabled-state" class="{{ $user->two_factor_confirmed_at ? '' : 'd-none' }}">
            <div class="mb-3 p-3 border rounded">
                <div class="text-muted small">Status</div>
                <div class="font-weight-bold">Enabled</div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <div class="p-3 border rounded h-100">
                        <div class="font-weight-semibold mb-1">Recovery codes</div>
                        <div class="text-muted small mb-2">Regenerate recovery codes (requires password).</div>
                        <input type="password" class="form-control mb-2" id="twofa-password-recovery" placeholder="Confirm password">
                        <button class="btn btn-outline-primary btn-sm" type="button" id="btn-recovery-regenerate">
                            <i class="mdi mdi-refresh"></i> Regenerate Codes
                        </button>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="p-3 border rounded h-100">
                        <div class="font-weight-semibold mb-1">Disable 2FA</div>
                        <div class="text-muted small mb-2">Disable authenticator protection (requires password).</div>
                        <input type="password" class="form-control mb-2" id="twofa-password-disable" placeholder="Confirm password">
                        <button class="btn btn-outline-danger btn-sm" type="button" id="btn-twofa-disable">
                            <i class="mdi mdi-close-circle-outline"></i> Disable
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="twofa-disabled-state" class="{{ $user->two_factor_confirmed_at ? 'd-none' : '' }}">
            <div class="mb-3 p-3 border rounded">
                <div class="text-muted small">Status</div>
                <div class="font-weight-bold">Not enabled</div>
                <div class="text-muted small mt-1">Enable authenticator-based 2FA to require an app code at login.</div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <div class="p-3 border rounded h-100 text-center">
                        <div class="font-weight-semibold mb-2">Scan QR Code</div>
                        <img id="twofa-qr" src="" alt="QR code" style="max-width: 220px; border-radius: 12px; border: 1px solid #e9ecef; background:#fff">
                        <div class="text-muted small mt-2">Use Google Authenticator, Authy, or Microsoft Authenticator.</div>
                    </div>
                </div>
                <div class="col-md-6 mb-2">
                    <div class="p-3 border rounded h-100">
                        <div class="font-weight-semibold mb-1">Manual key</div>
                        <div id="twofa-manual-key" style="font-family: monospace">-</div>
                        <div class="mt-3">
                            <label class="control-label"><strong>Verify code</strong></label>
                            <input type="text" class="form-control" id="twofa-code" placeholder="6-digit code">
                            <button class="btn btn-primary btn-sm mt-3" type="button" id="btn-twofa-confirm">
                                <i class="mdi mdi-shield-check"></i> Confirm Setup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="recoveryCodesModal" tabindex="-1" role="dialog" aria-labelledby="recoveryCodesModalLabel" aria-hidden="true" wire:ignore.self>
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content" style="border-radius: 18px">
      <div class="modal-header">
        <h5 class="modal-title" id="recoveryCodesModalLabel"><i class="mdi mdi-key-outline text-primary"></i> Recovery Codes</h5>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning" role="alert" style="border-radius: 12px">
            Save these codes somewhere safe. Each code can be used once to access your account if you lose your authenticator.
        </div>
        <pre class="p-3 mb-2" style="background:#f8f9fa;border-radius:12px" id="recovery-codes-pre"></pre>
        <div class="d-flex align-items-center" style="gap: .5rem">
            <button class="btn btn-outline-secondary btn-sm" type="button" id="btn-copy-recovery"><i class="mdi mdi-content-copy"></i> Copy</button>
            <button class="btn btn-primary btn-sm" type="button" id="btn-recovery-saved"><i class="mdi mdi-check"></i> I have saved the recovery codes</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
    (function () {
        if (window.__personnelUserProfile2faInit) {
            return;
        }
        window.__personnelUserProfile2faInit = true;

        function bootTwoFa() {
            if (typeof window.jQuery === 'undefined') {
                return;
            }
            var $ = window.jQuery;
            var csrf = $('meta[name="csrf-token"]').attr('content');

            function showAlert(type, message) {
                var $a = $('#twofa-alert');
                $a.removeClass('d-none alert-success alert-danger alert-info')
                    .addClass(type === 'success' ? 'alert-success' : (type === 'info' ? 'alert-info' : 'alert-danger'))
                    .text(message);
            }

            function resetAlert() {
                $('#twofa-alert').addClass('d-none').text('');
            }

            function openTwoFaModal() {
                resetAlert();
                $('#twoFactorModal').modal('show');
                if (! $('#twofa-disabled-state').hasClass('d-none')) {
                    $('#twofa-qr').attr('src', '');
                    $('#twofa-manual-key').text('Loading…');
                    $.ajax({
                        url: @json(route('account.2fa.setup')),
                        method: 'GET',
                        headers: { 'X-CSRF-TOKEN': csrf },
                        success: function (resp) {
                            var svg = resp.qr_svg_base64 || resp.qr_svg || resp.qr || null;
                            if (svg) {
                                $('#twofa-qr').attr('src', 'data:image/svg+xml;base64,' + svg);
                            }
                            $('#twofa-manual-key').text(resp.manual_key || '-');
                        },
                        error: function (xhr) {
                            showAlert('danger', 'Failed to start 2FA setup.' + (xhr && xhr.status ? ' (HTTP ' + xhr.status + ')' : ''));
                            $('#twofa-manual-key').text('-');
                        }
                    });
                }
            }

            $(document).off('click.pup2fa', '#open-2fa-modal, #open-2fa-modal-2').on('click.pup2fa', '#open-2fa-modal, #open-2fa-modal-2', openTwoFaModal);

            $(document).off('click.pup2faConfirm', '#btn-twofa-confirm').on('click.pup2faConfirm', '#btn-twofa-confirm', function () {
                resetAlert();
                $.ajax({
                    url: @json(route('account.2fa.confirm')),
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf },
                    data: { code: $('#twofa-code').val() },
                    success: function (resp) {
                        showAlert('success', 'Two-factor enabled. Save your recovery codes now.');
                        $('#recovery-codes-pre').text((resp.recovery_codes || []).join("\n"));
                        $('#twofa-disabled-state').addClass('d-none');
                        $('#twofa-enabled-state').removeClass('d-none');
                        $('#recoveryCodesModal').modal({ backdrop: 'static', keyboard: false });
                        $('#recoveryCodesModal').modal('show');
                    },
                    error: function (xhr) {
                        showAlert('danger', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Invalid code.');
                    }
                });
            });

            $(document).off('click.pup2faDisable', '#btn-twofa-disable').on('click.pup2faDisable', '#btn-twofa-disable', function () {
                resetAlert();
                $.ajax({
                    url: @json(route('account.2fa.disable')),
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf },
                    data: { password: $('#twofa-password-disable').val() },
                    success: function () {
                        showAlert('success', 'Two-factor disabled. Reloading...');
                        window.location.reload();
                    },
                    error: function (xhr) {
                        showAlert('danger', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to disable.');
                    }
                });
            });

            $(document).off('click.pup2faRecovery', '#btn-recovery-regenerate').on('click.pup2faRecovery', '#btn-recovery-regenerate', function () {
                resetAlert();
                $.ajax({
                    url: @json(route('account.2fa.recovery-codes')),
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf },
                    data: { password: $('#twofa-password-recovery').val() },
                    success: function (resp) {
                        showAlert('success', 'Recovery codes regenerated. Save them now.');
                        $('#recovery-codes-pre').text((resp.recovery_codes || []).join("\n"));
                        $('#recoveryCodesModal').modal({ backdrop: 'static', keyboard: false });
                        $('#recoveryCodesModal').modal('show');
                    },
                    error: function (xhr) {
                        showAlert('danger', (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to regenerate codes.');
                    }
                });
            });

            $(document).off('click.pup2faCopy', '#btn-copy-recovery').on('click.pup2faCopy', '#btn-copy-recovery', function () {
                var text = $('#recovery-codes-pre').text();
                if (text && navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text);
                }
            });

            $(document).off('click.pup2faSaved', '#btn-recovery-saved').on('click.pup2faSaved', '#btn-recovery-saved', function () {
                window.location.reload();
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bootTwoFa);
        } else {
            bootTwoFa();
        }
    })();
</script>
