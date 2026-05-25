import sys
sys.path.append('.')
from python.ai_service.core.manifest_intent_router import _GROUP_A_RULES, _GROUP_B_RULES
import json
from pathlib import Path

# 1. Load manifest JSON
manifest_path = Path('python/ai_service/core/live_data_manifest.json')
with manifest_path.open('r', encoding='utf-8') as f:
    manifest_data = json.load(f)

# 2. Extract patterns and groups
patterns_map = {}
groups_map = {}

for domain, rules in _GROUP_A_RULES.items():
    for patterns, intent_id in rules:
        patterns_map.setdefault(intent_id, []).extend(patterns)
        groups_map[intent_id] = 'A'

for domain, rules in _GROUP_B_RULES.items():
    for patterns, intent_id in rules:
        patterns_map.setdefault(intent_id, []).extend(patterns)
        groups_map[intent_id] = 'B'

# 3. Build PHP representation for manifest intents
php_intents = []
for domain, intents in manifest_data.items():
    for intent_id, info in intents.items():
        group_type = groups_map.get(intent_id, 'A')
        sql_query = info.get('sql', '').replace("'", "\\'")
        description = info.get('description', '').replace("'", "\\'")
        output_format = info.get('output_format', 'table')
        ttl_seconds = info.get('ttl_seconds', 120)
        
        php_intents.append(f"""            [
                'id' => '{intent_id}',
                'domain' => '{domain}',
                'group_type' => '{group_type}',
                'sql_query' => '{sql_query}',
                'description' => '{description}',
                'output_format' => '{output_format}',
                'ttl_seconds' => {ttl_seconds},
            ]""")

php_intents_str = ",\n".join(php_intents)

# 4. Build PHP representation for patterns
php_patterns = []
valid_intent_ids = set()
for domain, intents in manifest_data.items():
    for intent_id in intents.keys():
        valid_intent_ids.add(intent_id)

for intent_id, patterns in patterns_map.items():
    if intent_id not in valid_intent_ids:
        continue
    for p in patterns:
        p_safe = p.lower().strip().replace("'", "\\'")
        php_patterns.append(f"""            [
                'intent_id' => '{intent_id}',
                'pattern' => '{p_safe}',
            ]""")

php_patterns_str = ",\n".join(php_patterns)

# 5. Generate complete PHP Seeder file template
template = """<?php

namespace Database\\Seeders\\Setup;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;

class AiManifestIntentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $connection = 'pgsql_ai';

        $this->command?->info('====================================================');
        $this->command?->info('STARTING SEEDING: AI Manifest Intents & Patterns');
        $this->command?->info('====================================================');

        DB::connection($connection)->transaction(function () use ($connection) {
            // 1. Clear existing manifest records
            DB::connection($connection)->table('manifest_intent_patterns')->delete();
            DB::connection($connection)->table('manifest_intents')->delete();
            $this->command?->info('Cleared existing AI manifest and pattern records.');

            // 2. Define intents
            $intents = [
__INTENTS_PLACEHOLDER__
            ];

            // 3. Define patterns
            $patterns = [
__PATTERNS_PLACEHOLDER__
            ];

            // 4. Insert intents
            foreach ($intents as $intent) {
                DB::connection($connection)->table('manifest_intents')->insert(array_merge($intent, [
                    'active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
            $this->command?->info('Successfully seeded ' . count($intents) . ' manifest intents.');

            // 5. Insert patterns
            foreach ($patterns as $pattern) {
                DB::connection($connection)->table('manifest_intent_patterns')->insert(array_merge($pattern, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
            $this->command?->info('Successfully seeded ' . count($patterns) . ' intent patterns.');
        });

        $this->command?->info('====================================================');
        $this->command?->info('AI SEEDING COMPLETED SUCCESSFULLY!');
        $this->command?->info('====================================================');
    }
}
"""

seeder_content = template.replace('__INTENTS_PLACEHOLDER__', php_intents_str).replace('__PATTERNS_PLACEHOLDER__', php_patterns_str)

# 6. Write file
output_file = Path('database/seeders/Setup/AiManifestIntentsSeeder.php')
output_file.parent.mkdir(parents=True, exist_ok=True)
with output_file.open('w', encoding='utf-8') as f:
    f.write(seeder_content)

print('Successfully generated seeder at database/seeders/Setup/AiManifestIntentsSeeder.php')
