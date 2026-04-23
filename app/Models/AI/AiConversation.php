<?php

namespace App\Models\AI;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AiConversation extends Model
{
    use SoftDeletes;

    protected $table = 'ai_conversations';

    protected $fillable = [
        'user_id',
        'title',
        'context',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned'    => 'boolean',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
        'deleted_at'   => 'datetime',
    ];

    public function messages()
    {
        return $this->hasMany(AiMessage::class, 'ai_conversation_id')->orderBy('created_at');
    }

    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }
}
