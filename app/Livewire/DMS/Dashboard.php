<?php

namespace App\Livewire\DMS;

use Livewire\Component;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentType;
use App\Models\DMS\DocumentAmendment;
use App\Models\DMS\DocumentNotification;
use App\Services\DMS\DocumentExpiryService;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $stats = [];
    public $recentDocuments = [];
    public $recentAmendments = [];
    public $expiringDocuments = [];
    public $unreadNotifications = [];
    public $amendmentTrends = [];
    
    // Computed properties for better performance
    public $expiringDocumentsWithDays = [];

    protected $expiryService;

    public function boot(DocumentExpiryService $expiryService)
    {
        $this->expiryService = $expiryService;
    }

    public function mount(): void
    {
        $this->loadStats();
        $this->loadRecentActivity();
        $this->loadExpiringDocuments();
        $this->loadNotifications();
        $this->loadAmendmentTrends();
    }

    public function loadStats(): void
    {
        $user = auth()->user();
        $cacheKey = "dms_stats_user_{$user->id}";

        // Cache stats for 5 minutes to improve performance
        $this->stats = \Cache::remember($cacheKey, 300, function () use ($user) {
            // Optimize with single query using DB::raw for multiple counts
            $stats = \DB::table('documents')
                ->selectRaw('
                    COUNT(CASE WHEN deleted_at IS NULL THEN 1 END) as total_documents,
                    COUNT(CASE WHEN owner_id = ? AND deleted_at IS NULL THEN 1 END) as my_documents,
                    COUNT(CASE WHEN status = ? AND deleted_at IS NULL THEN 1 END) as pending_approvals,
                    COUNT(CASE WHEN expiry_date <= ? AND deleted_at IS NULL THEN 1 END) as expiring_soon,
                    COUNT(CASE WHEN deleted_at IS NOT NULL THEN 1 END) as archived_documents,
                    COUNT(CASE WHEN MONTH(created_at) = ? AND YEAR(created_at) = ? AND deleted_at IS NULL THEN 1 END) as documents_this_month
                ', [
                    $user->id,
                    'pending_approval',
                    now()->addDays(30),
                    now()->month,
                    now()->year
                ])
                ->first();

            $documentTypeCount = \DB::table('document_types')
                ->where('is_active', true)
                ->count();

            $amendmentCount = \DB::table('document_amendments')
                ->where('status', 'requested')
                ->count();

            return [
                'total_types' => $documentTypeCount,
                'total_documents' => $stats->total_documents ?? 0,
                'my_documents' => $stats->my_documents ?? 0,
                'pending_approvals' => $stats->pending_approvals ?? 0,
                'pending_amendments' => $amendmentCount,
                'expiring_soon' => $stats->expiring_soon ?? 0,
                'archived_documents' => $stats->archived_documents ?? 0,
                'documents_this_month' => $stats->documents_this_month ?? 0,
            ];
        });
    }

    public function loadRecentActivity(): void
    {
        $this->recentDocuments = Document::with(['documentType', 'owner', 'creator'])
            ->active()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $this->recentAmendments = DocumentAmendment::with(['document', 'requester'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }

    public function loadExpiringDocuments(): void
    {
        $this->expiringDocuments = $this->expiryService
            ->getExpiringDocumentsForUser(auth()->user(), 30);
            
        // Pre-calculate days remaining to avoid PHP logic in Blade
        $this->expiringDocumentsWithDays = $this->expiringDocuments->map(function ($doc) {
            $daysRemaining = $doc->expiry_date->diffInDays(now());
            return [
                'document' => $doc,
                'days_remaining' => $daysRemaining,
                'badge_class' => $daysRemaining <= 7 ? 'danger' : 'warning'
            ];
        });
    }

    public function loadNotifications(): void
    {
        $this->unreadNotifications = DocumentNotification::where('user_id', auth()->id())
            ->unread()
            ->with('document')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }

    public function loadAmendmentTrends(): void
    {
        // Get amendment data for the last 6 months
        $trends = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $count = DocumentAmendment::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();
            
            $trends[] = [
                'month' => $date->format('M Y'),
                'count' => $count,
            ];
        }

        $this->amendmentTrends = $trends;
    }

    public function markNotificationRead($notificationId): void
    {
        $notification = DocumentNotification::find($notificationId);
        if ($notification && $notification->user_id === auth()->id()) {
            $notification->markAsRead();
            $this->loadNotifications();
        }
    }

    public function markAllNotificationsRead(): void
    {
        DocumentNotification::where('user_id', auth()->id())
            ->unread()
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $this->loadNotifications();
    }

    public function refreshStats(): void
    {
        // Clear cached stats before reloading
        $user = auth()->user();
        $cacheKey = "dms_stats_user_{$user->id}";
        \Cache::forget($cacheKey);
        
        $this->loadStats();
        $this->loadRecentActivity();
        $this->loadExpiringDocuments();
        $this->loadNotifications();
    }

    public function render()
    {
        return view('livewire.dms.dashboard-component')->layout('layouts.app');
    }
}

