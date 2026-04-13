<?php

namespace App\Livewire\Crm\Feedback;

use App\Models\CRM\CustomerFeedback;
use App\Models\CRM\CRMCustomer;
use App\Models\System\SystemConfiguration;
use App\Models\System\SystemConfigurationsType;
use Livewire\Attributes\On;
use App\Livewire\Crm\BaseCrmComponent;

class FeedbackForm extends BaseCrmComponent
{
    public $feedbackId = null;
    public $customerId = null;
    public $feedback = '';
    public $received_from = '';
    public $status = 0;
    public $date = '';
    public $isActive = true;
    public $customers = [];

    public function mount($feedbackId = null, $customerId = null)
    {
        $this->initialize();
        $this->customers = CRMCustomer::where('company_id', $this->getUserCompany())->orderBy('name')->get();
        $this->date = date('Y-m-d');

        if ($feedbackId) {
            $feedback = CustomerFeedback::find($feedbackId);
            if ($feedback) {
                $this->feedbackId = $feedback->id;
                $this->feedback = $feedback->feedback;
                $this->received_from = $feedback->received_from;
                $this->status = $feedback->status;
                $this->isActive = ($this->status == 0);
                $this->date = $feedback->date;
                $this->customerId = $feedback->customer_id;
            }
        } elseif ($customerId) {
            $customer = CRMCustomer::find($customerId);
            if ($customer) {
                $this->customerId = $customer->id;
                $this->received_from = $customer->name;
            }
        }
    }

    protected function rules()
    {
        return [
            'feedback' => 'required|string',
            'received_from' => 'required|string',
            'date' => 'required|date',
            'status' => 'boolean',
        ];
    }

    public function save()
    {
        $this->status = $this->isActive ? 0 : 1;
        $this->validate();

        if ($this->feedbackId) {
            $this->checkPermission('CRM.components.Feedbacks.Edit');
            $feedback = CustomerFeedback::find($this->feedbackId);
            $feedback->edited_by = auth()->user()->name;
        } else {
            $this->checkPermission('CRM.components.Feedbacks.Add');
            $feedback = new CustomerFeedback();
            $feedback->registered_by = auth()->user()->name;
            $feedback->user_type = 'Customer';
        }

        $feedback->feedback = $this->feedback;
        $feedback->received_from = $this->received_from;
        $feedback->status = $this->status;
        $feedback->date = $this->date;

        // PRIORITIZE: Use the explicit customerId if available
        if ($this->customerId) {
            $feedback->customer_id = $this->customerId;
        } 
        // FALLBACK: Only try name lookup if we don't have an ID (e.g. from standalone form)
        else {
            $customer = CRMCustomer::where('name', $this->received_from)->first();
            if ($customer) {
                $feedback->customer_id = $customer->id;
            }
        }

        $feedback->save();

        // Generate code if new
        if (!$this->feedbackId) {
            if (strlen($feedback->id) < 4) {
                $diff = 4 - strlen($feedback->id);
                $zero = str_repeat("0", $diff);
                $feedback->code = "FB" . $zero . $feedback->id;
            } else {
                $feedback->code = "FB" . $feedback->id;
            }
            $feedback->save();

            // Send email notifications
            $config = SystemConfigurationsType::where('configuration_type', 'Personnel to Recieve Feedback and Complaint Notification')->first();
            if ($config) {
                $config_users = SystemConfiguration::where('configuration_type_id', $config->id)->get();
                $company = getCompanyDetails();
                foreach ($config_users as $user) {
                    $subject = '[' . $company['name'] . '] Customer Feedback Notification - ' . $feedback->code;
                    $body = 'Hi ' . $user->key . ', <br> We hereby inform you that there is a Customer feedback of ID <b>' . $feedback->code . '</b> that needs your attention.<br>Kindly review it.<br>Regards ' . $company['name'];
                    notify_user($body, $user->value, $subject);
                }
            }
        }

        $this->showSuccess($this->feedbackId ? 'Feedback edited successfully!' : 'Feedback added successfully!');
        $this->dispatch('feedback-saved');
        $this->close();
    }

    public function close()
    {
        $this->dispatch('feedback-form-closed');
    }

    public function render()
    {
        return view('livewire.crm.feedback.feedback-form');
    }
}
