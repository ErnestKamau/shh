<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use App\User;

class TicketTeamChat extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    protected $table = 'ticket_team_chat';

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'mentions',
    ];

    protected $casts = [
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

    public function mentionedUsers()
    {
        if (!$this->mentions || !is_array($this->mentions)) {
            return collect();
        }
        
        return User::whereIn('id', $this->mentions)->get();
    }

    // Scopes
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('created_at', '>=', now()->subHours($hours));
    }
}

