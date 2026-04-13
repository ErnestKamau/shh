<?php

namespace App\Mail;

use App\Models\CRM\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use PDF;

class ComplaintClosureMail extends Mailable
{
    use Queueable, SerializesModels;

    public $complaint;
    public $pdfContent;
    public $logoPath;
    public $companyAddress;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Complaint $complaint, $pdfContent = null)
    {
        $this->complaint = $complaint->load([
            'client', 
            'resolutions.resolvedBy', 
            'resolutions.resolvedBy', 
            // 'chainOfCustody.movedInBy', // Removed: Relationship undefined on Complaint model
            'notes' => function($query) {
                $query->where('is_public', 1)
                      ->where('is_delete', '!=', 1)
                      ->orderBy('created_at', 'asc');
            },
            'attachments' => function($query) {
                $query->where('is_public', 1)
                      ->where('is_delete', '!=', 1)
                      ->where(function($q) {
                          // Exclude attachments where type or title is 'Closure Report'
                          // (type != X AND title != X) excludes rows where either field equals X
                          $q->where('type', '!=', 'Closure Report')
                            ->where('title', '!=', 'Closure Report');
                      })
                      ->orderBy('created_at', 'asc');
            }
        ]);
        $this->pdfContent = $pdfContent;
        
        // Fetch logo path and company address from active company (null-safe when no company configured)
        $activeCompany = getActiveCompany();
        $this->logoPath = ($activeCompany && !empty($activeCompany->logo)) ? public_path($activeCompany->logo) : null;
        $this->companyAddress = $activeCompany ? [
            'name' => $activeCompany->name ?? 'IMARA LIMS',
            'line1' => $activeCompany->address ?? '',
            'phone' => $activeCompany->cell_phone ?? '',
            'email' => $activeCompany->email ?? '',
            'website' => $activeCompany->website ?? '',
            'location' => $activeCompany->location ?? ''
        ] : [
            'name' => 'IMARA LIMS',
            'line1' => '',
            'phone' => '',
            'email' => '',
            'website' => '',
            'location' => ''
        ];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->pdfContent) {
            $pdfOutput = $this->pdfContent;
        } else {
             $pdf = \PDF::loadView('pdfs.closure_report', [
                'complaint' => $this->complaint,
                'publicNotes' => $this->complaint->notes,
                'publicAttachments' => $this->complaint->attachments,
                'chainOfCustody' => \App\Models\CRM\Chain_of_Custody_Complaint::where('complaint_id', $this->complaint->id)
                                    ->whereNotIn('action', ['Closure Report Regenerated'])
                                    ->with('movedInBy')
                                    ->orderBy('created_at', 'asc')
                                    ->get()
            ]);
            addClosureReportPageNumbers($pdf);
            $pdfOutput = $pdf->output();
        }

        $safeId = str_replace(['/', '\\'], '-', $this->complaint->complaint_id);
        $email = $this->from(config('mail.from.address'), 'IMARA SYSTEM')
                    ->subject('Official Closure Report: ' . $this->complaint->complaint_id)
                    ->view('emails.complaint_closure')
                    ->attachData($pdfOutput, 'Closure_Report_' . $safeId . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);

        // Also check for the auto-generated Investigation Report attachment
        $investigationAttachment = $this->complaint->attachments()
            ->where('title', 'Investigation Report')
            ->orderBy('id', 'desc')
            ->first();

        if ($investigationAttachment) {
            $path = storage_path('app/public/' . str_replace('/storage/', '', $investigationAttachment->file_path));
            if (file_exists($path)) {
                $email->attach($path, [
                    'as' => 'Investigation_Report_' . $safeId . '.pdf',
                    'mime' => 'application/pdf',
                ]);
            }
        }

        return $email;
    }
}
