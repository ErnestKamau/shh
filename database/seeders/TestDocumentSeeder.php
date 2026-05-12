<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DMS\Document;
use App\Models\DMS\DocumentType;
use App\User;

class TestDocumentSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
            ]);
        }

        $docType = DocumentType::firstOrCreate(
            ['name' => 'General Documentation'],
            ['code' => 'GEN', 'description' => 'General purpose documentation']
        );

        Document::create([
            'title' => 'SOP-TEST-001: Laboratory Safety Protocol',
            'document_number' => 'SOP-TEST-001',
            'document_type_id' => $docType->id,
            'kb_content' => 'LABORATORY SAFETY PROTOCOL v1.0: Always wear PPE. No eating in lab. Report spills.',
            'is_kb_indexed' => true,
            'status' => 'approved',
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'file_path' => 'test/sop001.pdf',
            'file_name' => 'sop001.pdf',
            'version_number' => 1,
            'amendment_count' => 0
        ]);

        Document::create([
            'title' => 'SOP-TEST-002: Sample Handling Procedure',
            'document_number' => 'SOP-TEST-002',
            'document_type_id' => $docType->id,
            'kb_content' => 'SAMPLE HANDLING: Sealed containers only. Store at 4°C. Log identifier in LIMS.',
            'is_kb_indexed' => true,
            'status' => 'approved',
            'owner_id' => $user->id,
            'created_by' => $user->id,
            'file_path' => 'test/sop002.pdf',
            'file_name' => 'sop002.pdf',
            'version_number' => 1,
            'amendment_count' => 0
        ]);
    }
}
