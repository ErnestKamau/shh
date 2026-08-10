
<div>
    <style>
        .contact-form {
            --cf-maroon: #7a1f2b;
            --cf-maroon-soft: #f7eef0;
            --cf-maroon-border: #e4c5cb;
            --cf-ink: #2b2426;
            --cf-muted: #6b6467;
            --cf-line: #ebe4e6;
            --cf-field: #fbf9fa;
            --cf-radius: 10px;
        }

        .contact-modal-overlay {
            z-index: 1060;
            padding: 1rem 0;
        }

        .contact-modal-dialog {
            margin: 1rem auto;
            max-width: 920px;
        }

        .contact-modal-content {
            max-height: calc(100vh - 2rem);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 0;
            border-radius: 14px;
            box-shadow: 0 18px 48px rgba(43, 36, 38, 0.22);
        }

        .contact-modal-header {
            flex-shrink: 0;
            border-bottom: 0;
            padding: 1rem 1.35rem;
        }

        .contact-modal-header .modal-title {
            font-size: 1.05rem;
            font-weight: 600;
            letter-spacing: 0.01em;
        }

        .contact-modal-form {
            display: flex;
            flex-direction: column;
            flex: 1 1 auto;
            min-height: 0;
            overflow: hidden;
        }

        .contact-modal-body {
            flex: 1 1 auto;
            overflow-y: auto;
            min-height: 0;
            padding: 1.25rem 1.35rem 1rem;
            background:
                linear-gradient(180deg, #fcfbfb 0%, #ffffff 120px);
        }

        .contact-modal-footer {
            flex-shrink: 0;
            position: sticky;
            bottom: 0;
            z-index: 2;
            gap: 0.5rem;
            border-top: 1px solid var(--cf-line);
            padding: 0.85rem 1.35rem;
            background: #faf7f8;
        }

        .cf-section {
            margin-bottom: 1.15rem;
        }

        .cf-section:last-child {
            margin-bottom: 0;
        }

        .cf-section-head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            padding-bottom: 0.45rem;
            border-bottom: 1px solid var(--cf-line);
        }

        .cf-section-head h5 {
            margin: 0;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--cf-maroon);
        }

        .cf-section-head span {
            font-size: 0.75rem;
            color: var(--cf-muted);
        }

        .cf-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.85rem 1rem;
        }

        .cf-grid--phones {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            max-width: calc((100% - 1rem) * 2 / 3 + 1rem);
        }

        @media (max-width: 767.98px) {
            .cf-grid,
            .cf-grid--phones,
            .cf-prefs {
                grid-template-columns: 1fr;
                max-width: none;
            }
        }

        .cf-field {
            margin: 0;
        }

        .cf-field label {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--cf-ink);
        }

        .cf-field .form-control,
        .cf-field .tag-select-input {
            border-color: #ddd4d6;
            border-radius: 8px;
            background: #fff;
            min-height: 40px;
            box-shadow: none;
        }

        .cf-field .form-control:focus,
        .cf-field .tag-select-input:focus-within {
            border-color: var(--cf-maroon);
            box-shadow: 0 0 0 3px rgba(122, 31, 43, 0.12);
        }

        .cf-field .text-danger.small {
            display: block;
            margin-top: 0.25rem;
        }

        .tag-select-container {
            position: relative;
            width: 100%;
        }

        .tag-select-input {
            min-height: 40px;
            border: 1px solid #ddd4d6;
            border-radius: 8px;
            padding: 4px 8px;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
            background-color: #fff;
            cursor: text;
        }

        .tag-input {
            border: none;
            outline: none;
            flex: 1;
            min-width: 110px;
            font-size: 0.9rem;
            background: transparent;
        }

        .tag-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #e8d5d9;
            color: #4a1520;
            border: 1px solid #c49aa3;
            border-radius: 999px;
            padding: 3px 10px;
            font-size: 0.8rem;
            font-weight: 600;
            line-height: 1.3;
        }

        .tag-badge i {
            cursor: pointer;
            color: #7a1f2b;
            font-size: 0.95rem;
        }

        .tag-badge i:hover {
            color: #4a1520;
        }

        .tag-dropdown {
            position: absolute;
            top: calc(100% + 4px);
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #ddd4d6;
            border-radius: 8px;
            box-shadow: 0 10px 28px rgba(43, 36, 38, 0.12);
            max-height: 220px;
            overflow-y: auto;
            z-index: 1100;
        }

        .tag-dropdown-item {
            padding: 9px 12px;
            cursor: pointer;
            font-size: 0.9rem;
        }

        .tag-dropdown-item:hover {
            background: var(--cf-maroon-soft);
        }

        .tag-select-container.is-invalid .tag-select-input {
            border-color: #dc3545;
        }

        .cf-prefs {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.65rem;
        }

        .cf-pref {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            margin: 0;
            min-height: 46px;
            padding: 0.65rem 0.8rem;
            border: 1px solid var(--cf-line);
            border-radius: 10px;
            background: #fff;
            cursor: pointer;
            transition: border-color 0.15s ease, background-color 0.15s ease, box-shadow 0.15s ease;
        }

        .cf-pref:hover {
            border-color: var(--cf-maroon-border);
            background: #fffcfc;
        }

        .cf-pref:has(input:checked) {
            border-color: var(--cf-maroon-border);
            background: var(--cf-maroon-soft);
            box-shadow: inset 0 0 0 1px rgba(122, 31, 43, 0.08);
        }

        .cf-pref input {
            margin: 0;
            flex-shrink: 0;
            accent-color: var(--cf-maroon);
        }

        .cf-pref span {
            font-size: 0.86rem;
            font-weight: 600;
            color: var(--cf-ink);
            line-height: 1.25;
        }

        .cf-portal {
            margin-top: 0.75rem;
            border: 1px solid var(--cf-line);
            border-radius: 12px;
            background: #fff;
            overflow: hidden;
        }

        .cf-portal-toggle {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin: 0;
            padding: 0.9rem 1rem;
            cursor: pointer;
        }

        .cf-portal-toggle input {
            margin-top: 0.2rem;
            flex-shrink: 0;
            accent-color: var(--cf-maroon);
        }

        .cf-portal-toggle strong {
            display: block;
            font-size: 0.92rem;
            color: var(--cf-ink);
            margin-bottom: 0.15rem;
        }

        .cf-portal-toggle small {
            display: block;
            color: var(--cf-muted);
            font-size: 0.78rem;
            line-height: 1.4;
        }

        .cf-portal-note {
            display: flex;
            align-items: flex-start;
            gap: 0.65rem;
            margin: 0;
            padding: 0.85rem 1rem;
            border-top: 1px dashed var(--cf-maroon-border);
            background: linear-gradient(180deg, var(--cf-maroon-soft), #fff);
            color: #5a3038;
            font-size: 0.84rem;
            line-height: 1.45;
        }

        .cf-portal-note i {
            color: var(--cf-maroon);
            font-size: 1.1rem;
            margin-top: 0.05rem;
        }

        .cf-portal:has(input:checked) {
            border-color: var(--cf-maroon-border);
            box-shadow: 0 0 0 3px rgba(122, 31, 43, 0.06);
        }
    </style>

    <template x-teleport="body">
        <div class="modal fade show contact-modal-overlay contact-form"
            style="display: block; background-color: rgba(43, 36, 38, 0.45); overflow-y: auto;"
            wire:click.self="close"
            tabindex="-1"
            role="dialog"
            wire:ignore.self>
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable contact-modal-dialog" role="document">
                <div class="modal-content contact-modal-content">
                    <div class="modal-header contact-modal-header">
                        <h4 class="modal-title">
                            <i class="mdi mdi-{{ $contactId ? 'pencil' : 'account-plus' }}"></i>
                            {{ $contactId ? __('crm.edit') : __('crm.add') }} {{ __('crm.company_contact') }}
                        </h4>
                        <button type="button" class="close" wire:click="close" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form class="contact-modal-form" onsubmit="return false;">
                        <div class="modal-body contact-modal-body">
                            <section class="cf-section">
                                <div class="cf-section-head">
                                    <h5>Contact details</h5>
                                    <span>Name, role, and how to reach them</span>
                                </div>

                                <div class="cf-grid">
                                    <div class="cf-field">
                                        <label>{{ __('crm.first_name') }} <span class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('first_name') is-invalid @enderror"
                                            wire:model="first_name"
                                            placeholder="{{ __('crm.first_name_placeholder') }}"
                                            required />
                                        @error('first_name') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="cf-field">
                                        <label>{{ __('crm.middle_name') }}</label>
                                        <input type="text"
                                            class="form-control"
                                            wire:model="second_name"
                                            placeholder="{{ __('crm.middle_name_placeholder') }}" />
                                    </div>
                                    <div class="cf-field">
                                        <label>{{ __('crm.last_name') }}</label>
                                        <input type="text"
                                            class="form-control"
                                            wire:model="third_name"
                                            placeholder="{{ __('crm.last_name_placeholder') }}" />
                                    </div>

                                    <div class="cf-field">
                                        <label>{{ __('crm.occupation') }}</label>
                                        <input type="text"
                                            class="form-control @error('job_occupation') is-invalid @enderror"
                                            wire:model="job_occupation"
                                            placeholder="{{ __('crm.occupation_placeholder') }}" />
                                        @error('job_occupation') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="cf-field">
                                        <label>{{ __('crm.company_unit') }}</label>
                                        <div class="tag-select-container @error('unit_name') is-invalid @enderror"
                                            wire:click="$set('showUnitDropdown', true)"
                                            wire:click.outside="$set('showUnitDropdown', false)">
                                            <div class="tag-select-input">
                                                @foreach($this->selectedUnits as $unit)
                                                    <span class="tag-badge">
                                                        {{ $unit->name }}
                                                        <i class="mdi mdi-close-circle" wire:click.stop="removeUnitSelection('{{ $unit->id }}')"></i>
                                                    </span>
                                                @endforeach
                                                <input type="text"
                                                    wire:model.live="unitSearch"
                                                    class="tag-input"
                                                    placeholder="{{ __('crm.search_or_select_units') }}"
                                                    autocomplete="off">
                                            </div>

                                            @if($showUnitDropdown)
                                                <div class="tag-dropdown">
                                                    @forelse($this->filteredUnits as $unit)
                                                        <div class="tag-dropdown-item d-flex justify-content-between align-items-center"
                                                            wire:click.stop="toggleUnitSelection('{{ $unit->id }}')">
                                                            <span>{{ $unit->name }}</span>
                                                            @if($this->isUnitSelected($unit->id))
                                                                <i class="mdi mdi-check text-success"></i>
                                                            @endif
                                                        </div>
                                                    @empty
                                                        <div class="tag-dropdown-item text-muted">{{ __('crm.no_units_found_static') }}</div>
                                                    @endforelse
                                                </div>
                                            @endif
                                        </div>
                                        @error('unit_name') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="cf-field">
                                        <label>{{ __('crm.email') }} <span class="text-danger">*</span></label>
                                        <input type="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            wire:model="email"
                                            placeholder="{{ __('crm.email_address_placeholder') }}"
                                            required />
                                        @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="cf-grid cf-grid--phones mt-3">
                                    <div class="cf-field">
                                        <label>{{ __('crm.telephone') }} <span class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('telephone') is-invalid @enderror"
                                            wire:model.blur="telephone"
                                            placeholder="{{ __('crm.telephone_placeholder') }}" />
                                        @error('telephone') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="cf-field">
                                        <label>{{ __('crm.mobile') }}</label>
                                        <input type="text"
                                            class="form-control @error('mobile') is-invalid @enderror"
                                            wire:model="mobile"
                                            placeholder="{{ __('crm.mobile_placeholder') }}" />
                                        @error('mobile') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                @include('livewire.crm.partials.contact-signature-fields')
                            </section>

                            <section class="cf-section">
                                <div class="cf-section-head">
                                    <h5>Preferences</h5>
                                    <span>What this contact should receive</span>
                                </div>

                                <div class="cf-prefs">
                                    <label class="cf-pref" for="receiveReport">
                                        <input type="checkbox" id="receiveReport" wire:model="receive_report">
                                        <span>{{ __('crm.receives_report') }}</span>
                                    </label>
                                    <label class="cf-pref" for="receivePriceList">
                                        <input type="checkbox" id="receivePriceList" wire:model="receive_price_list">
                                        <span>{{ __('crm.receives_price_list') }}</span>
                                    </label>
                                    <label class="cf-pref" for="receiveInvoice">
                                        <input type="checkbox" id="receiveInvoice" wire:model="receive_invoice">
                                        <span>{{ __('crm.receives_invoice') }}</span>
                                    </label>
                                    <label class="cf-pref" for="receiveFeedback">
                                        <input type="checkbox" id="receiveFeedback" wire:model="receive_feedback">
                                        <span>{{ __('crm.opt_in_feedback_emails') }}</span>
                                    </label>
                                    <label class="cf-pref" for="isActive">
                                        <input type="checkbox" id="isActive" wire:model="active">
                                        <span>{{ __('crm.is_active') }}</span>
                                    </label>
                                </div>
                            </section>

                            <section class="cf-section">
                                <div class="cf-section-head">
                                    <h5>Portal access</h5>
                                    <span>Optional client portal login</span>
                                </div>

                                <div class="cf-portal">
                                    <label class="cf-portal-toggle" for="createPassword">
                                        <input type="checkbox" id="createPassword" wire:model.live="can_login">
                                        <span>
                                            <strong>{{ __('crm.portal_access') }}</strong>
                                            <small>Create a portal account for this contact. Credentials are emailed automatically.</small>
                                        </span>
                                    </label>

                                    @if($can_login)
                                        <p class="cf-portal-note">
                                            <i class="mdi mdi-email-fast-outline"></i>
                                            <span>
                                                Password will be auto-generated and emailed with a link to
                                                <strong>amspec-portal.imaralims.com</strong>.
                                            </span>
                                        </p>
                                    @endif
                                </div>
                            </section>
                        </div>

                        <div class="modal-footer contact-modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="close" wire:loading.attr="disabled">
                                {{ __('crm.close') }}
                            </button>
                            <button type="button" id="contactFormSaveButton" class="btn btn-primary" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="save">
                                    <i class="mdi mdi-content-save"></i> {{ __('crm.save') }}
                                </span>
                                <span wire:loading wire:target="save">
                                    <i class="mdi mdi-loading mdi-spin"></i> {{ __('crm.saving') }}...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>

@script
<script>
    (function () {
        const loadScript = (src, dataAttr) => new Promise((resolve, reject) => {
            if (document.querySelector(`script[${dataAttr}]`)) {
                resolve();
                return;
            }
            const script = document.createElement('script');
            script.src = src;
            script.setAttribute(dataAttr.split('=')[0], '1');
            script.onload = () => resolve();
            script.onerror = reject;
            document.head.appendChild(script);
        });

        let pad = null;
        let resizeObserver = null;

        const nodes = () => ({
            canvas: document.getElementById('contactFormSignatureCanvas'),
            wrap: document.getElementById('contactFormSignatureCanvasWrap'),
            clearBtn: document.getElementById('clearContactFormSignaturePad'),
            placeholder: document.getElementById('contactFormSignaturePlaceholder'),
            status: document.getElementById('contactFormSignatureStatus'),
            upload: document.getElementById('contactFormSignatureUpload'),
            hidden: document.getElementById('contactFormSignatureData'),
            saveBtn: document.getElementById('contactFormSaveButton'),
        });

        const setStatus = (signed) => {
            const { status, placeholder } = nodes();
            if (status) {
                status.textContent = signed ? 'Signed' : 'Not signed';
                status.classList.toggle('is-signed', signed);
            }
            if (placeholder) {
                placeholder.style.display = signed ? 'none' : 'flex';
            }
        };

        const currentPadDataUrl = () => {
            if (!pad || pad.isEmpty()) {
                return '';
            }

            return pad.toDataURL('image/png');
        };

        const syncToLivewire = async ({ clearIfEmpty = false } = {}) => {
            const drawn = currentPadDataUrl();
            const { hidden } = nodes();

            if (drawn !== '') {
                if (hidden) {
                    hidden.value = drawn;
                }
                await $wire.set('signatureData', drawn);
                await $wire.set('clearExistingSignature', false);
                setStatus(true);
                return drawn;
            }

            if (clearIfEmpty) {
                if (hidden) {
                    hidden.value = '';
                }
                await $wire.set('signatureData', '');
                await $wire.set('clearExistingSignature', true);
                setStatus(false);
                return '';
            }

            // Keep any previously loaded/saved signatureData; do not wipe it because the pad looks empty.
            const existing = $wire.get('signatureData') || '';
            setStatus(existing !== '');
            return existing;
        };

        const resizeCanvas = () => {
            const { canvas, wrap } = nodes();
            if (!canvas || !pad || typeof SignaturePad === 'undefined') {
                return;
            }
            const data = pad.toData();
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const width = Math.max(wrap?.clientWidth || canvas.clientWidth || 620, 200);
            const height = 160;
            canvas.width = width * ratio;
            canvas.height = height * ratio;
            canvas.style.width = width + 'px';
            canvas.style.height = height + 'px';
            canvas.getContext('2d').setTransform(ratio, 0, 0, ratio, 0, 0);
            pad.clear();
            if (data && data.length) {
                pad.fromData(data);
            }
        };

        const initPad = async () => {
            const { canvas, clearBtn, upload, wrap, saveBtn } = nodes();
            if (!canvas) {
                return;
            }

            if (typeof SignaturePad === 'undefined') {
                await loadScript(
                    'https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js',
                    'data-contact-signature-pad'
                );
            }

            if (pad) {
                pad.off();
                pad = null;
            }

            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const width = Math.max(wrap?.clientWidth || canvas.clientWidth || 620, 200);
            canvas.width = width * ratio;
            canvas.height = 160 * ratio;
            canvas.style.width = width + 'px';
            canvas.style.height = '160px';
            canvas.getContext('2d').setTransform(ratio, 0, 0, ratio, 0, 0);

            pad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255,255,255)',
                penColor: 'rgb(17, 24, 39)',
                minWidth: 0.8,
                maxWidth: 2.5,
            });

            const existing = $wire.get('signatureData');
            if (existing && typeof existing === 'string' && existing.indexOf('data:image') === 0) {
                try {
                    await pad.fromDataURL(existing, { ratio });
                } catch (error) {
                    console.error(error);
                }
                setStatus(!pad.isEmpty());
            } else {
                setStatus(false);
            }

            pad.addEventListener('beginStroke', () => setStatus(true));
            pad.addEventListener('endStroke', () => {
                syncToLivewire();
            });

            if (clearBtn && clearBtn.dataset.bound !== '1') {
                clearBtn.dataset.bound = '1';
                clearBtn.addEventListener('click', async (event) => {
                    event.preventDefault();
                    pad.clear();
                    await syncToLivewire({ clearIfEmpty: true });
                });
            }

            if (upload && upload.dataset.bound !== '1') {
                upload.dataset.bound = '1';
                upload.addEventListener('change', async () => {
                    const file = upload.files && upload.files[0] ? upload.files[0] : null;
                    if (!file) {
                        return;
                    }
                    if (!(file.type === 'application/pdf' || /\.pdf$/i.test(file.name))) {
                        return;
                    }
                    try {
                        if (typeof pdfjsLib === 'undefined') {
                            await loadScript(
                                'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js',
                                'data-contact-pdfjs'
                            );
                        }
                        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                        const buffer = await file.arrayBuffer();
                        const pdf = await pdfjsLib.getDocument({ data: buffer }).promise;
                        const page = await pdf.getPage(1);
                        const viewport = page.getViewport({ scale: 1.5 });
                        const offscreen = document.createElement('canvas');
                        offscreen.width = viewport.width;
                        offscreen.height = viewport.height;
                        await page.render({ canvasContext: offscreen.getContext('2d'), viewport }).promise;
                        pad.fromDataURL(offscreen.toDataURL('image/png'));
                        await syncToLivewire();
                        upload.value = '';
                        await $wire.set('signatureUpload', null);
                    } catch (error) {
                        console.error(error);
                        alert('Could not read that PDF. Please upload an image or draw on the pad.');
                    }
                });
            }

            if (wrap && typeof ResizeObserver !== 'undefined') {
                if (resizeObserver) {
                    resizeObserver.disconnect();
                }
                resizeObserver = new ResizeObserver(() => resizeCanvas());
                resizeObserver.observe(wrap);
            }

            if (saveBtn && saveBtn.dataset.bound !== '1') {
                saveBtn.dataset.bound = '1';
                saveBtn.addEventListener('click', async (event) => {
                    event.preventDefault();
                    await syncToLivewire();
                    await $wire.save();
                });
            }
        };

        let attempts = 0;
        const boot = () => {
            attempts += 1;
            const canvas = document.getElementById('contactFormSignatureCanvas');
            const wrap = document.getElementById('contactFormSignatureCanvasWrap');
            if (canvas && wrap && wrap.clientWidth > 0) {
                initPad();
                return;
            }
            if (canvas && attempts > 5) {
                initPad().then(() => setTimeout(resizeCanvas, 100));
                return;
            }
            if (attempts < 40) {
                setTimeout(boot, 50);
            }
        };

        boot();
    })();
</script>
@endscript
