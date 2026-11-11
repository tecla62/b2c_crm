<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('*', function ($view) {
            try {
                $s = Setting::allCached();
                $view->with('globalSettings', $s);

                // Convenience shortcuts used across all views
                $view->with('appName',    $s['company_name']    ?? 'WaterProof CRM');
                $view->with('appTagline', $s['company_tagline'] ?? 'Enterprise Management');
                $view->with('appLogo',    !empty($s['company_logo'])
                    ? ((file_exists(public_path('storage/' . $s['company_logo'])) || file_exists(storage_path('app/public/' . $s['company_logo'])))
                        ? asset('storage/' . $s['company_logo']) : '')
                    : '');
                $view->with('appFavicon', !empty($s['company_favicon'])
                    ? ((file_exists(public_path('storage/' . $s['company_favicon'])) || file_exists(storage_path('app/public/' . $s['company_favicon'])))
                        ? asset('storage/' . $s['company_favicon']) : '')
                    : '');
                $view->with('themeColor',   $s['theme_color']   ?? '#4f46e5');
                $view->with('sidebarColor', $s['sidebar_color'] ?? '#0f172a');
                $view->with('headerColor',  $s['header_color']  ?? '#ffffff');
                $view->with('dateFormat',   $s['date_format']   ?? 'd/m/Y');
            } catch (\Throwable) {
                $view->with([
                    'globalSettings' => [],
                    'appName'        => 'WaterProof CRM',
                    'appTagline'     => 'Enterprise Management',
                    'appLogo'        => '',
                    'appFavicon'     => '',
                    'themeColor'     => '#4f46e5',
                    'sidebarColor'   => '#0f172a',
                    'headerColor'    => '#ffffff',
                    'dateFormat'     => 'd/m/Y',
                ]);
            }
        });
    }
}
