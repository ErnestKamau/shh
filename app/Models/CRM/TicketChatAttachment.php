<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketChatAttachment extends Model
{
    use SoftDeletes;

    protected $table = 'ticket_chat_attachments';

    protected $fillable = [
        'chat_message_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'mime_type',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    // Relationships
    public function chatMessage()
    {
        return $this->belongsTo(TicketChat::class, 'chat_message_id');
    }

    // Helper methods
    public function isImage(): bool
    {
        return in_array($this->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg']);
    }

    public function getFileUrlAttribute(): string
    {
        return asset($this->file_path);
    }
}
