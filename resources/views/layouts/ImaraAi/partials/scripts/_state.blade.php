const KNOWLEDGE_SEARCH_URL  = '{{ route('ai.knowledge.search') }}';
const ASK_STREAM_URL        = '{{ route('ai.knowledge.ask-stream') }}';
const CSRF_TOKEN            = '{{ csrf_token() }}';
const CONVOS_URL            = '{{ route('ai.conversations.list') }}';
const CREATE_CONVO_URL      = '{{ route('ai.conversations.create') }}';
const CONVO_MESSAGES_BASE   = '/imara-ai/conversations';
@auth
const USER_INITIALS = '{{ strtoupper(substr(auth()->user()->name ?? "U", 0, 1)) }}';
@else
const USER_INITIALS = 'U';
@endauth

const chatMessages   = document.getElementById('chatMessages');
const welcomeScreen  = document.getElementById('welcomeScreen');
const messageInput   = document.getElementById('messageInput');
const sendButton     = document.getElementById('sendButton');
const stopButton     = document.getElementById('stopButton');
const sidebar        = document.getElementById('aiSidebar');
const sidebarHistory = document.getElementById('sidebarHistory');
const sidebarSearch  = document.getElementById('sidebarSearch');
const aiMain         = document.querySelector('.ai-main');
const aiInputArea    = document.querySelector('.ai-input-area');
const hamburgerBtn   = document.getElementById('hamburgerBtn');
const menuOverlay    = document.getElementById('menuOverlay');
const btnHeaderNewChat = document.getElementById('btnHeaderNewChat');
const toolsBtn       = document.getElementById('toolsBtn');
const toolsMenu      = document.getElementById('toolsMenu');
const toggleMasterTools = document.getElementById('toggleMasterTools');
const visualsToggle  = document.getElementById('visualsToggle');

let thinkingRow              = null;
let currentAbortController   = null;
let currentStreamSessionId   = null;  // Tracks the sessionId of active stream for proper cleanup
let isCleaningUp             = false;  // Prevents new submissions during cleanup
let isSending                = false;  // Prevents concurrent submissions
let cleanupTimeoutId         = null;   // Watchdog timeout for stuck cleanup states
let conversations            = [];
let currentConvoId           = null; // server-generated integer id once conversation is created
let currentConvoCreated      = false; // true once a server record exists for this session
let selectedConvoIds         = new Set(); // ids checked in bulk-select mode
let bulkSelectMode           = false;
let toolsMasterEnabled       = true; // Master switch for all tools
let visualsEnabled           = true; // Whether AI charts are allowed
