<?php

namespace App\Jobs\Equipment;

use App\Models\Equipments\EquipmentDisposal;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendDisposalApprovalNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $disposal;
    public $approver;

    /**
     * Create a new job instance.
     *
     * @param EquipmentDisposal $disposal
     * @param User $approver
     * @return void
     */
    public function __construct(EquipmentDisposal $disposal, User $approver)
    {
        $this->disposal = $disposal;
        $this->approver = $approver;
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
            $approver = $this->approver;

            // Build email content
            $subject = 'Disposal Approval Required - ' . $disposal->equipment->name;
            $message = "Dear {$approver->name},\n\n";
            $message .= "A disposal request requires your approval:\n\n";
            $message .= "Equipment: {$disposal->equipment->name}\n";
            $message .= "Equipment Number: {$disposal->equipment->equipment_number}\n";
            $message .= "Requested By: {$disposal->requester->name}\n";
            $message .= "Risk Level: {$disposal->risk_level}\n";
            $message .= "Disposal Method: " . ucfirst($disposal->proposed_method) . "\n\n";
            $message .= "Justification:\n{$disposal->justification}\n\n";
            $message .= "Please review and approve/reject this disposal request.\n\n";
            $message .= "Disposal ID: {$disposal->id}\n";

            // Send email
            Mail::raw($message, function ($mail) use ($approver, $subject) {
                $mail->to($approver->email)
                     ->subject($subject);
            });

            \Log::info('Disposal approval notification sent', [
                'disposal_id' => $disposal->id,
                'approver_id' => $approver->id,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send disposal approval notification', [
                'disposal_id' => $this->disposal->id,
                'approver_id' => $this->approver->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}


