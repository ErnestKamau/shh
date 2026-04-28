<?php

namespace App\Jobs\Messaging;

use App\Services\Messaging\AfricasTalkingWhatsappService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAfricasTalkingWhatsappMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $tenantId,
        public int $outboundMessageId,
    ) {
    }

    public function handle(AfricasTalkingWhatsappService $service): void
    {
        $service->deliverQueuedMessage($this->tenantId, $this->outboundMessageId);
    }
}