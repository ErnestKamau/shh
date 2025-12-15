<?php

namespace App\Jobs\Equipment;

use App\Models\Equipments\EquipmentDisposal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDisposalDecisionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $disposal;
    public $decision;

    /**
     * Create a new job instance.
     *
     * @param EquipmentDisposal $disposal
     * @param string $decision
     * @return void
     */
    public function __construct(EquipmentDisposal $disposal, string $decision)
    {
        $this->disposal = $disposal;
        $this->decision = $decision;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            $disposal = $this->disposal;
            $requester = $disposal->requester;
            $decision = $this->decision;

            // Build email content
            $subject = 'Disposal Decision - ' . $disposal->equipment->name;
            $message = "Dear {$requester->name},\n\n";
            $message .= "Your disposal request has been " . strtoupper($decision) . ":\n\n";
            $message .= "Equipment: {$disposal->equipment->name}\n";
            $message .= "Equipment Number: {$disposal->equipment->equipment_number}\n";
            $message .= "Disposal ID: {$disposal->id}\n";
            $message .= "Status: " . ucfirst($disposal->status) . "\n\n";

            if ($decision === 'approved') {
                $message .= "The disposal request has been approved. You may now proceed with decommissioning and physical disposal.\n";
            } elseif ($decision === 'rejected') {
                $message .= "The disposal request has been rejected. Please review the remarks from the approver.\n";
            }

            // Send email
            Mail::raw($message, function ($mail) use ($requester, $subject) {
                $mail->to($requester->email)
                     ->subject($subject);
            });

            \Log::info('Disposal decision notification sent', [
                'disposal_id' => $disposal->id,
                'requester_id' => $requester->id,
                'decision' => $decision,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send disposal decision notification', [
                'disposal_id' => $this->disposal->id,
                'decision' => $this->decision,
                'error' => $e->getMessage(),
            ]);
        }
    }
}


