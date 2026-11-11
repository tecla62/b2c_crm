<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.bootstrap-5');
        Paginator::defaultSimpleView('vendor.pagination.bootstrap-5');

        // Apply dynamic settings on every request
        try {
            $s = Setting::allCached();

            // Timezone
            if (!empty($s['timezone'])) {
                Config::set('app.timezone', $s['timezone']);
                date_default_timezone_set($s['timezone']);
            }

            // SMTP — apply live so mail works without .env changes
            if (!empty($s['mail_host'])) {
                Config::set('mail.mailers.smtp.host',       $s['mail_host']);
                Config::set('mail.mailers.smtp.port',       $s['mail_port']       ?? 587);
                Config::set('mail.mailers.smtp.username',   $s['mail_username']   ?? '');
                Config::set('mail.mailers.smtp.password',   $s['mail_password']   ?? '');
                Config::set('mail.mailers.smtp.encryption', $s['mail_encryption'] ?? 'tls');
            }
            if (!empty($s['mail_from_address'])) {
                Config::set('mail.from.address', $s['mail_from_address']);
                Config::set('mail.from.name',    $s['mail_from_name'] ?? config('app.name'));
            }
        } catch (\Throwable) {
            // DB not ready yet (migrations) — skip silently
        }
    }
}
