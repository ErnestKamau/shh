@extends('layouts.configuration.layout.app')

@section('title2')
<title>System Preferences & Theming</title>
<style type="text/css">
    /* Premium Theming Panel Styles */
    .theme-card {
        border-radius: 16px;
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.05);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }
    .theme-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.06);
    }
    .preview-pane {
        background: #111827;
        border-radius: 12px;
        padding: 24px;
        min-height: 450px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }
    .preview-pane::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.05) 0%, transparent 50%);
        pointer-events: none;
    }
    .preview-sidebar {
        background: var(--preview-sidebar-bg, #2a2a2a);
        border-radius: 8px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        padding: 16px;
        transition: background-color 0.3s ease;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.3);
    }
    .preview-module-div {
        text-align: center;
        padding: 16px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
        border-radius: 12px;
        margin-bottom: 16px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: inset 0 1px 3px rgba(255, 255, 255, 0.1), inset 0 -1px 3px rgba(0, 0, 0, 0.1), 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    .preview-module-div i {
        font-size: 3.5rem;
        color: var(--preview-primary, #4a90e2);
        display: block;
        margin-bottom: 8px;
        text-shadow: 0 0 20px var(--preview-primary, #4a90e2);
        transition: all 0.3s ease;
    }
    .preview-link {
        display: flex;
        align-items: center;
        padding: 10px 12px;
        color: var(--preview-sidebar-text-muted, rgba(255, 255, 255, 0.6)) !important;
        background: var(--preview-link-bg, rgba(255, 255, 255, 0.05));
        border-radius: 8px;
        margin-bottom: 4px;
        text-decoration: none !important;
        font-size: 14px;
        transition: all 0.3s ease;
        position: relative;
    }
    .preview-link:hover {
        background: var(--preview-link-bg, rgba(255, 255, 255, 0.05));
        transform: translateX(5px) scale(1.02);
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1), 0 6px 20px var(--preview-primary, #4a90e2);
        border: 1px solid var(--preview-primary, #4a90e2);
        padding: 9px 11px; /* compensate for 1px border */
        color: var(--preview-sidebar-text, rgba(255, 255, 255, 0.95)) !important;
    }
    .preview-link.active {
        background: var(--preview-link-bg, rgba(255, 255, 255, 0.05));
        box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.15), 0 4px 15px var(--preview-primary, #4a90e2);
        border: 2px solid transparent;
        background-clip: padding-box;
        padding: 8px 10px; /* compensate for 2px border */
        color: var(--preview-sidebar-text, rgba(255, 255, 255, 0.95)) !important;
    }
    .preview-link.active::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 8px;
        padding: 2px;
        background: linear-gradient(135deg, var(--preview-primary, #4a90e2), var(--preview-secondary, #50e3c2), var(--preview-accent, #f5a623), var(--preview-primary, #4a90e2));
        background-size: 300% 300%;
        -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        -webkit-mask-composite: exclude;
        mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
        mask-composite: exclude;
    }
    /* Inner pseudo content to show standard active look */
    .preview-link-inner {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        width: 100%;
        color: white;
    }
    .preview-link .mdi {
        font-size: 18px;
        margin-right: 8px;
    }
    .preview-button {
        background-color: var(--preview-primary, #4a90e2);
        border-color: var(--preview-primary, #4a90e2);
        color: white;
        padding: 6px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }
    .preset-theme-btn {
        width: 100%;
        text-align: left;
        padding: 10px 14px;
        border: 1px solid rgba(0, 0, 0, 0.06);
        background: #f9fafb;
        border-radius: 10px;
        margin-bottom: 8px;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .preset-theme-btn:hover {
        background: #f3f4f6;
        border-color: rgba(0, 0, 0, 0.12);
        transform: scale(1.02);
    }
    .color-swatch-group {
        display: flex;
        gap: 6px;
    }
    .color-swatch {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }
    .form-control-color {
        width: 48px;
        height: 38px;
        padding: 4px;
        border-radius: 8px;
    }
</style>
@endsection

@section('content2')
@php
    $sysThemePrimary = optional(getConfigByName('sys_theme_primary_color')->first())->value ?? '#4a90e2';
    $sysThemeSecondary = optional(getConfigByName('sys_theme_secondary_color')->first())->value ?? '#50e3c2';
    $sysThemeAccent = optional(getConfigByName('sys_theme_accent_color')->first())->value ?? '#f5a623';
    $sysSidebarBg = optional(getConfigByName('sys_sidebar_bg_color')->first())->value ?? '#2a2a2a';
    $sysSidebarLinkBg = optional(getConfigByName('sys_sidebar_link_bg')->first())->value ?? 'rgba(255, 255, 255, 0.05)';
@endphp
<main>
    <?php 
        $items = array(
            array(
                'link'=>route('system-settings.preferences'),
                'name'=>'System Theming',
                'icon'=>null
            )
        );
    ?>
    <x-bread-crumb :items="$items"></x-bread-crumb>
    
    <h2 class="p-4">
        <i class="mdi mdi-palette"></i> System Theming
    </h2>

    <div class="container-fluid px-2">

    <div class="row">
        <!-- Controls Column -->
        <div class="col-lg-7">
            <div class="card theme-card mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3 text-bold"><i class="mdi mdi-palette-outline text-primary mr-1"></i> Global Custom Branding</h5>
                    
                    <form action="{{ route('system-settings.preferences.update') }}" method="POST">
                        @csrf
                        <div class="row mb-4">
                            <!-- Sidebar Background Color -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label text-bold mb-1 d-block">Sidebar Background Color</label>
                                <span class="text-xs text-muted mb-2 d-block">The main background color of the navigation sidebar.</span>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="color" name="sys_sidebar_bg_color" id="sidebarBgInput" class="form-control-color" value="{{ $sysSidebarBg }}">
                                    <input type="text" id="sidebarBgText" class="form-control" style="border-radius: 8px; max-width: 150px;" value="{{ $sysSidebarBg }}" pattern="^#[a-fA-F0-9]{6}$" placeholder="#2a2a2a">
                                </div>
                            </div>
                            
                            <!-- Sidebar Link Background -->
                            <div class="col-md-12 mb-4 pb-3 border-bottom">
                                <label class="form-label text-bold mb-1 d-block">Sidebar Link Background</label>
                                <span class="text-xs text-muted mb-2 d-block">Background color or rgba value for inactive sidebar links.</span>
                                <input type="text" name="sys_sidebar_link_bg" id="sidebarLinkBgInput" class="form-control" style="border-radius: 8px; max-width: 250px;" value="{{ $sysSidebarLinkBg }}" placeholder="rgba(255, 255, 255, 0.05)">
                            </div>

                            <!-- Primary Highlight Color -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label text-bold mb-1 d-block">Primary Highlight Color</label>
                                <span class="text-xs text-muted mb-2 d-block">Used for active sidebar icons, principal highlights, and active borders.</span>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="color" name="sys_theme_primary_color" id="primaryColorInput" class="form-control-color" value="{{ $sysThemePrimary }}">
                                    <input type="text" id="primaryColorText" class="form-control" style="border-radius: 8px; max-width: 150px;" value="{{ $sysThemePrimary }}" pattern="^#[a-fA-F0-9]{6}$" placeholder="#ffffff">
                                </div>
                            </div>

                            <!-- Secondary Highlight Color -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label text-bold mb-1 d-block">Secondary Highlight Color</label>
                                <span class="text-xs text-muted mb-2 d-block">Used for auxiliary highlights and second-order gradients.</span>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="color" name="sys_theme_secondary_color" id="secondaryColorInput" class="form-control-color" value="{{ $sysThemeSecondary }}">
                                    <input type="text" id="secondaryColorText" class="form-control" style="border-radius: 8px; max-width: 150px;" value="{{ $sysThemeSecondary }}" pattern="^#[a-fA-F0-9]{6}$" placeholder="#ffffff">
                                </div>
                            </div>

                            <!-- Accent Color -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label text-bold mb-1 d-block">Accent Color</label>
                                <span class="text-xs text-muted mb-2 d-block">Used in the primary three-color background gradient to bring module icons to life.</span>
                                <div class="d-flex align-items-center gap-3">
                                    <input type="color" name="sys_theme_accent_color" id="accentColorInput" class="form-control-color" value="{{ $sysThemeAccent }}">
                                    <input type="text" id="accentColorText" class="form-control" style="border-radius: 8px; max-width: 150px;" value="{{ $sysThemeAccent }}" pattern="^#[a-fA-F0-9]{6}$" placeholder="#ffffff">
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                            <button type="button" id="resetDefaultBtn" class="btn btn-outline-secondary" style="border-radius: 8px;">
                                <i class="mdi mdi-refresh"></i> Reset Defaults
                            </button>
                            <button type="submit" class="btn btn-primary px-4" style="border-radius: 8px; background: #6366f1; border-color: #6366f1;">
                                <i class="mdi mdi-check"></i> Save Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Preset Themes -->
            <div class="card theme-card mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-3 text-bold"><i class="mdi mdi-star-circle-outline text-warning mr-1"></i> Premium Presets</h5>
                    <p class="text-xs text-muted mb-3">Click on any preset below to load a professionally designed global palette instantly.</p>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="preset-theme-btn" data-bg="#2a2a2a" data-primary="#4a90e2" data-secondary="#50e3c2" data-accent="#f5a623">
                                <span class="text-bold">Classic Imara Blue</span>
                                <div class="color-swatch-group">
                                    <div class="color-swatch" style="background: #2a2a2a;"></div>
                                    <div class="color-swatch" style="background: #4a90e2;"></div>
                                </div>
                            </button>
                        </div>
                        
                        <div class="col-md-6">
                            <button type="button" class="preset-theme-btn" data-bg="#0f172a" data-primary="#10b981" data-secondary="#06b6d4" data-accent="#3b82f6">
                                <span class="text-bold">Emerald Night</span>
                                <div class="color-swatch-group">
                                    <div class="color-swatch" style="background: #0f172a;"></div>
                                    <div class="color-swatch" style="background: #10b981;"></div>
                                </div>
                            </button>
                        </div>

                        <div class="col-md-6">
                            <button type="button" class="preset-theme-btn" data-bg="#1a1a1a" data-primary="#ef4444" data-secondary="#f97316" data-accent="#eab308">
                                <span class="text-bold">Sunset Fire</span>
                                <div class="color-swatch-group">
                                    <div class="color-swatch" style="background: #1a1a1a;"></div>
                                    <div class="color-swatch" style="background: #ef4444;"></div>
                                </div>
                            </button>
                        </div>

                        <div class="col-md-6">
                            <button type="button" class="preset-theme-btn" data-bg="#000000" data-primary="#8b5cf6" data-secondary="#ec4899" data-accent="#3b82f6">
                                <span class="text-bold">Midnight Neon</span>
                                <div class="color-swatch-group">
                                    <div class="color-swatch" style="background: #000000;"></div>
                                    <div class="color-swatch" style="background: #8b5cf6;"></div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Column -->
        <div class="col-lg-5">
            <div class="card theme-card mb-4" style="position: sticky; top: 20px;">
                <div class="card-body p-4">
                    <h5 class="mb-3 text-bold"><i class="mdi mdi-eye-outline text-success mr-1"></i> Multi-Module Live Preview</h5>
                    
                    <select id="previewModuleSelector" class="form-control mb-3" style="border-radius: 8px;">
                        <option value="lab">Lab Management</option>
                        <option value="crm">CRM Dashboard</option>
                        <option value="inventory">Inventory & Logistics</option>
                        <option value="settings">System Settings</option>
                    </select>

                    <p class="text-xs text-muted mb-3">Watch the sidebar preview adapt in real-time!</p>
                    
                    <div class="preview-pane" id="livePreviewPane">
                        <!-- Sidebar Mockup -->
                        <div class="preview-sidebar">
                            <div class="preview-module-div">
                                <i class="mdi mdi-flask" id="previewIcon"></i>
                                <span class="text-bold text-xs d-block" id="previewTitle" style="color: var(--preview-sidebar-text, rgba(255, 255, 255, 0.95)) !important;">LAB MANAGEMENT</span>
                            </div>
                            
                            <a href="#" class="preview-link active">
                                <div class="preview-link-inner">
                                    <span class="mdi mdi-desktop-mac-dashboard" id="previewLink1Icon"></span>
                                    <span id="previewLink1Text">Dashboard</span>
                                </div>
                            </a>
                            
                            <a href="#" class="preview-link">
                                <div class="preview-link-inner">
                                    <span class="mdi mdi-file-document-edit-outline" id="previewLink2Icon"></span>
                                    <span id="previewLink2Text">Sample Workflow</span>
                                </div>
                            </a>
                        </div>
                        
                        <!-- Content Card Mockup -->
                        <div class="mt-4 p-3 border border-secondary rounded text-white" style="background: var(--preview-sidebar-bg, #2a2a2a);">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-xs text-bold">Module Overview</span>
                                <button type="button" class="preview-button">Analyze</button>
                            </div>
                            <div style="height: 4px; background: var(--preview-primary, #4a90e2); border-radius: 2px;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</main>
@endsection

@section('script2')
<script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        const primaryInput = document.getElementById('primaryColorInput');
        const primaryText = document.getElementById('primaryColorText');
        const secondaryInput = document.getElementById('secondaryColorInput');
        const secondaryText = document.getElementById('secondaryColorText');
        const accentInput = document.getElementById('accentColorInput');
        const accentText = document.getElementById('accentColorText');
        const sidebarBgInput = document.getElementById('sidebarBgInput');
        const sidebarBgText = document.getElementById('sidebarBgText');
        const sidebarLinkBgInput = document.getElementById('sidebarLinkBgInput');
        
        const previewPane = document.getElementById('livePreviewPane');

        // Module preview definitions
        const modulesData = {
            'lab': { icon: 'mdi-flask', title: 'LAB MANAGEMENT', l1Icon: 'mdi-desktop-mac-dashboard', l1Text: 'Dashboard', l2Icon: 'mdi-file-document-edit-outline', l2Text: 'Sample Workflow' },
            'crm': { icon: 'mdi-domain', title: 'CRM DASHBOARD', l1Icon: 'mdi-account-group', l1Text: 'Customers', l2Icon: 'mdi-phone-in-talk', l2Text: 'Communications' },
            'inventory': { icon: 'mdi-truck', title: 'INVENTORY', l1Icon: 'mdi-package-variant', l1Text: 'Stock Registry', l2Icon: 'mdi-truck-delivery', l2Text: 'Dispatch' },
            'settings': { icon: 'mdi-cogs', title: 'SYSTEM SETTINGS', l1Icon: 'mdi-palette', l1Text: 'Preferences', l2Icon: 'mdi-account-cog', l2Text: 'User Roles' }
        };

        const moduleSelector = document.getElementById('previewModuleSelector');
        moduleSelector.addEventListener('change', function() {
            const data = modulesData[this.value];
            document.getElementById('previewIcon').className = 'mdi ' + data.icon;
            document.getElementById('previewTitle').innerText = data.title;
            document.getElementById('previewLink1Icon').className = 'mdi ' + data.l1Icon;
            document.getElementById('previewLink1Text').innerText = data.l1Text;
            document.getElementById('previewLink2Icon').className = 'mdi ' + data.l2Icon;
            document.getElementById('previewLink2Text').innerText = data.l2Text;
        });

        function updatePreview() {
            previewPane.style.setProperty('--preview-primary', primaryInput.value);
            previewPane.style.setProperty('--preview-secondary', secondaryInput.value);
            previewPane.style.setProperty('--preview-accent', accentInput.value);
            previewPane.style.setProperty('--preview-sidebar-bg', sidebarBgInput.value);
            previewPane.style.setProperty('--preview-link-bg', sidebarLinkBgInput.value);

            // Dynamic text YIQ contrast calculation
            const hex = sidebarBgInput.value.replace('#', '');
            let r = parseInt(hex.substr(0, 2), 16) || 0;
            let g = parseInt(hex.substr(2, 2), 16) || 0;
            let b = parseInt(hex.substr(4, 2), 16) || 0;
            if (hex.length === 3) {
                r = parseInt(hex[0] + hex[0], 16) || 0;
                g = parseInt(hex[1] + hex[1], 16) || 0;
                b = parseInt(hex[2] + hex[2], 16) || 0;
            }
            const yiq = ((r * 299) + (g * 587) + (b * 114)) / 1000;
            const sidebarText = (yiq >= 128) ? 'rgba(0, 0, 0, 0.85)' : 'rgba(255, 255, 255, 0.95)';
            const sidebarTextMuted = (yiq >= 128) ? 'rgba(0, 0, 0, 0.55)' : 'rgba(255, 255, 255, 0.6)';

            previewPane.style.setProperty('--preview-sidebar-text', sidebarText);
            previewPane.style.setProperty('--preview-sidebar-text-muted', sidebarTextMuted);
        }

        function setupColorBinding(inputEl, textEl) {
            inputEl.addEventListener('input', function() {
                textEl.value = inputEl.value;
                updatePreview();
            });
            textEl.addEventListener('input', function() {
                if (/^#[a-fA-F0-9]{6}$/.test(textEl.value)) {
                    inputEl.value = textEl.value;
                    updatePreview();
                }
            });
        }

        setupColorBinding(primaryInput, primaryText);
        setupColorBinding(secondaryInput, secondaryText);
        setupColorBinding(accentInput, accentText);
        setupColorBinding(sidebarBgInput, sidebarBgText);

        sidebarLinkBgInput.addEventListener('input', updatePreview);

        document.querySelectorAll('.preset-theme-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const bg = this.getAttribute('data-bg');
                const primary = this.getAttribute('data-primary');
                const secondary = this.getAttribute('data-secondary');
                const accent = this.getAttribute('data-accent');

                sidebarBgInput.value = bg;
                sidebarBgText.value = bg;
                primaryInput.value = primary;
                primaryText.value = primary;
                secondaryInput.value = secondary;
                secondaryText.value = secondary;
                accentInput.value = accent;
                accentText.value = accent;
                
                // Adjust link BG based on dark theme
                sidebarLinkBgInput.value = 'rgba(255, 255, 255, 0.05)';

                updatePreview();
            });
        });

        document.getElementById('resetDefaultBtn').addEventListener('click', function() {
            sidebarBgInput.value = '#2a2a2a';
            sidebarBgText.value = '#2a2a2a';
            primaryInput.value = '#4a90e2';
            primaryText.value = '#4a90e2';
            secondaryInput.value = '#50e3c2';
            secondaryText.value = '#50e3c2';
            accentInput.value = '#f5a623';
            accentText.value = '#f5a623';
            sidebarLinkBgInput.value = 'rgba(255, 255, 255, 0.05)';
            updatePreview();
        });

        updatePreview();
    });
</script>
@endsection
