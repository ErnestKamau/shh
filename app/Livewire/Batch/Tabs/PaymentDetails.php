<?php

namespace App\Livewire\Batch\Tabs;

use App\SampleHeader;
use Livewire\Component;
use Livewire\WithPagination;

class PaymentDetails extends Component
{
    use WithPagination;

    public SampleHeader $batch;
    public string $search = '';
    public int $perPage = 10;

    protected $paginationTheme = 'bootstrap';

    public function mount(SampleHeader $batch): void
    {
        $this->batch = $batch;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getPaymentDetailsProperty()
    {
        $paymentDetails = $this->batch->payment_details();
        
        // Filter if search is active
        if ($this->search) {
            $paymentDetails = $paymentDetails->filter(function($payment) {
                return str_contains(strtolower($payment->payment_method ?? ''), strtolower($this->search)) ||
                       str_contains(strtolower($payment->ref_no ?? ''), strtolower($this->search)) ||
                       str_contains(strtolower($payment->contact_person_name ?? ''), strtolower($this->search)) ||
                       str_contains(strtolower($payment->receivername ?? ''), strtolower($this->search));
            });
        }
        
        // Manually paginate the collection
        $page = request()->get('page', 1);
        $offset = ($page - 1) * $this->perPage;
        
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $paymentDetails->slice($offset, $this->perPage)->values(),
            $paymentDetails->count(),
            $this->perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.batch.tabs.payment-details', [
            'paymentDetails' => $this->paymentDetails
        ]);
    }
}
