<?php

namespace App\Livewire\Mas;

use App\SampleHeader;
use Illuminate\Support\Facades\DB;

class Crm extends BaseMasPage
{
    public array $stats = [];

    public function mount(): void
    {
        $this->loadStats();
    }

    public function refresh(): void
    {
        $this->loadStats();
    }

    private function loadStats(): void
    {
        $topClients = DB::table('crm_customers')
            ->join('sample_headers', 'sample_headers.crm_customer_id', '=', 'crm_customers.id')
            ->select('crm_customers.name', DB::raw('count(sample_headers.id) as total'))
            ->groupBy('crm_customers.id', 'crm_customers.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $orderTrend = SampleHeader::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $this->stats = [
            'total_clients'   => DB::table('crm_customers')->count(),
            'recent_orders'   => SampleHeader::where('created_at', '>=', now()->subDays(30))->count(),
            'unpaid_invoices' => DB::table('customer_invoice')->count(),
            'top_clients'     => $topClients,
            'order_trend'     => $orderTrend,
        ];
    }

    public function render()
    {
        return view('livewire.mas.crm', ['stats' => $this->stats]);
    }
}
