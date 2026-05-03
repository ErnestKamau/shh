<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;

class GenerateModuleAccessPermissionMatrix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'report:module-access-matrix {--output=docs/module-access-permission-matrix.md : Output path relative to project root}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a markdown report of module access permissions by role';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $trackedPermissions = [
            'access inventory',
            'access laboratory',
            'access personnel',
            'access skills matrix',
            'access equipment',
            'access crm',
            'access documents',
            'access risk management',
            'access audit',
            'access help desk',
            'access system',
        ];

        $roles = Role::query()->orderBy('name')->get();

        $rows = [];
        $rolesWithAccess = 0;

        foreach ($roles as $role) {
            $granted = $role->permissions()
                ->whereIn('name', $trackedPermissions)
                ->pluck('name')
                ->sort()
                ->values()
                ->all();

            if (count($granted) > 0) {
                $rolesWithAccess++;
            }

            $rows[] = [
                'name' => $role->name,
                'count' => count($granted),
                'granted' => $granted,
            ];
        }

        $rolesWithoutAccess = $roles->count() - $rolesWithAccess;
        $outputRelative = (string) $this->option('output');
        $outputPath = base_path($outputRelative);

        $markdown = $this->buildMarkdown(
            $trackedPermissions,
            $roles->count(),
            $rolesWithAccess,
            $rolesWithoutAccess,
            $rows
        );

        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, $markdown);

        $this->info('Module access permission matrix generated successfully.');
        $this->line('Output: ' . $outputRelative);

        return self::SUCCESS;
    }

    /**
     * @param array<int, string> $trackedPermissions
     * @param array<int, array{name: string, count: int, granted: array<int, string>}> $rows
     */
    private function buildMarkdown(
        array $trackedPermissions,
        int $rolesTotal,
        int $rolesWithAccess,
        int $rolesWithoutAccess,
        array $rows
    ): string {
        $lines = [];

        $lines[] = '# Module Access Permission Matrix';
        $lines[] = '';
        $lines[] = 'Generated: ' . now()->format('Y-m-d H:i:s');
        $lines[] = 'Source: Spatie roles/permissions in DB (`guard_name=web`)';
        $lines[] = 'Tracked permissions:';
        $lines[] = '';

        foreach ($trackedPermissions as $permission) {
            $lines[] = '- ' . $permission;
        }

        $lines[] = '';
        $lines[] = '## Summary';
        $lines[] = '';
        $lines[] = '- Roles scanned: ' . $rolesTotal;
        $lines[] = '- Roles with at least one tracked module access permission: ' . $rolesWithAccess;
        $lines[] = '- Roles with zero tracked module access permissions: ' . $rolesWithoutAccess;
        $lines[] = '';
        $lines[] = '## Role Coverage';
        $lines[] = '';
        $lines[] = '| Role | Count | Granted module access permissions |';
        $lines[] = '|---|---:|---|';

        foreach ($rows as $row) {
            $grantedText = count($row['granted']) > 0
                ? implode(', ', $row['granted'])
                : '(none)';

            $lines[] = sprintf('| %s | %d | %s |', $row['name'], $row['count'], $grantedText);
        }

        $lines[] = '';
        $lines[] = '## Notes';
        $lines[] = '';
        $lines[] = '- This matrix reflects current DB state at generation time.';

        return implode(PHP_EOL, $lines) . PHP_EOL;
    }
}
