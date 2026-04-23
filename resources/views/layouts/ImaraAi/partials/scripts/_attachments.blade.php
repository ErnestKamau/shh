const attachBtn       = document.getElementById('attachBtn');
const fileInput       = document.getElementById('fileInput');
const attachPreview   = document.getElementById('attachPreviewBar');
const attachFileName  = document.getElementById('attachFileName');
const attachStatus    = document.getElementById('attachStatusBadge');
const attachRemoveBtn = document.getElementById('attachRemoveBtn');

let currentAttachmentId = null;

attachBtn?.addEventListener('click', () => fileInput?.click());

attachRemoveBtn?.addEventListener('click', () => {
    currentAttachmentId = null;
    fileInput.value = '';
    attachPreview.style.display = 'none';
});

fileInput?.addEventListener('change', async () => {
    const file = fileInput.files[0];
    if (!file) return;

    const maxMb = 10;
    if (file.size > maxMb * 1024 * 1024) {
        alert(`File too large. Maximum size is ${maxMb} MB.`);
        fileInput.value = '';
        return;
    }

    attachFileName.textContent = file.name;
    attachStatus.textContent   = 'Uploading…';
    attachStatus.style.background = '#dbeafe';
    attachStatus.style.color      = '#1d4ed8';
    attachPreview.style.display   = 'flex';

    if (!currentConvoCreated) {
        try {
            const convo = await createConversationOnServer('Attachment: ' + file.name.slice(0, 40));
            currentConvoId      = convo.id;
            currentConvoCreated = true;
            conversations.unshift(convo);
            renderSidebarHistory();
        } catch (e) {
            attachStatus.textContent = 'Error';
            console.error('Failed to create convo for attachment', e);
            return;
        }
    }

    const formData = new FormData();
    formData.append('file', file);
    formData.append('_token', CSRF_TOKEN);

    try {
        const res  = await fetch(`${CONVO_MESSAGES_BASE}/${currentConvoId}/attachments`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF_TOKEN },
            body: formData,
        });
        const json = await res.json();
        if (res.ok && json.status === 'ok') {
            currentAttachmentId = json.attachment_id;
            attachStatus.textContent = json.has_text ? 'Ready' : 'No text';
        } else {
            attachStatus.textContent = 'Upload failed';
        }
    } catch (e) {
        attachStatus.textContent = 'Network error';
        console.error('File upload failed', e);
    }
});
