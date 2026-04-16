<?php

namespace App\Livewire\Mas;

use App\SampleHeader;
use Illuminate\Support\Facades\DB;

class Crm extends BaseMasPage
{
    public array $stats = [];
    public string $period = '1_month';
    public ?string $from = null;
    public ?string $to = null;

    public function mount(): void
    {
        $this->loadStats();
    }

    public function updatedPeriod(): void
    {
        $this->loadStats();
    }

    public function applyCustomFilter(): void
    {
        $this->loadStats();
    }

    public function refresh(): void
    {
        $this->loadStats();
    }

    private function loadStats(): void
    {
        if ($this->period === 'custom' && $this->from && $this->to) {
            $periodLabel = $this->from . ' - ' . $this->to;
            $orderTrendQuery = SampleHeader::whereBetween('created_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59']);
        } else {
            $days = match ($this->period) {
                '1_week' => 7,
                '2_weeks' => 14,
                '1_month' => 30,
                '2_months' => 60,
                'quarterly' => 90,
                'semi_annually' => 180,
                'annually' => 365,
                default => 30,
            };
            $periodLabel = __('mas/crm.label_' . $this->period);
            $orderTrendQuery = SampleHeader::where('created_at', '>=', now()->subDays($days));
        }

        $topClients = DB::table('crm_customers')
            ->join('sample_headers', 'sample_headers.crm_customer_id', '=', 'crm_customers.id')
            ->select('crm_customers.name', DB::raw('count(sample_headers.id) as total'))
            ->groupBy('crm_customers.id', 'crm_customers.name')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $orderTrend = $orderTrendQuery->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $this->stats = [
            'total_clients'   => DB::table('crm_customers')->count(),
            'recent_orders'   => SampleHeader::where('created_at', '>=', now()->subDays(30))->count(),
            'unpaid_invoices' => DB::table('customer_invoice')->count(),
            'top_clients'     => $topClients,
            'order_trend'     => $orderTrend,
            'period_label'    => $periodLabel,
        ];

        // Trigger JS update for charts
        $this->dispatch('statsUpdated', stats: $this->stats);
    }

    public function render()
    {
        return view('livewire.mas.crm', ['stats' => $this->stats]);
    }
}
