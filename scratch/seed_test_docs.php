<?php

use App\Models\DMS\Document;
use Illuminate\Support\Str;

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$docType = \App\Models\DMS\DocumentType::firstOrCreate(
    ['name' => 'General Documentation'],
    ['code' => 'GEN', 'description' => 'General purpose documentation']
);

$testDocs = [
    [
        'title' => 'SOP-TEST-001: Laboratory Safety Protocol',
        'document_number' => 'SOP-TEST-001',
        'description' => 'Comprehensive safety protocol for all laboratory personnel.',
        'kb_content' => "LABORATORY SAFETY PROTOCOL v1.0\n\n1. Always wear Personal Protective Equipment (PPE) including lab coats, gloves, and safety goggles.\n2. No eating or drinking is allowed in the laboratory areas.\n3. All chemical spills must be reported immediately to the supervisor.\n4. Familiarize yourself with the location of eye wash stations and fire extinguishers.",
        'is_kb_indexed' => true,
    ],
    [
        'title' => 'SOP-TEST-002: Sample Handling Procedure',
        'document_number' => 'SOP-TEST-002',
        'description' => 'Standard operating procedure for receiving and logging samples.',
        'kb_content' => "SAMPLE HANDLING PROCEDURE v2.1\n\n1. Samples must be received in sealed, leak-proof containers.\n2. Each sample must have a unique identifier tag attached.\n3. Log the arrival time, temperature (if applicable), and condition of the sample in the LIMS.\n4. Store samples in the designated refrigerated area at 4°C within 30 minutes of receipt.",
        'is_kb_indexed' => true,
    ],
    [
        'title' => 'POLICY-TEST-001: Data Privacy Policy',
        'document_number' => 'POLICY-TEST-001',
        'description' => 'Official policy regarding the handling of sensitive laboratory and client data.',
        'kb_content' => "DATA PRIVACY POLICY v1.5\n\n1. All client data must be encrypted at rest and in transit.\n2. Access to sensitive test results is restricted to authorized personnel only.\n3. Personal Identifiable Information (PII) must be redacted from public reports.\n4. Periodic audits of data access logs will be conducted to ensure compliance with GDPR and local regulations.",
        'is_kb_indexed' => true,
    ],
];

foreach ($testDocs as $docData) {
    $existing = Document::where('document_number', $docData['document_number'])->first();
    if ($existing) {
        $existing->update($docData);
        echo "Updated: {$docData['document_number']}\n";
    } else {
        Document::create($docData + [
            'document_type_id' => $docType->id,
            'status' => 'approved',
            'approval_status' => 'approved',
            'owner_id' => 1, // Assuming admin user ID is 1
            'created_by' => 1,
            'version_number' => 1,
            'amendment_count' => 0,
        ]);
        echo "Created: {$docData['document_number']}\n";
    }
}
