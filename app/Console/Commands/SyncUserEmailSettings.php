<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncUserEmailSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:user-email-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync SMTP email settings from config to all users if not already set';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $smtpSettings = [
            ['name' => 'FROM_EMAIL', 'value' => config('mail.from.address')],
            ['name' => 'FROM_NAME', 'value' => config('mail.from.name')],
            ['name' => 'SERVER_DRIVER', 'value' => config('mail.default')],
            ['name' => 'SERVER_HOST', 'value' => config('mail.mailers.smtp.host')],
            ['name' => 'SERVER_PORT', 'value' => config('mail.mailers.smtp.port')],
            ['name' => 'SERVER_USERNAME', 'value' => config('mail.mailers.smtp.username')],
            ['name' => 'SERVER_PASSWORD', 'value' => config('mail.mailers.smtp.password')],
            ['name' => 'SERVER_ENCRYPTION', 'value' => config('mail.mailers.smtp.encryption')],
        ];

        $users = \App\Models\User::all();
        foreach ($users as $user) {
            foreach ($smtpSettings as $setting) {
                $exists = \DB::table('settings')
                    ->where('name', $setting['name'])
                    ->where('type', 'smtp')
                    ->where('parent_id', $user->id)
                    ->exists();
                if (!$exists) {
                    \DB::table('settings')->insert([
                        'name' => $setting['name'],
                        'type' => 'smtp',
                        'parent_id' => $user->id,
                        'value' => $setting['value'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
        $this->info('SMTP settings synced for all users.');
        return Command::SUCCESS;
    }
}
