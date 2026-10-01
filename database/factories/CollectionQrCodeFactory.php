<?php

namespace Database\Factories;

use App\Models\CollectionQrCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CollectionQrCode>
 */
class CollectionQrCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token' => Str::random(12),
            'sampling_schedule_id' => null,
            'submission_form_instance_id' => null,
            'row_index' => null,
            'slot_no' => 1,
            'status' => CollectionQrCode::STATUS_UNLINKED,
        ];
    }

    public function forInstanceRow(string $instanceId, int $rowIndex): static
    {
        return $this->state(fn (): array => [
            'submission_form_instance_id' => $instanceId,
            'row_index' => $rowIndex,
            'slot_no' => $rowIndex + 1,
        ]);
    }

    public function forSchedule(string $scheduleId, int $slotNo = 1): static
    {
        return $this->state(fn (): array => [
            'sampling_schedule_id' => $scheduleId,
            'slot_no' => $slotNo,
        ]);
    }

    public function linkedTo(string $batchId, string $sampleDetailId): static
    {
        return $this->state(fn (): array => [
            'batch_id' => $batchId,
            'sample_detail_id' => $sampleDetailId,
            'status' => CollectionQrCode::STATUS_LINKED,
            'linked_at' => now(),
        ]);
    }

    public function void(): static
    {
        return $this->state(fn (): array => ['status' => CollectionQrCode::STATUS_VOID]);
    }
}
