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
        
        $activeCompany = getActiveCompany();
        $this->logoPath = ($activeCompany && !empty($activeCompany->logo))
            ? (str_starts_with($activeCompany->logo, 'http') ? $activeCompany->logo : rtrim(config('app.url'), '/') . '/' . ltrim($activeCompany->logo, '/'))
            : null;

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
            // Regenerate the Investigation Report if content wasn't provided
            $resolution = $this->complaint->resolutions->last();
            $chainOfCustody = \App\Models\CRM\Chain_of_Custody_Complaint::where('complaint_id', $this->complaint->id)
                                    ->whereNotIn('action', ['Closure Report Regenerated', 'Investigation Report Regenerated'])
                                    ->with('actionTaker')
                                    ->orderBy('created_at', 'asc')
                                    ->get();

            $pdf = \PDF::loadView('pdfs.investigation_report', [
                'complaint' => $this->complaint,
                'resolution' => $resolution,
                'chainOfCustody' => $chainOfCustody,
            ]);
            
            // Standard page numbering for complaint reports
            if (function_exists('addClosureReportPageNumbers')) {
                addClosureReportPageNumbers($pdf);
            }
            $pdfOutput = $pdf->output();
        }

        $safeId = str_replace(['/', '\\'], '-', $this->complaint->complaint_id);
        
        return $this->from(config('mail.from.address'), 'IMARA SYSTEM')
                    ->subject('Investigation Report Update: ' . $this->complaint->complaint_id)
                    ->view('emails.complaint_investigation')
                    ->attachData($pdfOutput, 'Investigation_Report_' . $safeId . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}
