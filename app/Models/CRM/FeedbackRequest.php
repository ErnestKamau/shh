<?php

namespace App\Models\CRM;

use Illuminate\Database\Eloquent\Model;

class FeedbackRequest extends Model
{
    protected $table = 'feedback_requests';
    
    const STATUS_PENDING = 'PENDING';
    const STATUS_SUBMITTED = 'SUBMITTED';
    const STATUS_EXPIRED = 'EXPIRED';
    
    protected $fillable = [
        'company_id',
        'customer_id',
        'contact_id',
        'token',
        'email',
        'feedback_id',
        'status',
        'sent_at',
        'submitted_at',
        'expires_at',
    ];
    
    protected $casts = [
        'sent_at' => 'datetime',
        'submitted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];
    
    /**
     * Get the customer this request was sent to.
     */
    public function customer()
    {
        return $this->belongsTo(\App\Models\CRM\CRMCustomer::class, 'customer_id');
    }
    
    /**
     * Get the contact this request was sent to.
     */
    public function contact()
    {
        return $this->belongsTo(\App\Models\CRM\CustomerContact::class, 'contact_id');
    }
    
    /**
     * Get the feedback submitted in response to this request.
     */
    public function feedback()
    {
        return $this->belongsTo(\App\Models\CRM\CustomerFeedback::class, 'feedback_id');
    }
    
    /**
     * Generate a unique token.
     */
    public static function generateUniqueToken()
    {
        do {
            $token = \Illuminate\Support\Str::uuid()->toString();
        } while (self::where('token', $token)->exists());
        
        return $token;
    }
    
    /**
     * Check if this request has expired.
     */
    public function isExpired()
    {
        return $this->status === self::STATUS_PENDING && now()->isAfter($this->expires_at);
    }
    
    /**
     * Mark this request as submitted and link the feedback.
     */
    public function markAsSubmitted($feedbackId)
    {
        $this->update([
            'status' => self::STATUS_SUBMITTED,
            'feedback_id' => $feedbackId,
            'submitted_at' => now(),
        ]);
    }
}
