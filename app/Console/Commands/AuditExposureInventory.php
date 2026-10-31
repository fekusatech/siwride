<?php

namespace App\Console\Commands;

use App\Models\Partner;
use App\Models\Setting;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('audit:exposure-inventory {--cleanup-sql : Print a proposed legacy log cleanup query without executing it}')]
#[Description('List setting key names and legacy public identity paths without revealing values or changing data')]
class AuditExposureInventory extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Setting key names (values omitted):');
        foreach (Setting::query()->orderBy('setting_key')->pluck('setting_key') as $key) {
            $this->line($key);
        }
        $this->info('Legacy public partner identity files (not deleted):');
        $paths = collect(Storage::disk('public')->allFiles('partners/nik'))
            ->merge(Partner::query()->whereNotNull('nik_image')->pluck('nik_image'))
            ->unique()->filter(fn (string $path): bool => Storage::disk('public')->exists($path));
        foreach ($paths as $path) {
            $this->line($path);
        }
        if ($this->option('cleanup-sql')) {
            $this->warn('REVIEW ONLY. This command does not execute the query. Replace the cutoff with the deployment timestamp.');
            $this->line("UPDATE api_logs SET request_headers = NULL, request_body = NULL, response_body = NULL WHERE created_at < 'REPLACE_WITH_DEPLOYMENT_TIMESTAMP';");
        }

        return self::SUCCESS;
    }
}
