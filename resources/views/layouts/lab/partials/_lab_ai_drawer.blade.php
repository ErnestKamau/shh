{{-- 
    Lab AI Drawer Component
    Provides a collapsible AI assistant sidebar with a floating trigger icon.
--}}

<div id="lab-ai-container">
    {{-- 1. Floating Trigger Icon --}}
    <button id="lab-ai-trigger" title="Open Lab Assistant">
        <i class="mdi mdi-flask-round-bottom"></i>
        <span>Lab AI</span>
    </button>

    {{-- 2. Slide-out Drawer --}}
    <div id="lab-ai-drawer" class="collapsible-drawer">
        <div class="drawer-header">
            <div class="header-title">
                <i class="mdi mdi-brain"></i> Lab Assistant
            </div>
            <button id="lab-ai-close" class="close-btn">&times;</button>
        </div>
        
        <div class="drawer-body">
            {{-- We satisfy the ID requirements for the existing ImaraAI scripts --}}
            <div id="imara-ai-root" class="drawer-imara-wrap">
                <div class="ai-body">
                    {{-- We skip the sidebar for the drawer version to keep it compact --}}
                    {{-- But we need the sidebar container if scripts expect it, so we add it hidden --}}
                    <div style="display:none">
                        @include('layouts.ImaraAi.partials._sidebar')
                    </div>
                    @include('layouts.ImaraAi.partials._main')
                </div>
            </div>
        </div>
    </div>

    {{-- 3. Drawer Overlay --}}
    <div id="lab-ai-overlay" class="drawer-overlay"></div>
</div>

{{-- 4. Styles --}}
<style>
    /* Trigger Button */
    #lab-ai-trigger {
        position: fixed;
        bottom: 25px;
        right: 25px;
        z-index: 1040;
        background: #a72b2a;
        color: #fff;
        border: none;
        border-radius: 50px;
        padding: 12px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 15px rgba(167, 43, 42, 0.35);
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        font-weight: 600;
    }

    #lab-ai-trigger:hover {
        transform: translateY(-5px) scale(1.05);
        background: #8e1c1b;
        box-shadow: 0 8px 25px rgba(167, 43, 42, 0.45);
    }

    #lab-ai-trigger i {
        font-size: 1.4rem;
    }

    /* Drawer Container */
    #lab-ai-drawer {
        position: fixed;
        top: 0;
        right: 0;
        width: 450px;
        max-width: 90vw;
        height: 100vh;
        background: #fff;
        z-index: 1060;
        box-shadow: -5px 0 30px rgba(0,0,0,0.15);
        display: flex;
        flex-direction: column;
        transform: translateX(110%);
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #lab-ai-drawer.open {
        transform: translateX(0);
    }

    /* Drawer Header */
    .drawer-header {
        padding: 15px 20px;
        background: #1e1e1e;
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #333;
    }

    .header-title {
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 1.1rem;
    }

    .header-title i {
        color: #a72b2a;
        font-size: 1.3rem;
    }

    .close-btn {
        background: none;
        border: none;
        color: #888;
        font-size: 2rem;
        cursor: pointer;
        padding: 0;
        line-height: 1;
        transition: color 0.2s;
    }

    .close-btn:hover {
        color: #fff;
    }

    /* Drawer Body */
    .drawer-body {
        flex: 1;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .drawer-imara-wrap {
        height: 100% !important;
        flex: 1 !important;
    }

    /* Adjust existing AI styles for drawer context */
    .drawer-imara-wrap .ai-body {
        height: 100%;
    }

    .drawer-imara-wrap .ai-main {
        height: 100%;
        width: 100%;
        border: none !important;
    }

    /* Overlay */
    .drawer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0,0,0,0.4);
        backdrop-filter: blur(2px);
        z-index: 1055;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    .drawer-overlay.active {
        opacity: 1;
        pointer-events: auto;
    }

    /* Satisfy background requirements of the included styles */
    @include('layouts.ImaraAi.partials._styles')

    /* Fix for when absolute positioning in _styles overrides our flex layout */
    #imara-ai-root {
        height: 100% !important;
        background: #fff !important;
    }
</style>

{{-- 5. Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.2.4/dist/purify.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const trigger = document.getElementById('lab-ai-trigger');
        const drawer = document.getElementById('lab-ai-drawer');
        const overlay = document.getElementById('lab-ai-overlay');
        const closeBtn = document.getElementById('lab-ai-close');

        // Toggle Logic
        function openDrawer() {
            drawer.classList.add('open');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden'; // Prevent background scroll
            
            // Focus the AI input if it exists
            setTimeout(() => {
                const input = document.getElementById('messageInput');
                if (input) input.focus();
            }, 450);
        }

        function closeDrawer() {
            drawer.classList.remove('open');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }

        trigger.addEventListener('click', openDrawer);
        closeBtn.addEventListener('click', closeDrawer);
        overlay.addEventListener('click', closeDrawer);

        // Escape Key Support
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && drawer.classList.contains('open')) {
                closeDrawer();
            }
        });

        // Initialize Imara AI logic in the drawer context
        @include('layouts.ImaraAi.partials._script')

        // Domain-specific metadata injection for Lab module
        if (typeof window.ImaraAiState !== 'undefined') {
            window.ImaraAiState.module_context = 'lab';
            console.log('[Lab AI] Context initialized.');
        }
    });
</script>
