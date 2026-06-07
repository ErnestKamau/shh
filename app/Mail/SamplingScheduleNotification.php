<?php

namespace App\Mail;

use App\Models\SamplingSchedule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SamplingScheduleNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $schedule;
    public $companyName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SamplingSchedule $schedule)
    {
        $this->schedule = $schedule->load(['client', 'contact', 'personnel']);
        
        $activeCompany = getActiveCompany();
        $this->companyName = $activeCompany->name ?? 'IMARA LIMS';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $contactName = $this->schedule->contact 
            ? trim(($this->schedule->contact->first_name ?? '') . ' ' . ($this->schedule->contact->last_name ?? ''))
            : 'Valued Client';

        $subject = 'Sampling Scheduled: ' . $this->schedule->title;

        return $this->from(config('mail.from.address'), $this->companyName)
                    ->subject($subject)
                    ->view('emails.sampling_schedule_notification')
                    ->with([
                        'contactName' => $contactName,
                        'schedule' => $this->schedule,
                        'companyName' => $this->companyName,
                    ]);
    }
}
