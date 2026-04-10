<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use App\User;

class TicketComment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'comment',
        'is_internal',
        'mentions',
        'parent_comment_id',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'mentions' => 'array',
    ];

    // Relationships
    public function ticket()
    {
        return $this->belongsTo(Complaint::class, 'ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parentComment()
    {
        return $this->belongsTo(TicketComment::class, 'parent_comment_id');
    }

    public function replies()
    {
        return $this->hasMany(TicketComment::class, 'parent_comment_id');
    }

    public function mentionedUsers()
    {
        if (!$this->mentions || !is_array($this->mentions)) {
            return collect();
        }
        
        return User::whereIn('id', $this->mentions)->get();
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }
}

