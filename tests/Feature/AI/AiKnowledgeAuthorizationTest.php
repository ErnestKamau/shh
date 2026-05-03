<?php

namespace Tests\Feature\AI;

use App\Models\AI\AiManualDocument;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AiKnowledgeAuthorizationTest extends TestCase
{
    use DatabaseTransactions;

    protected array $manualDocumentIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        if (! $this->requiredTablesExist()) {
            $this->markTestSkipped('AI knowledge authorization tables are not available for feature tests.');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        if ($this->manualDocumentIds !== []) {
            try {
                AiManualDocument::query()->whereIn('id', $this->manualDocumentIds)->delete();
            } catch (\Throwable $exception) {
                // Ignore cleanup failures when the AI repository connection is unavailable.
            }
        }

        parent::tearDown();
    }

    public function test_owner_can_view_their_manual_document(): void
    {
        $owner = User::factory()->create();
        $document = $this->createManualDocument($owner);

        $this->actingAs($owner)
            ->getJson("/imara-ai/knowledge/{$document->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $document->id)
            ->assertJsonPath('data.title', $document->title);
    }

    public function test_user_with_ai_knowledge_manage_permission_can_view_another_users_document(): void
    {
        $owner = User::factory()->create();
        $manager = User::factory()->create();
        $this->grantPermission($manager, 'ai.knowledge.manage');

        $document = $this->createManualDocument($owner);

        $this->actingAs($manager)
            ->getJson("/imara-ai/knowledge/{$document->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $document->id);
    }

    public function test_admin_can_view_another_users_document_without_explicit_ai_permission(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $this->assignRole($admin, 'admin');

        $document = $this->createManualDocument($owner);

        $this->actingAs($admin)
            ->getJson("/imara-ai/knowledge/{$document->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $document->id);
    }

    public function test_non_owner_without_manage_permission_cannot_view_document(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $document = $this->createManualDocument($owner);

        $this->actingAs($otherUser)
            ->getJson("/imara-ai/knowledge/{$document->id}")
            ->assertForbidden()
            ->assertJsonPath('message', 'Unauthorized to edit this document.');
    }

    private function requiredTablesExist(): bool
    {
        if (! Schema::hasTable('users')) {
            return false;
        }

        if (! Schema::hasTable('spatie_permissions') || ! Schema::hasTable('spatie_roles')) {
            return false;
        }

        try {
            return Schema::connection('pgsql_ai')->hasTable('ai.ai_manual_documents');
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private function createManualDocument(User $owner): AiManualDocument
    {
        $document = AiManualDocument::query()->create([
            'title' => 'Authorization Test Document',
            'collection_name' => 'tests',
            'content' => 'Knowledge base content.',
            'required_permission' => 'general.view',
            'created_by' => $owner->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->manualDocumentIds[] = $document->id;

        return $document;
    }

    private function grantPermission(User $user, string $permissionName): void
    {
        $permission = Permission::findOrCreate($permissionName, 'web');
        $user->givePermissionTo($permission);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function assignRole(User $user, string $roleName): void
    {
        $role = Role::findOrCreate($roleName, 'web');
        $user->assignRole($role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}