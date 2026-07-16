<?php

namespace Tests\Unit\Services\Personnel;

use App\Services\Personnel\PersonnelProfileService;
use App\Services\Personnel\PersonnelSignatureService;
use App\User;
use Tests\TestCase;

class PersonnelProfileServiceTest extends TestCase
{
    public function test_update_assigns_lab_section_ids_to_user_before_save(): void
    {
        $user = new class extends User {
            public bool $wasSaved = false;

            public array $savedAttributes = [];

            public function save(array $options = []): bool
            {
                $this->wasSaved = true;
                $this->savedAttributes = $this->getAttributes();

                return true;
            }

            public function fresh($with = []): static
            {
                return $this;
            }
        };

        $user->id = 'user-test-id';

        $service = new PersonnelProfileService(new PersonnelSignatureService());

        $service->update($user, [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'id_number' => 'ID-001',
            'lab_section_ids' => ['section-a', 'section-b', 'section-a'],
            'lab_ids' => [],
        ]);

        $this->assertTrue($user->wasSaved);
        $this->assertSame('section-a,section-b', $user->lab_section_id);
        $this->assertSame('section-a,section-b', $user->savedAttributes['lab_section_id'] ?? null);
    }
}
