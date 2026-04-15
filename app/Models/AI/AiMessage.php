<?php

namespace App\Models\AI;

use Illuminate\Database\Eloquent\Model;

class AiMessage extends Model
{
    protected $table = 'ai_messages';

    protected $fillable = [
        'ai_conversation_id',
        'parent_message_id',
        'role',
        'content',
        'sources',
        'is_edited',
        'feedback',
        'metadata',
    ];

    protected $casts = [
        'sources'      => 'array',
        'metadata'     => 'json',
        'is_edited'    => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }

    public function parentMessage()
    {
        return $this->belongsTo(AiMessage::class, 'parent_message_id');
    }
}
