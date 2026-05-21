<?php

namespace Tests\Unit\Lab;

use App\Models\Equipments\Equipment;
use App\Models\Lab\EquipmentUsageRequest;
use App\Services\Lab\EquipmentUsageRequestNotificationService;
use App\User;
use Tests\TestCase;

class EquipmentUsageRequestServiceTest extends TestCase
{
    public function test_approved_notification_message_includes_equipment_name(): void
    {
        $request = new EquipmentUsageRequest([
            'id' => 'req-1',
            'requester_id' => 'user-1',
            'approval_comment' => 'Scheduled after calibration',
            'approved_start_at' => now(),
            'approved_end_at' => now()->addHours(2),
        ]);

        $request->setRelation('equipment', new Equipment(['name' => 'GC-MS']));
        $request->setRelation('helpingAnalyst', new User(['name' => 'Jane Analyst']));
        $request->setRelation('approver', new User(['name' => 'Lead Approver']));

        $service = new EquipmentUsageRequestNotificationService();

        $this->assertStringContainsString(
            'GC-MS',
            $service->buildApprovedMessage($request)
        );
    }
}
