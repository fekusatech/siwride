<?php

namespace App\Console\Commands;

use App\Services\WhatsAppService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('wa:test')]
#[Description('Test WhatsApp API Connection')]
class WhatsAppTestCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(WhatsAppService $waService)
    {
        $this->info('Starting WhatsApp API Connectivity Test...');
        $this->comment('API URL: ' . config('services.whatsapp.api_url'));
        $this->comment('Group ID: ' . config('services.whatsapp.group_id'));

        $this->info('1. Sending TEST GROUP message...');
        $response = $waService->sendGroupMessage('🤖 *Aplikasi Online* - Test koneksi sistem ke WhatsApp berhasil!');

        if ($response && $response->successful()) {
            $this->info('✅ Group Message Sent Successfully!');
        } else {
            $this->error('❌ Group Message Failed!');
            if ($response) {
                $this->error('Status: ' . $response->status());
                $this->error('Body: ' . $response->body());
            } else {
                $this->error('Error: No response from API (Double check URL/Connection)');
            }
        }

        $this->info("\nCheck your WhatsApp Group to see if the message arrived.");
    }
}
