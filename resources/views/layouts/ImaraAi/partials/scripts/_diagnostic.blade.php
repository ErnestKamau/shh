/**
 * Imara AI Diagnostic & Raw Stream Logger
 * Helps identify corrupted chunks like the "args.map" error.
 */

const AI_DIAGNOSTICS = {
    enabled: true,
    lastChunks: [],
    
    logChunk: function(chunk, buffer) {
        if (!this.enabled) return;
        
        const timestamp = new Date().toLocaleTimeString();
        console.groupCollapsed(`[AI Diagnostic] Stream Chunk @ ${timestamp}`);
        console.log("Raw Chunk:", chunk);
        console.log("Current Buffer:", buffer);
        
        try {
            if (chunk.startsWith('data: ')) {
                const dataStr = chunk.substring(6).trim();
                if (dataStr !== '[DONE]') {
                    const parsed = JSON.parse(dataStr);
                    console.log("Parsed Data:", parsed);
                } else {
                    console.log("Stream signaled [DONE]");
                }
            }
        } catch (e) {
            console.error("PARSE ERROR in Diagnostic:", e.message);
            console.warn("Evidence of corruption found in chunk:", chunk);
            
            // Critical check for "args.map" or other leaked code
            if (chunk.includes('args.map')) {
                console.error("DETECTED: Code leak in stream (args.map). This suggests the backend is emitting source code instead of JSON.");
            }
        }
        console.groupEnd();
        
        this.lastChunks.push({ timestamp, chunk });
        if (this.lastChunks.length > 50) this.lastChunks.shift();
    },

    checkSystem: function() {
        const components = {
            'State': typeof currentConvoId !== 'undefined',
            'UI Handlers': typeof useChip === 'function',
            'Convo Manager': typeof startNewConversation === 'function',
            'API Client': typeof sendMessage === 'function',
            'Rendering': typeof renderMarkdown === 'function'
        };
        
        console.table(components);
        const missing = Object.entries(components).filter(([k, v]) => !v);
        if (missing.length) {
            console.error("System incomplete! Missing components:", missing.map(m => m[0]).join(', '));
        } else {
            console.log("System components verification passed.");
        }
    }
};

// Auto-run system check on load
window.addEventListener('load', () => {
    setTimeout(() => AI_DIAGNOSTICS.checkSystem(), 1000);
});
