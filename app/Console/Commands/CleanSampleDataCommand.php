<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CleanSampleDataCommand extends Command
{
    protected $signature = 'legal:clean-data {--force : Bypass confirmation prompt}';

    protected $description = 'Purge all sample/seeded matters, documents, clients, and transactional records while preserving Firms and Staff accounts.';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('WARNING: This will permanently purge all sample clients, matters, documents, invoices, appointments, and messages. Continue?')) {
            $this->info('Data purge aborted.');

            return 0;
        }

        $this->info('Initiating chambers clean slate operation...');

        DB::transaction(function () {
            // 1. Delete physical document files
            $disk = config('filesystems.default', 'local');
            $docs = Document::all();
            foreach ($docs as $doc) {
                if ($doc->file_path && Storage::disk($disk)->exists($doc->file_path)) {
                    Storage::disk($disk)->delete($doc->file_path);
                }
            }
            $this->info('Physical document storage sanitized.');

            // 2. Delete transactional records in foreign key order
            $tables = [
                'document_requests',
                'documents',
                'time_entries',
                'invoices',
                'events',
                'tasks',
                'messages',
                'opinions',
                'appointments',
                'expenses',
                'transactions',
                'matter_user',
                'matters',
            ];

            foreach ($tables as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    $count = DB::table($table)->count();
                    DB::table($table)->delete();
                    $this->line("  Cleaned {$count} records from {$table}.");
                }
            }

            // 3. Remove client users and client entities
            $clientCount = Client::count();
            Client::query()->delete();
            $this->line("  Cleaned {$clientCount} client roster profiles.");

            $clientUsersCount = User::where('role', 'client')->count();
            User::where('role', 'client')->delete();
            $this->line("  Cleaned {$clientUsersCount} client portal user logins.");
        });

        $this->newLine();
        $this->info('================================================================');
        $this->info('CHAMBERS CLEAN SLATE COMPLETE');
        $this->info('All sample clients, matters, documents, and financials purged.');
        $this->info('Firms, subscription plans, and advocate staff accounts preserved.');
        $this->info('Chambers is now 100% fresh and ready for live production use.');
        $this->info('================================================================');

        return 0;
    }
}
