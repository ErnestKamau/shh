<?php

namespace App\Models\AuditModule;

use OwenIt\Auditing\Contracts\Auditable;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class VerificationRecord extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    use SoftDeletes;

    protected $table = 'verification_records';

    protected $fillable = [
        'verification_number',
        'corrective_action_id',
        'verification_date',
        'verified_by',
        'verified_by_user_id',
        'effectiveness_result_id',
        'effectiveness_result_name',
        'verification_method',
        'evidence_reviewed',
        'comments',
        'requires_reopen',
        'reopen_reason',
        'follow_up_date',
        'follow_up_notes',
        'closure_status_id',
        'closure_status_name',
        'closure_date',
        'closed_by',
        'created_by',
    ];

    protected $casts = [
        'verification_date' => 'date',
        'follow_up_date' => 'date',
        'closure_date' => 'date',
        'requires_reopen' => 'boolean',
    ];

    // Relationships
    public function correctiveAction(): BelongsTo
    {
        return $this->belongsTo(CorrectiveAction::class, 'corrective_action_id');
    }

    public function effectivenessResult(): BelongsTo
    {
        return $this->belongsTo(VerificationResult::class, 'effectiveness_result_id');
    }

    public function closureStatus(): BelongsTo
    {
        return $this->belongsTo(VerificationClosureStatus::class, 'closure_status_id');
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by_user_id');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(AuditAttachment::class, 'attachable');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereHas('closureStatus', fn($q) => $q->where('code', 'PEND'));
    }

    public function scopeClosed($query)
    {
        return $query->whereHas('closureStatus', fn($q) => $q->where('code', 'CLOSED'));
    }

    public function scopeEffective($query)
    {
        return $query->whereHas('effectivenessResult', fn($q) => $q->where('code', 'EFF'));
    }

    public function scopeNotEffective($query)
    {
        return $query->whereHas('effectivenessResult', fn($q) => $q->where('code', 'NOTEFF'));
    }

    // Boot method to auto-generate verification_number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($verification) {
            if (empty($verification->verification_number)) {
                $verification->verification_number = static::generateVerificationNumber();
            }
            
            if (empty($verification->created_by)) {
                $verification->created_by = auth()->id();
            }
        });
    }

    // Helper Methods
    public static function generateVerificationNumber(): string
    {
        $year = date('Y');
        
        // Get the highest number for this year to ensure uniqueness
        $lastRecord = static::where('verification_number', 'like', "VER/{$year}/%")
            ->orderBy('verification_number', 'desc')
            ->first();
        
        if ($lastRecord && preg_match('/VER\/\d+\/(\d+)/', $lastRecord->verification_number, $matches)) {
            $lastNumber = (int) $matches[1];
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        $verificationNumber = "VER/{$year}/" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        
        // Double-check uniqueness (handle race conditions)
        $attempts = 0;
        while (static::where('verification_number', $verificationNumber)->exists() && $attempts < 10) {
            $newNumber++;
            $verificationNumber = "VER/{$year}/" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
            $attempts++;
        }
        
        return $verificationNumber;
    }

    public function isEffective(): bool
    {
        return $this->effectivenessResult && $this->effectivenessResult->code === 'EFF';
    }
}
