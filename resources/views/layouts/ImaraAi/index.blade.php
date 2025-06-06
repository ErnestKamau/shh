@extends('layouts.app')

@section('module-name')
<li class="nav-item">
	<a class="nav-link module-name" href="{{ route('imara-ai-index') }}"><i class="mdi mdi-chip"></i> Imara AI</a>
</li>
@endsection

@section('title')
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        background: white;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        padding: 10px;
    }

    .chat-container {
        width: 100%;
        margin: 3%;
        /* max-width: ; */
        /* height: calc(100vh - 20px); */
        height:85vh;
        /* max-height: 70vh;
        min-height: 500px; */
        background: rgba(255, 255, 255, 0.95);
        border-radius: 16px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        backdrop-filter: blur(10px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .chat-header {
        background: linear-gradient(135deg, #a72b2a 0%, #a72b2a 100%);
        color: white;
        padding: 16px 20px;
        text-align: center;
        flex-shrink: 0;
    }

    .chat-header h1 {
        font-size: 1.3rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }
    
    /* MODIFIED FOR RESPONSIVENESS */
    .chat-header .lims-logo {
        width: 32px;
        height: 32px;
    }

    .status-indicator {
        width: 8px;
        height: 8px;
        background: #4ade80;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    .chat-messages {
        flex: 1;
        padding: 16px;
        padding-bottom: 60px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
        scroll-behavior: smooth;
    }

    .chat-messages::-webkit-scrollbar {
        width: 6px;
    }

    .chat-messages::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .chat-messages::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .chat-messages::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    .message-wrapper {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        max-width: 85%;
        animation: slideIn 0.3s ease-out;
        margin-bottom: 8px;
    }

    .message-wrapper.user {
        align-self: flex-end;
        flex-direction: row-reverse;
    }

    .message-wrapper.bot {
        align-self: flex-start;
    }

    /* --- START: UPDATED RESPONSIVE CODE --- */

    .lims-logo {
        width: 100%; /* Make the image fill its container */
        height: auto; /* Maintain aspect ratio */
        display: block; /* Remove any extra space below the image */
    }

    .message-avatar {
        width: 32px; /* Set a base size for larger screens */
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 4px;
        overflow: hidden; /* Ensure content outside the circle is hidden */
    }

    .message-avatar.user {
        background: transparent;
        border: 2px solid #4CAF50;
    }

    .message-avatar.bot {
        background: #f8fafc;
        padding: 4px; /* Padding to create space around the logo */
    }

    .user-icon {
        font-size: 1.2rem; /* Use a responsive font size unit */
        color: #4CAF50;
    }
    
    /* --- END: UPDATED RESPONSIVE CODE --- */

    .message {
        padding: 12px 16px;
        border-radius: 16px;
        word-wrap: break-word;
        line-height: 1.4;
        flex: 1;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .user-message {
        background: linear-gradient(135deg, #a72b2a 0%, #a72b2a 100%);
        color: white;
        border-bottom-right-radius: 4px;
    }

    .bot-message {
        background: #f8fafc;
        color: #334155;
        border: 2px solid #a72b2a;
        border-bottom-left-radius: 4px;
    }

    .error-message {
        background: #fef2f2;
        color: #a72b2a;
        border: 2px solid #fecaca;
        border-bottom-left-radius: 4px;
    }

    .thinking-wrapper {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        max-width: 85%;
        align-self: flex-start;
        margin-bottom: 60px;
        animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .thinking-message {
        background: #f8fafc;
        color: #64748b;
        border: 2px solid #8B4513;
        border-bottom-left-radius: 4px;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        border-radius: 16px;
        flex: 1;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .thinking-dots {
        display: flex;
        gap: 6px;
    }

    .thinking-dot {
        width: 10px;
        height: 10px;
        background: #64748b;
        border-radius: 50%;
        animation: thinking 1.4s infinite ease-in-out;
    }

    .thinking-dot:nth-child(1) { animation-delay: -0.32s; }
    .thinking-dot:nth-child(2) { animation-delay: -0.16s; }
    .thinking-dot:nth-child(3) { animation-delay: 0s; }

    @keyframes thinking {
        0%, 80%, 100% {
            transform: scale(0.6);
            opacity: 0.5;
        }
        40% {
            transform: scale(1);
            opacity: 1;
        }
    }

    .chat-input-container {
        padding: 20px 20px 24px;
        background: white;
        border-top: 1px solid #e2e8f0;
        flex-shrink: 0;
    }

    .input-wrapper {
        display: flex;
        gap: 8px;
        align-items: flex-end;
    }

    #messageInput {
        flex: 1;
        padding: 12px 16px;
        border: 2px solid #a72b2a;
        border-radius: 20px;
        font-size: 16px;
        outline: none;
        transition: all 0.3s ease;
        resize: none;
        min-height: 44px;
        overflow: hidden;
        max-height: 120px;
        font-family: inherit;
    }

    #messageInput:focus {
        border-color: #a72b2a;
        box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
    }

    #sendButton {
        padding: 12px;
        background: linear-gradient(135deg, #a72b2a 0%, #a72b2a 100%);
        color: white;
        border: none;
        border-radius: 50%;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    #sendButton svg {
        transform: rotate(-5deg);
        transition: transform 0.3s ease;
    }

    #sendButton:hover:not(:disabled) {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(139, 69, 19, 0.3);
    }

    #sendButton:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }

    .welcome-message {
        text-align: center;
        color: #64748b;
        padding: 40px 20px;
        border-radius: 12px;
        background: #f8fafc;
        border: 2px solid #a72b2a;
        margin-bottom: 20px;
    }

    .welcome-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #a72b2a;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
    }

    .welcome-subtitle {
        font-size: 1rem;
        margin-bottom: 0;
    }

    /* Mobile Responsiveness */
    @media (max-width: 768px) {
        body {
            padding: 5px;
        }
        
        .chat-container {
            height: calc(100vh - 10px);
            border-radius: 12px;
            max-height: none;
        }
        
        .chat-header {
            padding: 12px 16px;
        }
        
        .chat-header h1 {
            font-size: 1.1rem;
        }
        
        .chat-header .lims-logo {
            width: 28px;
            height: 28px;
        }
        
        .chat-messages {
            padding: 12px;
            padding-bottom: 40px;
            gap: 10px;
        }
        
        .message-wrapper {
            max-width: 90%;
            margin-bottom: 6px;
        }
        
        .message {
            padding: 10px 14px;
            font-size: 15px;
        }
        
        /* --- START: UPDATED RESPONSIVE CODE FOR TABLETS --- */
        .message-avatar {
            width: 28px; /* Slightly smaller on tablets */
            height: 28px;
        }

        .user-icon {
            font-size: 1rem; /* Adjust font size for the smaller avatar */
        }
        /* --- END: UPDATED RESPONSIVE CODE FOR TABLETS --- */

        .chat-input-container {
            padding: 16px 16px 20px;
        }
        
        #messageInput {
            font-size: 16px;
            padding: 10px 14px;
        }
        
        #sendButton {
            min-width: 40px;
            padding: 8px;
            height: 40px;
            font-size: 16px;
            width: 40px;
        }
        
        .welcome-message {
            padding: 30px 16px;
            margin: 0 4px 16px 4px;
        }
        
        .welcome-title {
            font-size: 1.3rem;
        }
        
        .thinking-wrapper {
            margin-bottom: 50px;
        }
        
        .thinking-message {
            padding: 10px 14px;
        }
    }

    @media (max-width: 480px) {
        .chat-messages {
            padding: 8px;
            padding-bottom: 32px;
        }
        
        .message {
            padding: 8px 12px;
            font-size: 14px;
        }
        
        /* --- START: UPDATED RESPONSIVE CODE FOR PHONES --- */
        .message-avatar {
            width: 24px; /* Smaller on mobile phones */
            height: 24px;
        }

        .user-icon {
            font-size: 0.9rem; /* Further adjust font size */
        }

        .message-avatar.bot {
            padding: 3px; /* Adjust padding for the smaller logo */
        }
        /* --- END: UPDATED RESPONSIVE CODE FOR PHONES --- */

        .input-wrapper {
            gap: 6px;
        }
        
        #sendButton {
            min-width: 36px;
            padding: 6px;
            height: 36px;
            font-size: 14px;
            width: 36px;
        }
        
        #messageInput {
            padding: 8px 12px;
            min-height: 36px;
        }
        
        .thinking-message {
            padding: 8px 12px;
        }
    }

    /* Landscape mobile */
    @media (max-height: 500px) and (orientation: landscape) {
        .chat-container {
            max-height: none;
            height: calc(100vh - 10px);
        }
        
        .welcome-message {
            padding: 20px 16px;
            margin-bottom: 16px;
        }
        
        .chat-messages {
            padding-bottom: 30px;
        }
    }

    /* High DPI displays */
    @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
        .message {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
    }
</style>
@endsection


@section('content')

<div class="chat-container">
    <div class="chat-header">
        <h1>
            IMARA LIMS AI Assistant
            <span class="status-indicator"></span>
        </h1>
    </div>
    
    <div class="chat-messages" id="chatMessages">
        <div class="welcome-message">
            <i class="mdi mdi-chip" style="font-size:35px;color:#a72b2a"></i>
            <div class="welcome-title">
                Welcome to IMARA LIMS AI Assistant
            </div>
            <div class="welcome-subtitle">Send a message to get started with your laboratory management queries</div>
        </div>
    </div>
    
    <div class="chat-input-container">
        <div class="input-wrapper">
            <textarea id="messageInput" placeholder="Ask about laboratory management, sample tracking, equipment management..." rows="1"></textarea>
            <button id="sendButton" type="button">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"></line>
                    <polygon points="22,2 15,22 11,13 2,9 22,2"></polygon>
                </svg>
            </button>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
    const WEBHOOK_URL = 'https://dretmox641.app.n8n.cloud/webhook/20f998ca-c469-412e-9210-f7d5d9602bbf';
    
    const chatMessages = document.getElementById('chatMessages');
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    let thinkingMessage = null;

    // Auto-resize textarea
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });

    async function sendMessageToWebhook(userInput) {
        try {
            const response = await fetch(WEBHOOK_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ message: userInput }),
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Server error (${response.status}): ${errorText || response.statusText}`);
            }

            const responseText = await response.text();
            let botReply = responseText;

            // Try to parse JSON response
            try {
                const jsonData = JSON.parse(responseText);
                if (jsonData && typeof jsonData === 'object') {
                    // Check for common response fields
                    botReply = jsonData.output || jsonData.reply || jsonData.message || jsonData.text || responseText;
                }
            } catch (e) {
                // Not JSON, use as plain text
            }

            return { reply: botReply || "I received your message but couldn't generate a response." };

        } catch (error) {
            console.error('Webhook error:', error);
            
            let errorMessage = 'Unable to connect to the AI service.';
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                errorMessage = 'Connection failed. Please check your internet connection and try again.';
            } else if (error.message.includes('CORS')) {
                errorMessage = 'Connection blocked by security policy. Please contact support.';
            } else if (error.message) {
                errorMessage = error.message;
            }
            
            return { error: errorMessage };
        }
    }

    function createLimsLogo() {
        const img = document.createElement('img');
        img.src = 'logo.png';
        img.alt = 'IMARA LIMS Logo';
        img.className = 'lims-logo';
        return img;
    }

    function createUserIcon() {
        const div = document.createElement('div');
        div.innerHTML = '👨‍💼';
        div.className = 'user-icon';
        return div;
    }

 function formatBotMessage(content) {
    // Remove asterisks used for markdown formatting
    content = content.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>'); // Convert bold markdown to HTML
    content = content.replace(/\*(.*?)\*/g, '<em>$1</em>'); // Convert italic markdown to HTML
    
    // Check if content contains list patterns
    const listPatterns = [
        /^\d+\.\s/m,  // Numbered lists (1. )
        /^[-•]\s/m,   // Bullet lists (- or •)
        /^\*\s/m      // Asterisk lists (* )
    ];
    
    const hasLists = listPatterns.some(pattern => pattern.test(content));
    
    if (hasLists) {
        // Split content into lines
        const lines = content.split('\n');
        const formattedContent = document.createElement('div');
        
        let globalNumberCounter = 1; // Global counter for all numbered items
        let currentList = null;
        let listType = null;
        
        lines.forEach(line => {
            line = line.trim();
            if (!line) {
                // Add line break for empty lines
                const br = document.createElement('br');
                formattedContent.appendChild(br);
                return;
            }
            
            // Check if line is a list item
            const numberedMatch = line.match(/^(\d+)\.\s(.+)/);
            const bulletMatch = line.match(/^[-•*]\s(.+)/);
            
            if (numberedMatch) {
                // Reset list context if it's a different type
                if (listType !== 'numbered') {
                    currentList = null;
                    listType = 'numbered';
                }
                
                // Create numbered item as a div (not using ol/li to avoid browser renumbering)
                const itemDiv = document.createElement('div');
                itemDiv.style.marginBottom = '8px';
                itemDiv.style.marginLeft = '0px';
                
                const numberSpan = document.createElement('span');
                numberSpan.textContent = `${globalNumberCounter}. `;
                numberSpan.style.fontWeight = 'normal';
                
                const contentSpan = document.createElement('span');
                contentSpan.innerHTML = numberedMatch[2]; // Allow HTML formatting
                
                itemDiv.appendChild(numberSpan);
                itemDiv.appendChild(contentSpan);
                formattedContent.appendChild(itemDiv);
                
                globalNumberCounter++;
            } else if (bulletMatch) {
                // Reset list context if it's a different type
                if (listType !== 'bullet') {
                    currentList = null;
                    listType = 'bullet';
                }
                
                // Create bullet item as a div
                const itemDiv = document.createElement('div');
                itemDiv.style.marginBottom = '4px';
                itemDiv.style.marginLeft = '20px';
                itemDiv.style.position = 'relative';
                
                const bulletSpan = document.createElement('span');
                bulletSpan.textContent = '• ';
                bulletSpan.style.position = 'absolute';
                bulletSpan.style.left = '-15px';
                
                const contentSpan = document.createElement('span');
                contentSpan.innerHTML = bulletMatch[1]; // Allow HTML formatting
                
                itemDiv.appendChild(bulletSpan);
                itemDiv.appendChild(contentSpan);
                formattedContent.appendChild(itemDiv);
            } else {
                // Regular paragraph - reset list context
                currentList = null;
                listType = null;
                
                const p = document.createElement('div');
                p.innerHTML = line; // Allow HTML formatting
                p.style.marginBottom = '8px';
                p.style.lineHeight = '1.5';
                formattedContent.appendChild(p);
            }
        });
        
        return formattedContent;
    } else {
        // Regular text content
        const div = document.createElement('div');
        div.innerHTML = content; // Allow HTML formatting for bold/italic
        div.style.lineHeight = '1.5';
        return div;
    }
}
    function addMessage(content, isUser = false, isError = false) {
        const messageWrapper = document.createElement('div');
        messageWrapper.className = `message-wrapper ${isUser ? 'user' : 'bot'}`;
        
        // Create avatar
        const avatar = document.createElement('div');
        avatar.className = `message-avatar ${isUser ? 'user' : 'bot'}`;
        
        if (isUser) {
            avatar.appendChild(createUserIcon());
        } else {
            avatar.appendChild(createLimsLogo());
        }
        
        // Create message
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${isUser ? 'user-message' : (isError ? 'error-message' : 'bot-message')}`;
        
        if (isUser || isError) {
            messageDiv.textContent = content;
        } else {
            // Format bot messages with proper structure
            const formattedContent = formatBotMessage(content);
            messageDiv.appendChild(formattedContent);
        }
        
        // Append elements
        messageWrapper.appendChild(avatar);
        messageWrapper.appendChild(messageDiv);
        
        // Remove welcome message if it exists
        const welcomeMessage = chatMessages.querySelector('.welcome-message');
        if (welcomeMessage) {
            welcomeMessage.remove();
        }
        
        chatMessages.appendChild(messageWrapper);
        scrollToBottom();
    }

    function showThinking() {
        thinkingMessage = document.createElement('div');
        thinkingMessage.className = 'thinking-wrapper';
        
        // Create avatar
        const avatar = document.createElement('div');
        avatar.className = 'message-avatar bot';
        avatar.appendChild(createLimsLogo());
        
        // Create thinking message
        const thinkingDiv = document.createElement('div');
        thinkingDiv.className = 'thinking-message';
        thinkingDiv.innerHTML = `
            <div class="thinking-dots">
                <div class="thinking-dot"></div>
                <div class="thinking-dot"></div>
                <div class="thinking-dot"></div>
            </div>
            <span>Thinking...</span>
        `;
        
        thinkingMessage.appendChild(avatar);
        thinkingMessage.appendChild(thinkingDiv);
        
        chatMessages.appendChild(thinkingMessage);
        scrollToBottom();
    }

    function hideThinking() {
        if (thinkingMessage) {
            thinkingMessage.remove();
            thinkingMessage = null;
        }
    }

    function scrollToBottom() {
        // Enhanced scroll to bottom with smooth animation
        setTimeout(() => {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 100);
    }

    async function sendMessage() {
        const message = messageInput.value.trim();
        if (!message) return;

        // Add user message
        addMessage(message, true);
        messageInput.value = '';
        messageInput.style.height = 'auto';
        
        // Disable input and show thinking
        sendButton.disabled = true;
        messageInput.disabled = true;
        showThinking();

        try {
            const result = await sendMessageToWebhook(message);
            
            hideThinking();
            
            if (result.error) {
                addMessage(result.error, false, true);
            } else if (result.reply) {
                addMessage(result.reply, false);
            }
        } catch (error) {
            hideThinking();
            addMessage(`Unexpected error: ${error.message}`, false, true);
        } finally {
            // Re-enable input
            sendButton.disabled = false;
            messageInput.disabled = false;
            messageInput.focus();
        }
    }

    // Event listeners
    sendButton.addEventListener('click', sendMessage);

    messageInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    // Focus input on load
    window.addEventListener('load', () => {
        messageInput.focus();
    });

    // Handle mobile viewport changes
    window.addEventListener('resize', () => {
        scrollToBottom();
    });
</script>

@endsection