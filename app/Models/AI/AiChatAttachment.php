<?php

namespace App\Models\AI;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiChatAttachment extends Model
{
    protected $table = 'ai_chat_attachments';

    public const STATUS_UPLOADED = 'uploaded';
    public const STATUS_EXTRACTED = 'extracted';
    public const STATUS_CHUNKED = 'chunked';
    public const STATUS_EMBEDDED = 'embedded';
    public const STATUS_INDEXED = 'indexed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_QUARANTINED = 'quarantined';

    protected $fillable = [
        'ai_conversation_id',
        'ai_message_id',
        'original_name',
        'stored_path',
        'mime_type',
        'file_size',
        'processing_status',
        'extracted_text',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(AiMessage::class, 'ai_message_id');
    }
}
