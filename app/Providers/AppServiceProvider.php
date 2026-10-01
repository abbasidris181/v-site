<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set default string length for MySQL / cPanel index compatibility (utf8mb4)
        Schema::defaultStringLength(191);

        // Blade conditional for admin backend access (Super Admin, Admin, Staff)
        Blade::if('hasAdminAccess', function () {
            return auth()->check() && auth()->user()->hasAdminBackendAccess();
        });

        // Blade conditional for specific roles
        Blade::if('role', function ($roles) {
            return auth()->check() && auth()->user()->hasRole($roles);
        });

        // Global Typography, Logo & Branding Configuration Composer
        view()->composer('*', function ($view) {
            try {
                if (Schema::hasTable('settings')) {
                    $fontFamily = \App\Models\Setting::get('font_family', "'Roboto', Arial, sans-serif");
                    $siteLogo = \App\Models\Setting::get('site_logo', '');
                    $siteName = \App\Models\Setting::get('site_name', 'V-SITE');
                    $siteTagline = \App\Models\Setting::get('site_tagline', 'Enterprise Nigerian Identity Verification Infrastructure');
                    $contactEmail = \App\Models\Setting::get('contact_email', 'support@vsite.ng');
                    $contactPhone = \App\Models\Setting::get('contact_phone', '+234 800 000 0000');
                    $contactWhatsapp = \App\Models\Setting::get('contact_whatsapp', '+234 812 345 6789');
                } else {
                    $fontFamily = "'Roboto', Arial, sans-serif";
                    $siteLogo = '';
                    $siteName = 'V-SITE';
                    $siteTagline = 'Enterprise Nigerian Identity Verification Infrastructure';
                    $contactEmail = 'support@vsite.ng';
                    $contactPhone = '+234 800 000 0000';
                    $contactWhatsapp = '+234 812 345 6789';
                }
            } catch (\Throwable $e) {
                $fontFamily = "'Roboto', Arial, sans-serif";
                $siteLogo = '';
                $siteName = 'V-SITE';
                $siteTagline = 'Enterprise Nigerian Identity Verification Infrastructure';
                $contactEmail = 'support@vsite.ng';
                $contactPhone = '+234 800 000 0000';
                $contactWhatsapp = '+234 812 345 6789';
            }

            // Derive Google Fonts URL if primary font is not standard system web-safe font
            $primaryFont = trim(explode(',', $fontFamily)[0]);
            $cleanFontName = trim($primaryFont, " '\"");
            $webSafeFonts = [
                'arial', 'helvetica', 'times new roman', 'times', 'courier new', 'courier',
                'verdana', 'georgia', 'tahoma', 'trebuchet ms', 'impact', 'sans-serif', 'serif', 'monospace'
            ];

            $googleFontUrl = null;
            if (! in_array(strtolower($cleanFontName), $webSafeFonts)) {
                if (strtolower($cleanFontName) === 'roboto') {
                    $googleFontUrl = 'https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;0,900;1,400;1,700&display=swap';
                } else {
                    $googleFontUrl = 'https://fonts.googleapis.com/css2?family=' . urlencode($cleanFontName) . ':wght@300;400;500;600;700;800;900&display=swap';
                }
            }

            $siteLogoUrl = ! empty($siteLogo) ? asset('storage/' . $siteLogo) : null;

            $view->with('siteFontFamily', $fontFamily);
            $view->with('siteFontGoogleUrl', $googleFontUrl);
            $view->with('siteLogo', $siteLogo);
            $view->with('siteLogoUrl', $siteLogoUrl);
            $view->with('siteName', $siteName);
            $view->with('siteTagline', $siteTagline);
            $view->with('contactEmail', $contactEmail);
            $view->with('contactPhone', $contactPhone);
            $view->with('contactWhatsapp', $contactWhatsapp);
        });

        // Active Services Composer for User Portal Sidebar Navigation
        view()->composer('layouts.app', function ($view) {
            try {
                $sidebarServices = Schema::hasTable('services')
                    ? \App\Models\Service::where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()
                    : collect();
            } catch (\Throwable $e) {
                $sidebarServices = collect();
            }

            $view->with('sidebarServices', $sidebarServices);
        });
    }
}
