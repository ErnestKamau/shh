<?php

namespace Database\Seeders;

use App\Event;
use App\Models\CRM\CRMCustomer;
use App\User;
use Database\Seeders\Concerns\ClearsSeedCommercialDemoData;
use Database\Seeders\Concerns\ResolvesAmSpecCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Phase16CalendarPlannerSeeder extends Seeder
{
    use ClearsSeedCommercialDemoData;
    use ResolvesAmSpecCompany;

    public function run(): void
    {
        config(['database.default' => 'pgsql']);
        Model::unguard();

        DB::connection('pgsql')->transaction(function (): void {
            $this->command?->info('====================================================');
            $this->command?->info('STARTING PHASE 16 SEEDING: System Planner / Calendar Events');
            $this->command?->info('====================================================');

            $company = $this->resolveAmSpecCompany();
            if (! $company) {
                $this->command?->error('Base company not found. Run Phase 1 first.');

                return;
            }

            $staffUser = User::query()->where('active', 1)->where('is_client', 0)->orderBy('id')->first()
                ?? User::query()->orderBy('id')->first();

            if (! $staffUser) {
                $this->command?->error('No staff user found for calendar events.');

                return;
            }

            $this->clearSeedCalendarPlannerData();

            $customerCodes = ['INT-ENRG-001', 'EXT-ENRG-001', 'EXT-ENRG-002'];
            $created = 0;

            foreach ($customerCodes as $index => $customerCode) {
                $customer = CRMCustomer::query()
                    ->where('company_id', $company->id)
                    ->where('code', $customerCode)
                    ->first();

                if ($customer === null) {
                    $this->command?->warn("Skipping calendar events for missing customer {$customerCode}.");

                    continue;
                }

                $created += $this->seedPlannerEventsForCustomer($customer, $staffUser, $index);
            }

            $this->command?->info("Seeded {$created} system planner calendar event(s) for 3 clients.");
            $this->command?->info('====================================================');
            $this->command?->info('PHASE 16 SEEDING COMPLETED SUCCESSFULLY!');
            $this->command?->info('====================================================');
        });

        Model::reguard();
    }

    private function seedPlannerEventsForCustomer(CRMCustomer $customer, User $staffUser, int $index): int
    {
        $definitions = [
            [
                'title' => 'SEED-Site Sampling Visit — '.$customer->name,
                'description' => 'Planned client sampling visit and chain-of-custody handover.',
                'status' => 'Scheduled',
                'start_offset' => 3 + ($index * 2),
                'duration_days' => 1,
                'location' => $customer->physical_address ?? 'Client site',
            ],
            [
                'title' => 'SEED-Lab Booking — '.$customer->name,
                'description' => 'Reserved laboratory bench time for incoming client batch.',
                'status' => 'Confirmed',
                'start_offset' => 7 + ($index * 2),
                'duration_days' => 2,
                'location' => 'AmSpec Dubai Laboratory',
            ],
        ];

        $count = 0;

        foreach ($definitions as $definition) {
            $startDate = now()->addDays($definition['start_offset'])->toDateString();
            $endDate = now()->addDays($definition['start_offset'] + $definition['duration_days'] - 1)->toDateString();
            $eventId = (string) Str::uuid();

            Event::query()->create([
                'id' => $eventId,
                'title' => $definition['title'],
                'description' => $definition['description'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'responsible_id' => (string) $staffUser->id,
                'client_id' => $customer->id,
                'location' => $definition['location'],
                'created_by' => $staffUser->id,
                'status' => $definition['status'],
                'is_client_notify' => true,
                'is_routine' => false,
                'has_notification' => false,
                'notification_sent' => false,
                'parent_id' => $eventId,
                'contract_valid_from' => now()->toDateString(),
                'contract_valid_to' => now()->addYear()->toDateString(),
            ]);

            $count++;
        }

        return $count;
    }
}
