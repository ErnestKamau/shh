<?php

namespace App\Console\Commands;

use App\Services\Messaging\AfricasTalkingWhatsappService;
use Illuminate\Console\Command;

class SendResultReadyTestMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messaging:test-result-ready
                            {tenant_id : Tenant identifier}
                            {phone : Recipient phone number in international format}
                            {customer_name : Customer name for template variable}
                            {result_reference : Result reference variable}
                            {--header= : Optional template header value}
                            {--idempotency= : Optional idempotency key}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue a standalone RESULT_READY WhatsApp template message through Africa\'s Talking';

    /**
     * Execute the console command.
     */
    public function handle(AfricasTalkingWhatsappService $service): int
    {
        $tenantId = (string) $this->argument('tenant_id');
        $phone = (string) $this->argument('phone');

        $variables = [
            'customer_name' => (string) $this->argument('customer_name'),
            'result_reference' => (string) $this->argument('result_reference'),
        ];

        $headerValue = $this->option('header');
        if (is_string($headerValue) && $headerValue !== '') {
            $variables['header_value'] = $headerValue;
        }

        $idempotencyKey = $this->option('idempotency');
        if (is_string($idempotencyKey) && $idempotencyKey !== '') {
            $variables['idempotency_key'] = $idempotencyKey;
        }

        try {
            $message = $service->sendTemplateMessage(
                $tenantId,
                'RESULT_READY',
                $phone,
                $variables,
            );
        } catch (\Throwable $exception) {
            $this->error('Failed to queue RESULT_READY message: ' . $exception->getMessage());
            return Command::FAILURE;
        }

        $this->info('RESULT_READY message queued successfully.');
        $this->line('Outbound message id: ' . $message->id);
        $this->line('Status: ' . $message->status);
        $this->line('Tenant: ' . $message->tenant_id);

        return Command::SUCCESS;
    }
}
