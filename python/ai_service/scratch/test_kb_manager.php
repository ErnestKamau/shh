<?php

use App\Http\Controllers\AI\AiKnowledgeBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;

// 1. Find a user to simulate login (preferably an admin)
$user = User::where('email', 'admin@polucon.com')->first() ?: User::first();
if (!$user) {
    echo "ERROR: No user found to perform test.\n";
    exit(1);
}

auth()->login($user);
echo "Logged in as: " . $user->email . "\n";

// 2. Prepare Test Data
$testData = [
    'title' => 'Verification Test Document - ' . now()->toDateTimeString(),
    'collection' => 'verification_test',
    'content' => 'This is a test document to verify that the Knowledge Base Manager backend is working correctly. It includes some sample text about laboratory safety protocols and compliance standards.',
    'permission' => 'General.View'
];

echo "Submitting manual document...\n";

try {
    $controller = app(AiKnowledgeBaseController::class);
    $request = Request::create('/imara-ai/knowledge', 'POST', $testData);
    $response = $controller->store($request);
    
    $result = json_decode($response->getContent(), true);
    echo "Response Status: " . ($result['status'] ?? 'unknown') . "\n";
    echo "Response Message: " . ($result['message'] ?? 'none') . "\n";

    if ($result['status'] !== 'ok') {
        echo "FAILURE: Controller returned error.\n";
        exit(1);
    }

    // 3. Verify in Database
    echo "Verifying database entries...\n";
    
    // Check manual_documents
    $doc = DB::connection('pgsql_ai')
        ->table('ai.ai_manual_documents')
        ->where('title', $testData['title'])
        ->first();
        
    if ($doc) {
        echo "SUCCESS: Manual document found in pgsql_ai.ai_manual_documents (ID: {$doc->id})\n";
    } else {
        echo "FAILURE: Manual document NOT found in database.\n";
        exit(1);
    }

    // Wait a brief moment for background indexing to complete (though it was likely synchronous in the controller call)
    // Actually, AiKnowledgeBaseController calls $this->inference->indexKnowledge which is synchronous in its HTTP call.
    
    // Check knowledge_chunks
    $chunks = DB::connection('pgsql_ai')
        ->table('ai.ai_knowledge_chunks')
        ->whereRaw("metadata->>'document_id' = ?", [(string)$doc->id])
        ->get();
        
    if ($chunks->count() > 0) {
        echo "SUCCESS: Found " . $chunks->count() . " chunks in pgsql_ai.ai_knowledge_chunks with embeddings.\n";
        foreach ($chunks as $chunk) {
            $meta = json_decode($chunk->metadata, true);
            echo " - Chunk ID: " . $chunk->chunk_id . " | Label: " . ($meta['display_label'] ?? 'N/A') . "\n";
        }
    } else {
        echo "FAILURE: No chunks found for the document. Indexing might have failed.\n";
        exit(1);
    }

    echo "\nBackend test for Knowledge Base Manager completed SUCCESSFULLY.\n";

} catch (\Exception $e) {
    echo "CRITICAL ERROR during test: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
