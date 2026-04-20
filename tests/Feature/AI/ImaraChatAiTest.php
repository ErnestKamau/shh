<?php

namespace Tests\Feature\AI;

use App\User;
use App\Models\AI\AiConversation;
use App\Models\AI\AiMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImaraChatAiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a user and a company for tests
        $this->user = User::factory()->create([
            'company_id' => 1
        ]);
    }

    /**
     * Test message persistence with object metadata (Fixes 422 regression).
     */
    public function test_can_persist_message_with_array_metadata()
    {
        $convo = AiConversation::create([
            'user_id' => $this->user->id,
            'title'   => 'Test Convo'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/imara-ai/conversations/{$convo->id}/messages", [
                'role'    => 'user',
                'content' => 'Hello',
                'metadata' => ['test_key' => 'test_value'] // Sent as array
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('ai_messages', [
            'ai_conversation_id' => $convo->id,
            'role' => 'user',
            'content' => 'Hello'
        ]);
        
        $message = AiMessage::latest()->first();
        $this->assertEquals(['test_key' => 'test_value'], $message->metadata);
    }

    /**
     * Test list conversations.
     */
    public function test_can_list_conversations()
    {
        AiConversation::create([
            'user_id' => $this->user->id,
            'title'   => 'Secret Chat'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/imara-ai/conversations");

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Secret Chat']);
    }

    /**
     * Test streaming endpoint structure.
     */
    public function test_ask_stream_returns_sse_headers()
    {
        $response = $this->actingAs($this->user)
            ->post("/imara-ai/ask-stream", [
                'question' => 'How many samples since start of system?'
            ]);

        $response->assertHeader('Content-Type', 'text/event-stream');
        $response->assertHeader('Cache-Control', 'no-cache');
    }
}
