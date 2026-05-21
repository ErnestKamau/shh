<div x-data="{ 
    open: @entangle('isOpen'),
    userInput: '',
    messages: [],
    convoId: null,
    isThinking: false,

    async sendMessage() {
        if (!this.userInput.trim() || this.isThinking) return;
        
        const text = this.userInput;
        this.userInput = '';
        this.messages.push({ role: 'user', content: text });
        this.isThinking = true;

        this.$nextTick(() => { this.scrollToBottom(); });

        try {
            // Create conversation if needed
            if (!this.convoId) {
                const convoRes = await fetch('/imara-ai/conversations', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content },
                    body: JSON.stringify({ title: text.substring(0, 50), context: '{{ $context }}', mode: '{{ $context }}' })
                });
                const convoData = await convoRes.json();
                this.convoId = convoData.conversation.id;
            }

            // Create bot placeholder
            this.messages.push({ role: 'bot', content: '' });
            const botMsgIndex = this.messages.length - 1;

            const aiRes = await fetch('/imara-ai/ask-stream', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content },
                body: JSON.stringify({ question: text, module_context: '{{ $context }}', mode: '{{ $context }}' })
            });

            const reader = aiRes.body.getReader();
            const decoder = new TextDecoder('utf-8');
            let fullReply = '';

            while (true) {
                const { done, value } = await reader.read();
                if (done) break;
                const chunk = decoder.decode(value, { stream: true });
                const lines = chunk.split('\n');
                for (const line of lines) {
                    if (line.startsWith('data: ')) {
                        const dataStr = line.substring(6).trim();
                        if (dataStr === '[DONE]') continue;
                        try {
                            const data = JSON.parse(dataStr);
                            if (data.token) {
                                fullReply += data.token;
                                this.messages[botMsgIndex].content = fullReply;
                                this.scrollToBottom();
                            }
                        } catch (e) {}
                    }
                }
            }
        } catch (e) {
            this.messages.push({ role: 'bot', content: 'Connection error.' });
        } finally {
            this.isThinking = false;
        }
    },

    formatMessage(text) {
        if (!text) return '';
        try {
            const rawHtml = marked.parse(text || '');
            return DOMPurify.sanitize(rawHtml);
        } catch (e) {
            return text;
        }
    },

    scrollToBottom() {
        const container = document.getElementById('aiDrawerMessages');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }
    }
}" x-init="window.marked?.setOptions({ breaks: true, gfm: true, headerIds: false, mangle: false });">
    {{-- Floating Trigger Button --}}
    <div
        class="ai-drawer-trigger" 
        :class="{ 'active': open }"
        @click="open = true"
        title="AI Assistant"
        style="z-index: 999999 !important; position: fixed; bottom: 30px; right: 30px;"
    >
        <i class="mdi mdi-{{ $this->icon }}"></i>
    </div>

    <template x-teleport="body">
        <div x-show="open" style="display: none;">
            {{-- Backdrop --}}
            <div
                class="ai-drawer-backdrop show" 
                @click="open = false"
                style="z-index: 1000000 !important;"
            ></div>

            {{-- Main Drawer --}}
            <div
                class="ai-drawer open" 
                id="ai-drawer-container"
                style="z-index: 1000001 !important;"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
            >
                <div class="ai-drawer-header">
                    <div class="d-flex align-items-center">
                        <div class="ai-logo-small bg-primary mr-3" style="background-color: #a72b2a !important;">
                            <i class="mdi mdi-{{ $this->icon }} text-white"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 font-weight-bold" style="color: #1a2a3a;">Imara AI</h5>
                            <small class="text-{{ $context == 'lab' ? 'success' : 'primary' }} font-weight-bold text-uppercase" style="letter-spacing: 0.1em; font-size: 0.65rem;">
                                {{ $context }} Assistant
                            </small>
                        </div>
                    </div>
                    <button class="btn btn-transparent p-0 ml-auto" @click="open = false" type="button">
                        <i class="mdi mdi-close fa-2x text-muted"></i>
                    </button>
                </div>

                <div class="ai-drawer-body" id="aiDrawerMessages">
                    <div class="welcome-message text-center p-4" x-show="messages.length === 0">
                        <div class="mb-3">
                            <i class="mdi mdi-comment-question-outline fa-3x text-light" style="color: #e2e8f0 !important;"></i>
                        </div>
                        <h6 class="font-weight-bold">How can I help you today?</h6>
                        <p class="text-muted small">{{ $this->welcomeMessage }}</p>
                    </div>

                    <template x-for="(msg, index) in messages" :key="index">
                        <div 
                            class="drawer-msg" 
                            :class="msg.role === 'user' ? 'drawer-msg-user' : 'drawer-msg-bot'"
                            x-html="msg.role === 'bot' ? formatMessage(msg.content) : msg.content"
                        ></div>
                    </template>

                    <div x-show="isThinking && messages[messages.length-1]?.role === 'user'" class="drawer-msg drawer-msg-bot">
                        <i class="mdi mdi-dots-horizontal mdi-spin"></i>
                    </div>
                </div>

                <div class="ai-drawer-footer">
                    <div class="ai-input-wrapper">
                        <textarea 
                            x-model="userInput"
                            @keydown.enter.prevent="sendMessage()"
                            placeholder="Ask a question..."
                            rows="1"
                        ></textarea>
                        <button @click="sendMessage()" class="btn btn-primary btn-circle" style="background-color: #a72b2a; border: none;">
                            <i class="mdi mdi-send"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <style>
        .ai-drawer-trigger { width: 60px; height: 60px; background: linear-gradient(135deg, #a72b2a, #d9534f); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 20px rgba(167, 43, 42, 0.35); cursor: pointer; transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); color: white; font-size: 24px; }
        .ai-drawer-trigger:hover { transform: scale(1.1); box-shadow: 0 12px 25px rgba(167, 43, 42, 0.45); }
        .ai-drawer-trigger.active { transform: scale(0); opacity: 0; pointer-events: none; }
        .ai-drawer-backdrop { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(15, 23, 42, 0.3); backdrop-filter: blur(2px); }
        .ai-drawer { position: fixed; top: 0; right: 0; width: 550px; height: 100vh; background: #fff; box-shadow: -10px 0 40px rgba(0,0,0,0.15); display: flex; flex-direction: column; }
        .ai-drawer-header { padding: 15px 20px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; }
        .ai-logo-small { width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .ai-drawer-body { flex: 1; overflow-y: auto; padding: 20px; background-color: #f8fafc; display: flex; flex-direction: column; gap: 15px; }
        .ai-drawer-footer { padding: 15px 20px; border-top: 1px solid #f1f5f9; background: #fff; }
        .ai-input-wrapper { position: relative; background: #f1f5f9; border-radius: 12px; padding: 8px 45px 8px 15px; border: 1px solid #e2e8f0; }
        .ai-input-wrapper textarea { width: 100%; border: none; background: transparent; resize: none; outline: none; font-size: 0.9rem; max-height: 120px; padding: 5px 0; }
        .drawer-msg { padding: 10px 14px; border-radius: 12px; max-width: 85%; font-size: 0.9rem; line-height: 1.5; overflow-wrap: break-word; }
        .drawer-msg-user { align-self: flex-end; background: #a72b2a !important; color: white !important; border-bottom-right-radius: 2px; }
        .drawer-msg-bot { align-self: flex-start; background: white; color: #1e293b; border-bottom-left-radius: 2px; border: 1px solid #e2e8f0; }

        /* Markdown Styles */
        .drawer-msg table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 0.85rem; background: white; }
        .drawer-msg th, .drawer-msg td { border: 1px solid #e2e8f0; padding: 6px 10px; text-align: left; }
        .drawer-msg th { background: #f8fafc; font-weight: 600; }
        .drawer-msg p:last-child { margin-bottom: 0; }
        .drawer-msg pre { background: #1e293b; color: #f8fafc; padding: 10px; border-radius: 6px; overflow-x: auto; margin: 10px 0; }
        .drawer-msg code { font-family: monospace; background: rgba(0,0,0,0.05); padding: 2px 4px; border-radius: 4px; }
        .drawer-msg pre code { background: transparent; padding: 0; color: inherit; }
        .drawer-msg ul, .drawer-msg ol { padding-left: 20px; margin-bottom: 10px; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.2.4/dist/purify.min.js"></script>
</div>
