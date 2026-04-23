<div x-data="{ open: @entangle('isOpen') }">
    {{-- Floating Trigger Button --}}
    <div 
        class="ai-drawer-trigger" 
        :class="{ 'active': open }"
        @click="open = !open"
        title="AI Assistant"
        style="z-index: 10002;"
    >
        <i class="mdi mdi-head-snowflake"></i>
    </div>

    {{-- Backdrop --}}
    <div 
        class="ai-drawer-backdrop" 
        :class="{ 'show': open }"
        @click="open = false"
        style="z-index: 10003;"
    ></div>

    {{-- Main Drawer --}}
    <div 
        class="ai-drawer" 
        :class="{ 'open': open }" 
        id="ai-drawer-container"
        style="z-index: 10004;"
    >
        <div class="ai-drawer-header">
            <div class="d-flex align-items-center">
                <div class="ai-logo-small bg-primary mr-3" style="background-color: #a72b2a !important;">
                    <i class="mdi mdi-robot text-white"></i>
                </div>
                <div>
                    <h5 class="mb-0 font-weight-bold" style="color: #1a2a3a;">Imara AI</h5>
                    <small class="text-{{ $context == 'lab' ? 'success' : 'primary' }} font-weight-bold text-uppercase" style="letter-spacing: 0.1em; font-size: 0.65rem;">
                        {{ $context }} Assistant
                    </small>
                </div>
            </div>
            <button class="btn btn-transparent p-0 ml-auto" @click="open = false">
                <i class="mdi mdi-close fa-2x text-muted"></i>
            </button>
        </div>

        <div class="ai-drawer-body" id="aiDrawerMessages">
            <div class="welcome-message text-center p-4">
                <div class="mb-3">
                    <i class="mdi mdi-comment-question-outline fa-3x text-light" style="color: #e2e8f0 !important;"></i>
                </div>
                <h6 class="font-weight-bold">How can I help you today?</h6>
                <p class="text-muted small">I can help with SOPs, Lab Metrics, and data analysis.</p>
            </div>
        </div>

        <div class="ai-drawer-footer">
            <div class="ai-input-wrapper">
                <textarea 
                    id="aiDrawerInput" 
                    placeholder="Ask a question..."
                    rows="1"
                ></textarea>
                <button id="aiDrawerSend" class="btn btn-primary btn-circle" style="background-color: #a72b2a; border: none;">
                    <i class="mdi mdi-send"></i>
                </button>
            </div>
        </div>
    </div>

    <style>
        .ai-drawer-trigger {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #a72b2a, #d9534f);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(167, 43, 42, 0.35);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            color: white;
            font-size: 24px;
        }
        .ai-drawer-trigger:hover {
            transform: scale(1.1);
            box-shadow: 0 12px 25px rgba(167, 43, 42, 0.45);
        }
        .ai-drawer-trigger.active {
            transform: scale(0);
            opacity: 0;
            pointer-events: none;
        }

        .ai-drawer-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.3);
            backdrop-filter: blur(2px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }
        .ai-drawer-backdrop.show {
            opacity: 1;
            pointer-events: auto;
        }

        .ai-drawer {
            position: fixed;
            top: 0;
            right: -580px;
            width: 550px;
            height: 100vh;
            background: #fff;
            box-shadow: -10px 0 40px rgba(0,0,0,0.15);
            transition: right 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }
        .ai-drawer.open {
            right: 0;
        }

        .ai-drawer-header {
            padding: 15px 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
        }
        .ai-logo-small {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .ai-drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background-color: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .ai-drawer-footer {
            padding: 15px 20px;
            border-top: 1px solid #f1f5f9;
            background: #fff;
        }

        .ai-input-wrapper {
            position: relative;
            background: #f1f5f9;
            border-radius: 12px;
            padding: 8px 45px 8px 15px;
            border: 1px solid #e2e8f0;
        }
        .ai-input-wrapper textarea {
            width: 100%;
            border: none;
            background: transparent;
            resize: none;
            outline: none;
            font-size: 0.9rem;
            max-height: 120px;
            padding: 5px 0;
        }

        .drawer-msg { padding: 10px 14px; border-radius: 12px; max-width: 85%; font-size: 0.9rem; line-height: 1.5; }
        .drawer-msg-user { align-self: flex-end; background: #a72b2a; color: white; border-bottom-right-radius: 2px; }
        .drawer-msg-bot { align-self: flex-start; background: white; color: #1e293b; border-bottom-left-radius: 2px; border: 1px solid #e2e8f0; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
             // We use DOMContentLoaded because the script is static and the elements are in the root view.
             const input = document.getElementById('aiDrawerInput');
             const sendBtn = document.getElementById('aiDrawerSend');
             const messagesContainer = document.getElementById('aiDrawerMessages');
             let drawerConvoId = null;

             if (!input || !sendBtn) return;

             const addMessage = (text, role) => {
                const div = document.createElement('div');
                div.className = `drawer-msg drawer-msg-${role}`;
                div.textContent = text;
                messagesContainer.appendChild(div);
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
             };

             const sendMessage = async () => {
                const text = input.value.trim();
                if (!text) return;

                input.value = '';
                addMessage(text, 'user');

                try {
                    if (!drawerConvoId) {
                        const convoRes = await fetch('/imara-ai/conversations', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ title: text.substring(0, 50), context: '{{ $context }}' })
                        });
                        const convoData = await convoRes.json();
                        drawerConvoId = convoData.conversation.id;
                    }

                    await fetch(`/imara-ai/conversations/${drawerConvoId}/messages`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ role: 'user', content: text })
                    });

                    const aiRes = await fetch('/imara-ai/ask', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ question: text })
                    });
                    const aiData = await aiRes.json();
                    
                    addMessage(aiData.reply || 'No response.', 'bot');

                    await fetch(`/imara-ai/conversations/${drawerConvoId}/messages`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ role: 'bot', content: aiData.reply })
                    });
                } catch (e) {
                    addMessage('Error connecting to AI service.', 'bot');
                }
             };

             sendBtn.addEventListener('click', sendMessage);
             input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
             });
        });
    </script>
</div>
