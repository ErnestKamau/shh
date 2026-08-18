<?php

namespace App\Services\CRM;

use App\Models\CRM\Complaint;
use Illuminate\Support\Facades\DB;

class ComplaintReferenceService
{
    public function generate(): string
    {
        $prefix = 'COMP/' . now()->format('Ymd') . '/';

        return DB::transaction(function () use ($prefix): string {
            $latest = Complaint::query()
                ->where('complaint_id', 'like', $prefix . '%')
                ->lockForUpdate()
                ->orderByDesc('complaint_id')
                ->value('complaint_id');

            $nextNumber = 1;
            if ($latest !== null) {
                $tail = (int) substr($latest, strrpos($latest, '/') + 1);
                $nextNumber = $tail + 1;
            }

            return $prefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }
}
