<?php

namespace App\Models\Messaging;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CRM\CustomerContact;

class WhatsAppConversation extends Model
{
    protected $table = 'whatsapp_conversations';

    protected $fillable = [
        'tenant_id',
        'phone_number',
        'customer_name',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    /**
     * Get the messages for this conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(WhatsAppMessage::class, 'conversation_id')->orderBy('timestamp', 'asc');
    }

    /**
     * Get or create a conversation for a tenant and phone number.
     */
    public static function getOrCreate(string $tenantId, string $phoneNumber): self
    {
        $conversation = self::where('tenant_id', $tenantId)
            ->where('phone_number', $phoneNumber)
            ->first();

        if (!$conversation) {
            // Clean phone number for contact lookup
            $cleanPhone = preg_replace('/\D/', '', $phoneNumber);

            // Attempt to resolve customer name from CRM contacts
            $contact = CustomerContact::where('mobile', 'like', "%{$cleanPhone}%")
                ->orWhere('telephone', 'like', "%{$cleanPhone}%")
                ->first();

            $customerName = null;
            if ($contact) {
                $firstName = $contact->first_name ?? '';
                $lastName = $contact->last_name ?? '';
                $customerName = trim($firstName . ' ' . $lastName);
            }

            $conversation = self::create([
                'tenant_id' => $tenantId,
                'phone_number' => $phoneNumber,
                'customer_name' => $customerName ?: 'Customer (' . $phoneNumber . ')',
                'last_message_at' => now(),
            ]);
        }

        return $conversation;
    }
}
