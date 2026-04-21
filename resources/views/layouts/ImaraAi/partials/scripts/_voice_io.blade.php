// ── Voice Output (TTS) ───────────────────────────────────────────────────
function speakMessage(text, btn) {
    if (!window.speechSynthesis) return;

    const cleanText = text
        .replace(/```[\s\S]*?```/g, ' [code block skipped] ')
        .replace(/__CHART_PLACEHOLDER_\d+__/g, ' [chart data] ')
        .replace(/\*\*|__|`|#/g, '');

    const utterance = new SpeechSynthesisUtterance(cleanText);
    utterance.lang = 'en-US';
    utterance.rate = 1.0;
    utterance.pitch = 1.0;

    utterance.onstart = () => {
        btn.classList.add('speaking');
        btn.innerHTML = '<i class="mdi mdi-stop"></i>';
    };

    utterance.onend = () => {
        btn.classList.remove('speaking');
        btn.innerHTML = '<i class="mdi mdi-volume-high"></i>';
    };

    utterance.onerror = (e) => {
        console.error('TTS Error:', e);
        btn.classList.remove('speaking');
        btn.innerHTML = '<i class="mdi mdi-volume-high"></i>';
    };

    window.speechSynthesis.cancel();
    window.speechSynthesis.speak(utterance);
}

// ── Voice Input (STT) ────────────────────────────────────────────────────
const micBtn = document.getElementById('micBtn');
let recognition = null;

if (micBtn) {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
        micBtn.title = 'Voice input not supported';
        micBtn.style.opacity = '0.4';
        micBtn.disabled = true;
    } else {
        recognition = new SpeechRecognition();
        recognition.continuous      = false;
        recognition.interimResults  = false;
        recognition.lang            = 'en-US';

        recognition.onstart = () => {
            micBtn.classList.add('mic-active');
            micBtn.querySelector('i').className = 'mdi mdi-microphone';
        };

        recognition.onresult = (e) => {
            const transcript = e.results[0][0].transcript;
            messageInput.value = transcript;
            messageInput.dispatchEvent(new Event('input'));
        };

        recognition.onerror = (e) => {
            micBtn.classList.remove('mic-active');
            micBtn.querySelector('i').className = 'mdi mdi-microphone-outline';
            if (e.error === 'not-allowed') alert('Microphone access denied.');
        };

        recognition.onend = () => {
            micBtn.classList.remove('mic-active');
            micBtn.querySelector('i').className = 'mdi mdi-microphone-outline';
        };

        micBtn.addEventListener('click', () => {
            if (micBtn.classList.contains('mic-active')) {
                recognition.stop();
            } else {
                if (window.speechSynthesis) window.speechSynthesis.cancel();
                try {
                    recognition.start();
                } catch (err) { console.error('Mic start failed', err); }
            }
        });
    }
}
