<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\User;

class TicketChangeHistory extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    protected $table = 'ticket_change_history';

    public $timestamps = false; // Only created_at is used, no updated_at needed

    protected $fillable = [
        'ticket_id',
        'user_id',
        'field_name',
        'old_value',
        'new_value',
        'change_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
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

    // Scopes
    public function scopeByChangeType($query, $type)
    {
        return $query->where('change_type', $type);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}

