<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE TABLE IF NOT EXISTS ai_chat_attachments (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                ai_conversation_id BIGINT UNSIGNED NOT NULL,
                ai_message_id BIGINT UNSIGNED NULL,
                original_name VARCHAR(255) NOT NULL,
                stored_path VARCHAR(500) NOT NULL,
                mime_type VARCHAR(100) NOT NULL,
                file_size BIGINT UNSIGNED NOT NULL DEFAULT 0,
                processing_status ENUM('pending','extracted','indexed','failed') NOT NULL DEFAULT 'pending',
                extracted_text LONGTEXT NULL,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                CONSTRAINT fk_ai_chat_att_convo FOREIGN KEY (ai_conversation_id)
                    REFERENCES ai_conversations(id) ON DELETE CASCADE,
                CONSTRAINT fk_ai_chat_att_msg FOREIGN KEY (ai_message_id)
                    REFERENCES ai_messages(id) ON DELETE SET NULL,
                INDEX idx_ai_chat_att_convo (ai_conversation_id),
                INDEX idx_ai_chat_att_status (processing_status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS ai_chat_attachments");
    }
};
