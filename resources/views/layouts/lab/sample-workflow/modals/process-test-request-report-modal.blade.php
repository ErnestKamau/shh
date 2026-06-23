{{-- ═══════════════════════════════════════════════════════════════
     Process Test Request Report Modal
     Variables expected: $batch
     ═══════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="process-test-request-report-modal" tabindex="-1" role="dialog"
     aria-labelledby="ptrr-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:660px;width:calc(100% - 32px);margin:16px auto;">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.2);display:flex;flex-direction:column;max-height:calc(100vh - 56px);">

            {{-- Header --}}
            <div class="modal-header" style="background:#8B1A1A;color:#fff;border-bottom:none;padding:18px 24px;">
                <h5 class="modal-title font-weight-bold" id="ptrr-modal-label">
                    <i class="mdi mdi-file-document-edit-outline mr-2"></i>
                    Process Laboratory Test Report
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                        style="color:#fff;opacity:1;font-size:1.4rem;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- Body --}}
            <form method="POST" action="{{ route('processTestRequestReport') }}" id="process-trr-form"
                  style="display:flex;flex-direction:column;flex:1 1 auto;min-height:0;">
                @csrf
                <input type="hidden" name="batch_id" value="{{ $batch->id }}">

                <div class="modal-body" style="padding:24px 28px;overflow-y:auto;flex:1 1 auto;">

                    {{-- Revision info banner --}}
                    @php
                        $nextRev = ($batch->test_request_report_sequence ?? 0) + 1;
                    @endphp
                    <div class="alert alert-info d-flex align-items-center mb-4" style="border-radius:8px;font-size:13px;padding:12px 16px;">
                        <i class="mdi mdi-information-outline mr-2" style="font-size:18px;"></i>
                        <div>
                            Generating <strong>Revision {{ str_pad($nextRev, 2, '0', STR_PAD_LEFT) }}</strong>
                            of report
                            <strong>{{ $batch->batch_code }}-R{{ str_pad($nextRev, 2, '0', STR_PAD_LEFT) }}</strong>
                        </div>
                    </div>

                    {{-- Language --}}
                    <div class="form-group mb-4">
                        <label class="font-weight-bold d-block mb-2" style="font-size:14px;">
                            <i class="mdi mdi-translate mr-1"></i> Report Language
                            <span class="text-danger">*</span>
                        </label>
                        <div class="d-flex" style="gap:10px;flex-wrap:wrap;">
                            @foreach([
                                'en' => ['label' => 'English', 'sub' => 'Default', 'code' => 'EN'],
                                'ar' => ['label' => 'Arabic',  'sub' => 'عربي',   'code' => 'AR'],
                                'pt' => ['label' => 'Portuguese', 'sub' => 'Português', 'code' => 'PT'],
                            ] as $val => $lang)
                            <label for="ptrr-lang-{{ $val }}"
                                   class="ptrr-lang-card"
                                   style="display:flex;align-items:center;gap:10px;padding:10px 16px;border:2px solid #dee2e6;border-radius:8px;cursor:pointer;flex:1;min-width:130px;transition:all .15s;">
                                <input type="radio" name="language" id="ptrr-lang-{{ $val }}"
                                       value="{{ $val }}" {{ $val === 'en' ? 'checked' : '' }}
                                       style="display:none;" class="ptrr-lang-radio">
                                <span style="background:#8B1A1A;color:#fff;font-weight:700;font-size:11px;padding:3px 7px;border-radius:4px;letter-spacing:.5px;flex-shrink:0;">{{ $lang['code'] }}</span>
                                <span>
                                    <strong style="font-size:13px;display:block;">{{ $lang['label'] }}</strong>
                                    <span style="font-size:11px;color:#888;">{{ $lang['sub'] }}</span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                        <small class="text-muted mt-1 d-block">
                            The entire report will be rendered in the selected language.
                        </small>
                    </div>
                    <style>
                        .ptrr-lang-card:has(.ptrr-lang-radio:checked) {
                            border-color: #8B1A1A !important;
                            background: #fdf4f4;
                        }
                    </style>
                    <script>
                        document.querySelectorAll('.ptrr-lang-radio').forEach(function(radio) {
                            radio.addEventListener('change', function() {
                                document.querySelectorAll('.ptrr-lang-card').forEach(function(card) {
                                    card.style.borderColor = '#dee2e6';
                                    card.style.background = '';
                                });
                                if (this.checked) {
                                    this.closest('.ptrr-lang-card').style.borderColor = '#8B1A1A';
                                    this.closest('.ptrr-lang-card').style.background = '#fdf4f4';
                                }
                            });
                        });
                        // Init on load
                        document.addEventListener('DOMContentLoaded', function() {
                            var checked = document.querySelector('.ptrr-lang-radio:checked');
                            if (checked) {
                                checked.closest('.ptrr-lang-card').style.borderColor = '#8B1A1A';
                                checked.closest('.ptrr-lang-card').style.background = '#fdf4f4';
                            }
                        });
                    </script>

                    {{-- Revision notes --}}
                    <div class="form-group mb-2">
                        <label class="font-weight-bold" for="ptrr-notes" style="font-size:14px;">
                            <i class="mdi mdi-note-text-outline mr-1"></i> Revision Notes
                            <span class="text-muted font-weight-normal">(optional)</span>
                        </label>
                        <textarea name="notes" id="ptrr-notes" rows="3" class="form-control"
                                  style="border-radius:8px;font-size:13px;resize:none;"
                                  placeholder="Describe what changed in this revision…"></textarea>
                    </div>

                    {{-- Previous revisions --}}
                    @php
                        $existingRevisions = \App\Models\TestRequestReportRevision::where('batch_id', $batch->id)
                            ->orderByDesc('revision_no')->get();
                    @endphp
                    @if($existingRevisions->isNotEmpty())
                    <div class="mt-4">
                        <p class="font-weight-bold mb-2" style="font-size:13px;color:#555;">
                            <i class="mdi mdi-history mr-1"></i> Revision History
                        </p>
                        <div style="max-height:140px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:8px;">
                            <table class="table table-sm table-hover mb-0" style="font-size:12px;">
                                <thead style="background:#f5f5f5;position:sticky;top:0;">
                                    <tr>
                                        <th class="pl-3">Rev.</th>
                                        <th>Language</th>
                                        <th>By</th>
                                        <th>Date</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($existingRevisions as $rev)
                                    <tr>
                                        <td class="pl-3 font-weight-bold">
                                            R{{ str_pad($rev->revision_no, 2, '0', STR_PAD_LEFT) }}
                                        </td>
                                        <td>
                                            @php $langLabels = ['en'=>'English','ar'=>'Arabic','pt'=>'Portuguese']; @endphp
                                            <span class="badge badge-light border">
                                                {{ $langLabels[$rev->language] ?? strtoupper($rev->language) }}
                                            </span>
                                        </td>
                                        <td>{{ optional(\App\User::find($rev->generated_by))->name ?? '—' }}</td>
                                        <td>{{ $rev->created_at ? $rev->created_at->format('d/m/Y') : '—' }}</td>
                                        <td class="text-muted">{{ $rev->notes ? \Illuminate\Support\Str::limit($rev->notes, 40) : '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                    {{-- ════════ DELIVERY OPTIONS ════════ --}}
                @php
                    $batchContacts = collect();
                    if (!empty($batch->crm_customer_id)) {
                        $batchContacts = \App\Models\CRM\CustomerContact::where('crm_customer_id', $batch->crm_customer_id)
                            ->where('active', 1)->get();
                    }
                @endphp

                <div class="mt-4" id="ptrr-delivery-section">
                    <div style="border-top:2px solid #f0f0f0;padding-top:16px;">
                        <p class="font-weight-bold mb-3" style="font-size:13px;color:#333;">
                            <i class="mdi mdi-send-outline mr-1" style="color:#8B1A1A;"></i> Send Report To
                            <span class="text-muted font-weight-normal" style="font-size:11px;">(optional)</span>
                        </p>

                        @if($batchContacts->isEmpty())
                            <div class="alert alert-warning py-2 px-3" style="font-size:12px;border-radius:6px;">
                                <i class="mdi mdi-alert-outline mr-1"></i> No contacts found for this customer.
                            </div>
                        @else
                            {{-- Contact picker --}}
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold" style="color:#555;">Select Contact(s)</label>
                                <div style="border:1px solid #dee2e6;border-radius:8px;max-height:130px;overflow-y:auto;padding:8px 12px;background:#fafafa;">
                                    @foreach($batchContacts as $c)
                                    <div class="custom-control custom-checkbox mb-1">
                                        <input type="checkbox" class="custom-control-input ptrr-contact-check"
                                               id="ptrr-contact-{{ $c->id }}" value="{{ $c->id }}"
                                               data-name="{{ $c->name ?? 'Contact' }}"
                                               data-email="{{ $c->email ?? '' }}"
                                               data-phone="{{ $c->mobile ?? $c->telephone ?? '' }}">
                                        <label class="custom-control-label" for="ptrr-contact-{{ $c->id }}" style="font-size:13px;cursor:pointer;">
                                            <strong>{{ $c->name ?? 'Contact' }}</strong>
                                            @if($c->email)
                                                <span class="text-muted" style="font-size:11px;"> &bull; {{ $c->email }}</span>
                                            @endif
                                            @if($c->mobile ?? $c->telephone)
                                                <span class="text-muted" style="font-size:11px;"> &bull; {{ $c->mobile ?? $c->telephone }}</span>
                                            @endif
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Channel selection --}}
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold" style="color:#555;">Send Via</label>
                                <div class="d-flex" style="gap:10px;flex-wrap:wrap;">
                                    <label class="ptrr-channel-card" for="ptrr-ch-email"
                                           style="display:flex;align-items:center;gap:8px;padding:8px 14px;border:2px solid #dee2e6;border-radius:8px;cursor:pointer;font-size:13px;transition:all .15s;">
                                        <input type="checkbox" id="ptrr-ch-email" value="email" class="ptrr-ch-check" style="display:none;">
                                        <i class="mdi mdi-email-outline" style="font-size:18px;color:#555;"></i>
                                        <span>Email</span>
                                    </label>
                                    <label class="ptrr-channel-card" for="ptrr-ch-whatsapp"
                                           style="display:flex;align-items:center;gap:8px;padding:8px 14px;border:2px solid #dee2e6;border-radius:8px;cursor:pointer;font-size:13px;transition:all .15s;">
                                        <input type="checkbox" id="ptrr-ch-whatsapp" value="whatsapp" class="ptrr-ch-check" style="display:none;">
                                        <i class="mdi mdi-whatsapp" style="font-size:18px;color:#25D366;"></i>
                                        <span>WhatsApp</span>
                                    </label>
                                    <label class="ptrr-channel-card" for="ptrr-ch-portal"
                                           style="display:flex;align-items:center;gap:8px;padding:8px 14px;border:2px solid #dee2e6;border-radius:8px;cursor:pointer;font-size:13px;transition:all .15s;">
                                        <input type="checkbox" id="ptrr-ch-portal" value="portal" class="ptrr-ch-check" style="display:none;">
                                        <i class="mdi mdi-web" style="font-size:18px;color:#4A90D9;"></i>
                                        <span>Customer Portal</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Delivery result area --}}
                            <div id="ptrr-delivery-result" style="display:none;"></div>

                            <div class="d-flex justify-content-end">
                                <button type="button" id="ptrr-send-btn"
                                        style="background:#1a6b1a;color:#fff;border:none;border-radius:6px;padding:7px 20px;font-size:13px;font-weight:700;cursor:pointer;">
                                    <i class="mdi mdi-send mr-1"></i> Send Now
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <style>
                    .ptrr-channel-card:has(.ptrr-ch-check:checked) { border-color:#8B1A1A !important; background:#fdf4f4; }
                </style>
                <script>
                (function() {
                    document.querySelectorAll('.ptrr-ch-check').forEach(function(cb) {
                        cb.addEventListener('change', function() {
                            var card = this.closest('.ptrr-channel-card');
                            card.style.borderColor = this.checked ? '#8B1A1A' : '#dee2e6';
                            card.style.background  = this.checked ? '#fdf4f4' : '';
                        });
                    });

                    var sendBtn = document.getElementById('ptrr-send-btn');
                    if (sendBtn) {
                        sendBtn.addEventListener('click', function() {
                            var contactIds = [];
                            document.querySelectorAll('.ptrr-contact-check:checked').forEach(function(c) {
                                contactIds.push(c.value);
                            });
                            var channels = [];
                            document.querySelectorAll('.ptrr-ch-check:checked').forEach(function(c) {
                                channels.push(c.value);
                            });

                            if (!contactIds.length) {
                                alert('Please select at least one contact.'); return;
                            }
                            if (!channels.length) {
                                alert('Please select at least one delivery channel.'); return;
                            }

                            var batchId = document.querySelector('#process-trr-form input[name="batch_id"]').value;
                            var notes   = document.getElementById('ptrr-notes') ? document.getElementById('ptrr-notes').value : '';
                            var csrf    = document.querySelector('#process-trr-form input[name="_token"]').value;

                            sendBtn.disabled = true;
                            sendBtn.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i> Sending…';

                            var body = new FormData();
                            body.append('_token', csrf);
                            body.append('batch_id', batchId);
                            body.append('notes', notes);
                            contactIds.forEach(function(id) { body.append('contact_ids[]', id); });
                            channels.forEach(function(ch)  { body.append('channels[]', ch); });

                            fetch('{{ route("deliverTestRequestReport") }}', { method: 'POST', body: body })
                                .then(function(r) { return r.json(); })
                                .then(function(data) {
                                    var resultEl = document.getElementById('ptrr-delivery-result');
                                    var html = '<div class="alert ' + (data.success ? 'alert-success' : 'alert-danger') + ' py-2 px-3 mt-2" style="font-size:12px;border-radius:6px;">';
                                    html += '<strong>' + (data.message || '') + '</strong>';
                                    if (data.results && data.results.length) {
                                        html += '<ul class="mb-0 mt-1" style="padding-left:16px;">';
                                        data.results.forEach(function(r) {
                                            var icon = r.status === 'sent' ? '✓' : '✗';
                                            var clr  = r.status === 'sent' ? 'green' : 'red';
                                            html += '<li style="color:' + clr + ';">' + icon + ' ' + r.contact + ' via ' + r.channel + (r.error ? ' — ' + r.error : '') + '</li>';
                                        });
                                        html += '</ul>';
                                    }
                                    html += '</div>';
                                    resultEl.innerHTML = html;
                                    resultEl.style.display = 'block';
                                    sendBtn.disabled = false;
                                    sendBtn.innerHTML = '<i class="mdi mdi-send mr-1"></i> Send Now';
                                })
                                .catch(function(err) {
                                    alert('Delivery failed. Please try again.');
                                    sendBtn.disabled = false;
                                    sendBtn.innerHTML = '<i class="mdi mdi-send mr-1"></i> Send Now';
                                });
                        });
                    }
                })();
                </script>

                </div>{{-- /modal-body --}}

                {{-- Footer --}}
                <div class="modal-footer" style="border-top:1px solid #f0f0f0;padding:16px 24px;background:#fafafa;">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="border-radius:6px;">
                        <i class="mdi mdi-close mr-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-sm font-weight-bold" id="ptrr-submit-btn"
                            style="background:#8B1A1A;color:#fff;border-radius:6px;padding:6px 20px;">
                        <i class="mdi mdi-file-check-outline mr-1"></i> Generate Report
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
