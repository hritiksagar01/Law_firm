<?php

namespace App\Console\Commands;

use App\Services\TestManagementSystem;
use Illuminate\Console\Command;

class RunSjmTestsCommand extends Command
{
    protected $signature = 'sjm:test';

    protected $description = 'Execute all 23 automated test suites for SJM Law Firm Management Platform';

    public function handle(TestManagementSystem $tester): int
    {
        $this->info('================================================================');
        $this->info('  SJM LAW FIRM PLATFORM — AUTOMATED SYSTEM VERIFICATION AUDIT   ');
        $this->info('================================================================');
        $this->line('Running 23 test suites against active database and storage...');

        $results = $tester->runAll();

        $this->newLine();

        foreach ($results['suites'] as $suite) {
            $badge = $suite['status'] === 'PASSED' ? '<info>[ PASS ]</info>' : '<error>[ FAIL ]</error>';
            $this->line("{$badge} {$suite['title']} ({$suite['passed']}/{$suite['total']}) — {$suite['duration_ms']}ms");

            foreach ($suite['checks'] as $check) {
                $icon = $check['passed'] ? '  ✔' : '  ✖';
                $style = $check['passed'] ? 'comment' : 'error';
                $this->line("<{$style}>{$icon} {$check['name']}</{$style}>");
                $this->line("    └─ {$check['details']}");
            }
            $this->newLine();
        }

        $this->info('================================================================');
        $this->info("RESULTS: {$results['passed']}/{$results['total_tests']} tests passed ({$results['success_rate']}%) in {$results['duration_ms']}ms");
        $this->info("FINAL AUDIT STATUS: [ {$results['status']} ]");
        $this->info('================================================================');

        return $results['failed'] === 0 ? 0 : 1;
    }
}
