<?php

namespace App\Livewire\Crm\Feedback;

use App\Models\CRM\CustomerContact;
use App\Models\CRM\CustomerFeedback;
use App\Mail\LowScoreAlert;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\DB;

class FeedbackSubmitForm extends Component
{
    public $contact;
    public $contact_id; // Primitive for robust state
    public $feedback;
    public $isSubmitted = false;
    public $logoUrl;
    /** @var \App\Company|null Company for logo and name (from helper). */
    public $company = null;

    // Tracking
    #[Url] // NEW: Ensure query string binding
    public $token;
    
    public $feedbackRequest;
    public $linkExpired = false;
    public $linkUsed = false;

    // Form Fields
    public $service_type;
    public $service_type_other;
    public $service_reference_no;
    public $equipment_sample_id;
    public $results_issued_date;
    
    // Dynamic Ratings
    public array $dynamic_ratings = [];

    // Custom AmSpec Fields
    public $business_frequency;
    public array $hear_about_us = [];
    public $hear_about_us_other;
    public array $critical_services = ['', '', ''];

    // Phase 2 Demographic Fields
    public $contact_person;
    public $contact_position;
    public $contact_phone;
    public $usage_duration;
    
    // Phase 2 ISO Header Fields
    public $doc_ref = 'LR-05';
    public $doc_version = '07';

    // ISO (Obsolete)
    // Complaints (Obsolete)
    // Improvement & Future (Obsolete)
    public $specific_feedback;
    public $suggestions;
    
    // Referral
    public $will_recommend = null;
    
    // Consent
    public $consent_contact = null;
    public $preferred_contact_method;

    protected $casts = [
        'has_issues' => 'boolean',
        'consent_contact' => 'boolean',
    ];

    public function mount($contact_id, $token = null)
    {
        Log::info('FeedbackSubmitForm: Mount', [
            'contact_id_param' => $contact_id,
            'token_param' => $token,
            'query_token' => request()->query('token'),
            'this_token' => $this->token
        ]);

        $this->contact_id = $contact_id;
        $this->contact = CustomerContact::with('customer')->findOrFail($contact_id);
        
        // Priority: Argument -> Query String -> Property (via #[Url])
        if ($token) {
            $this->token = $token;
        } elseif (!$this->token) {
            $this->token = request()->query('token');
        }

        // Company and logo from helpers (request context or active company)
        $this->company = getActiveCompany();
        $this->logoUrl = ($this->company && !empty($this->company->logo)) ? asset($this->company->logo) : null;

        // Populate Contact Person name by default
        if ($this->contact) {
            $this->contact_person = trim(($this->contact->first_name ?? '') . ' ' . ($this->contact->middle_name ?? '') . ' ' . ($this->contact->last_name ?? ''));
            $this->contact_phone = $this->contact->telephone ?? $this->contact->mobile ?? null;
            $this->contact_position = $this->contact->job_occupation ?? null;
        }

        // Default Type of Service so the required field is not left out when filling the form
        if (empty($this->service_type)) {
            $this->service_type = ['Laboratory Testing'];
        }

        // Validate token if provided
        if ($this->token) {
            $this->feedbackRequest = \App\Models\CRM\FeedbackRequest::where('token', $this->token)
                ->where('contact_id', $this->contact_id)
                ->first();
                
            // Check if token exists
            if (!$this->feedbackRequest) {
                Log::warning("Invalid feedback token: {$this->token} for contact: {$this->contact_id}");
                $this->linkExpired = true;
                return;
            }
            
            // Company for this request (so form and branding match the campaign sender)
            if ($this->feedbackRequest->company_id) {
                $this->company = getCompanyById($this->feedbackRequest->company_id);
                $this->logoUrl = ($this->company && !empty($this->company->logo)) ? asset($this->company->logo) : null;
            }

            // Check if already submitted (SINGLE-USE enforcement)
            if ($this->feedbackRequest->status === \App\Models\CRM\FeedbackRequest::STATUS_SUBMITTED) {
                $this->linkUsed = true;
                return;
            }
            
            // Check if expired by date
            if ($this->feedbackRequest->isExpired()) {
                $this->linkExpired = true;
                $this->feedbackRequest->update(['status' => \App\Models\CRM\FeedbackRequest::STATUS_EXPIRED]);
                return;
            }
        } else {
             Log::warning("FeedbackForm mounted without token.");
             // Optional: Set linkExpired if strict mode requires token always
        }
    }

    protected function rules()
    {
        $rules = [
            'results_issued_date' => 'nullable|date',
            'service_reference_no' => 'nullable',
            'service_type' => 'required',
            'service_type_other' => 'required_if:service_type,Other',
            'specific_feedback' => 'nullable|string',
            'will_recommend' => 'nullable|boolean',
            'contact_position' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'usage_duration' => 'nullable|string',
            'business_frequency' => 'required|string',
            'hear_about_us' => 'nullable|array',
            'hear_about_us_other' => 'nullable|string',
            'critical_services' => 'nullable|array',
        ];

        // Dynamic Ratings - All Required with dynamic scales
        $metrics = \App\Models\CRM\EvaluationMetric::where('is_active', true)->get();
        foreach ($metrics as $metric) {
            $rules['dynamic_ratings.' . $metric->id] = 'required|integer|between:0,' . $metric->max_rating;
        }

        return $rules;
    }

    public function messages()
    {
        $messages = [
            'business_frequency.required' => 'Please select how often you do business with us.',
        ];
        $metrics = \App\Models\CRM\EvaluationMetric::where('is_active', true)->get();
        foreach ($metrics as $metric) {
            $messages['dynamic_ratings.' . $metric->id . '.required'] = 'Please provide a rating for "' . $metric->name . '".';
            $messages['dynamic_ratings.' . $metric->id . '.between'] = 'Rating for "' . $metric->name . '" must be between 0 and ' . $metric->max_rating . '.';
        }
        return $messages;
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    // ISO Concern functionality removed as per simplification request.

    public function save()
    {
        Log::info('FeedbackSubmitForm: Save initiated', [
            'contact_id_prop' => $this->contact_id,
            'token_prop' => $this->token
        ]);

        // RECOVERY MECHANISM: If state is lost, try to recover using token/contact_id
        if (!$this->feedbackRequest) {
            // ... (Recovery logic remains same as original for brevity, usually unchanged) ...
             // 1. Ensure we have a token
            if (!$this->token) {
                $this->token = request()->query('token');
            }

            // 2. Ensure we have a contact_id
            if (!$this->contact_id && $this->contact) {
                $this->contact_id = $this->contact->id;
            } elseif (!$this->contact_id && request()->route('contact_id')) {
                 $this->contact_id = request()->route('contact_id');
            }
            
            // 3. Re-fetch FeedbackRequest
            if ($this->token && $this->contact_id) {
                $this->feedbackRequest = \App\Models\CRM\FeedbackRequest::where('token', $this->token)
                    ->where('contact_id', $this->contact_id)
                    ->first();
                
                if ($this->feedbackRequest) {
                     // 4. Re-fetch Contact if missing
                     if(!$this->contact) {
                         $this->contact = CustomerContact::with('customer')->find($this->contact_id);
                     }
                }
            }
        }

        if (!$this->feedbackRequest) {
            Log::error("Attempted feedback submission without valid FeedbackRequest context.");
            session()->flash('error', 'Invalid submission session. Please use the link provided in your email.');
            return;
        }

        // Phase 4: Pre-save cleanup and merge - Final gatekeeper check

        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('FeedbackSubmitForm: Validation failed', ['errors' => $e->errors()]);
            throw $e;
        }

        // Step 1: Sanitize text inputs
        $sanitize = function ($value) {
            $value = is_string($value) ? trim($value) : $value;
            return ($value === '' || $value === null) ? null : strip_tags($value);
        };

        // Step 2: Transactional Save with Locking
        try {
            DB::transaction(function () use ($sanitize) {
                // LOCK the request row to prevent race conditions
                $freshRequest = \App\Models\CRM\FeedbackRequest::where('id', $this->feedbackRequest->id)
                    ->lockForUpdate()
                    ->first();

                // Strict Status Check inside the lock
                if ($freshRequest->status === \App\Models\CRM\FeedbackRequest::STATUS_SUBMITTED) {
                    Log::warning('FeedbackSubmitForm: Race condition blocked - Already submitted', ['request_id' => $freshRequest->id]);
                    throw new \Exception('This feedback has already been submitted.');
                }

                if (!$freshRequest->feedback_id) {
                     throw new \Exception('System Error: Feedback Request is not linked to a feedback record.');
                }

                // Prepare Data
                $feedbackData = [
                    'customer_id' => $this->contact->customer->id,
                    'contact_id' => $this->contact_id, 
                    'service_type' => is_array($this->service_type) ? implode(', ', $this->service_type) : $this->service_type,
                    'service_type_other' => $sanitize($this->service_type_other),
                    'service_reference_no' => $sanitize($this->service_reference_no),
                    'equipment_sample_id' => $sanitize($this->equipment_sample_id),
                    'results_issued_date' => $sanitize($this->results_issued_date),
                    'specific_feedback' => $sanitize($this->specific_feedback),
                    'suggestions' => $sanitize($this->suggestions),
                    'will_recommend' => $this->will_recommend,
                    'consent_contact' => $this->consent_contact,
                    'preferred_contact_method' => $sanitize($this->preferred_contact_method),
                    'status' => CustomerFeedback::STATUS_SUBMITTED,
                    'is_submitted' => true,
                    'submitted_at' => now(),
                    // Phase 2 Fields
                    'contact_position' => $sanitize($this->contact_position),
                    'contact_phone' => $sanitize($this->contact_phone),
                    'usage_duration' => $sanitize($this->usage_duration),
                    'doc_ref' => $sanitize($this->doc_ref),
                    'doc_version' => $sanitize($this->doc_version),
                    // AmSpec Fields
                    'business_frequency' => $this->business_frequency,
                    'hear_about_us' => $this->hear_about_us,
                    'hear_about_us_other' => $sanitize($this->hear_about_us_other),
                    'critical_services' => $this->critical_services,
                ];

                $this->feedback = CustomerFeedback::findOrFail($freshRequest->feedback_id);
                $this->feedback->update($feedbackData);

                // Save Dynamic Ratings
                $ratingsData = [];
                $activeMetrics = \App\Models\CRM\EvaluationMetric::whereIn('id', array_keys($this->dynamic_ratings))->get()->keyBy('id');
                $totalPercentage = 0;
                $metricsCount = 0;

                foreach ($this->dynamic_ratings as $metricId => $score) {
                    $ratingsData[] = [
                        'id' => (string) Str::uuid(),
                        'customer_feedback_id' => $this->feedback->id,
                        'evaluation_metric_id' => $metricId,
                        'rating' => $score,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    if (isset($activeMetrics[$metricId]) && $activeMetrics[$metricId]->max_rating > 0) {
                        $totalPercentage += ((float) $score / $activeMetrics[$metricId]->max_rating);
                        $metricsCount++;
                    }
                }

                if (!empty($ratingsData)) {
                    \App\Models\CRM\FeedbackRating::insert($ratingsData);
                }

                if ($metricsCount > 0) {
                    // Normalize to a 10-point scale
                    $overallRatio = ($totalPercentage / $metricsCount);
                    $scaledScore = round($overallRatio * 10, 2);
                    $this->feedback->update(['rating_overall' => $scaledScore]);
                }

                // Mark Request as Submitted
                $freshRequest->markAsSubmitted($this->feedback->id);

                 // Trigger Alerts (inside transaction or after? inside is fine for now, mail is queued usually)
                $this->triggerAlertIfNeeded($this->feedback);
            });

            $this->isSubmitted = true;
            Log::info('FeedbackSubmitForm: Process completed successfully');

        } catch (\Exception $e) {
            if ($e->getMessage() === 'This feedback has already been submitted.') {
                $this->linkUsed = true;
                session()->flash('error', $e->getMessage());
            } else {
                Log::error('FeedbackSubmitForm: Transaction failed', ['error' => $e->getMessage()]);
                session()->flash('error', 'An error occurred while saving your feedback. Please try again.');
                throw $e; // Re-throw for Livewire to handle standard errors if needed
            }
        }
    }

    /**
     * Send alert email if feedback contains poor ratings, issues, or ISO concerns.
     */
    private function triggerAlertIfNeeded($feedback)
    {
        // Collect any rating below or equal to 40% of max rating (e.g. <= 4 for max_rating 10)
        $lowRatings = [];
        $feedback->load('ratings.metric');
        foreach ($feedback->ratings as $ratingRecord) {
            if ($ratingRecord->metric) {
                $maxRating = $ratingRecord->metric->max_rating ?: 4;
                $threshold = $maxRating * 0.4;
                if ($ratingRecord->rating <= $threshold) {
                    $lowRatings[] = [
                        'metric_name' => $ratingRecord->metric->name,
                        'rating' => $ratingRecord->rating,
                        'max_rating' => $maxRating,
                    ];
                }
            }
        }

        $needsAlert = !empty($lowRatings);

        if ($needsAlert) {
            try {
                // Load relationships for the email view
                $feedback->load('contact.customer');

                // Send to the first admin user or a configured email
                $adminEmail = config('mail.from.address', 'admin@example.com');
                Mail::to($adminEmail)->send(new LowScoreAlert($feedback, $lowRatings));

                Log::info('Feedback Alert sent for Feedback #' . $feedback->id);
            } catch (\Exception $e) {
                Log::error('Failed to send Feedback Alert: ' . $e->getMessage());
            }
        }
    }

    public function render()
    {
        return view('livewire.crm.feedback.feedback-submit-form', [
            'activeMetrics' => \App\Models\CRM\EvaluationMetric::where('is_active', true)->orderBy('display_order')->get()
        ])
            ->layout('layouts.auth');
    }
}
