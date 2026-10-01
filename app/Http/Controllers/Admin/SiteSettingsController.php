<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SiteSettingsController extends Controller
{
    /**
     * Display the Site Settings management portal (Pricing and Platform Configurations).
     */
    public function index(): View
    {
        $this->ensureAuthorized('settings.manage');

        $settings = Setting::getAllMapped();

        // Standard default configuration fallbacks
        $defaults = [
            'site_name' => 'V-SITE',
            'site_tagline' => 'Enterprise Nigerian Identity Verification Infrastructure',
            'contact_email' => 'support@vsite.ng',
            'contact_phone' => '+234 800 000 0000',
            'contact_whatsapp' => '+234 812 345 6789',
            'maintenance_mode' => '0',
            'min_wallet_deposit' => '500.00',
            'font_family' => 'Roboto, Arial, sans-serif',
            'site_logo' => '',
            'monnify_enabled' => '1',
            'monnify_environment' => 'SANDBOX',
            'monnify_api_key' => '',
            'monnify_secret_key' => '',
            'monnify_contract_code' => '',
        ];

        foreach ($defaults as $key => $defaultVal) {
            if (! isset($settings[$key])) {
                $settings[$key] = $defaultVal;
            }
        }

        return view('admin.settings.index', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update pricing, gateway drivers, and active status for catalog services.
     */
    public function updatePricing(Request $request): RedirectResponse
    {
        $this->ensureAuthorized('settings.manage');

        $request->validate([
            'services' => ['required', 'array'],
            'services.*.price' => ['required', 'numeric', 'min:0'],
            'services.*.provider_driver' => ['nullable', 'string', 'max:50'],
        ]);

        $submittedServices = $request->input('services', []);

        foreach ($submittedServices as $id => $data) {
            $service = Service::find($id);
            if ($service) {
                $service->update([
                    'price' => $data['price'],
                    'provider_driver' => ! empty($data['provider_driver']) ? $data['provider_driver'] : $service->provider_driver,
                    'is_active' => isset($data['is_active']) && ($data['is_active'] === '1' || $data['is_active'] === true),
                ]);
            }
        }

        return redirect()->route('admin.settings.index', ['tab' => 'pricing'])
            ->with('status', 'Service pricing and catalog parameters updated successfully.');
    }

    /**
     * Update global platform configurations (contact, WhatsApp, branding, maintenance, logo, Monnify gateway).
     */
    public function updateConfigurations(Request $request): RedirectResponse
    {
        $this->ensureAuthorized('settings.manage');

        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:100'],
            'site_tagline' => ['nullable', 'string', 'max:200'],
            'contact_email' => ['required', 'email', 'max:120'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'contact_whatsapp' => ['nullable', 'string', 'max:30'],
            'min_wallet_deposit' => ['required', 'numeric', 'min:50'],
            'maintenance_mode' => ['nullable', 'boolean'],
            'font_family' => ['nullable', 'string', 'max:100'],
            'site_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            // Monnify Gateway Constants
            'monnify_enabled' => ['nullable', 'boolean'],
            'monnify_environment' => ['nullable', 'string', 'in:SANDBOX,LIVE'],
            'monnify_api_key' => ['nullable', 'string', 'max:255'],
            'monnify_secret_key' => ['nullable', 'string', 'max:255'],
            'monnify_contract_code' => ['nullable', 'string', 'max:100'],
        ]);

        Setting::set('site_name', $validated['site_name'], 'general');
        Setting::set('site_tagline', $validated['site_tagline'] ?? '', 'general');
        Setting::set('contact_email', $validated['contact_email'], 'support');
        Setting::set('contact_phone', $validated['contact_phone'], 'support');
        Setting::set('contact_whatsapp', $validated['contact_whatsapp'] ?? '', 'support');
        Setting::set('min_wallet_deposit', (string) $validated['min_wallet_deposit'], 'wallet');
        Setting::set('maintenance_mode', $request->boolean('maintenance_mode') ? '1' : '0', 'operations');
        Setting::set('font_family', ! empty($validated['font_family']) ? $validated['font_family'] : 'Roboto, Arial, sans-serif', 'appearance');

        // Monnify Gateway Configurations
        Setting::set('monnify_enabled', $request->boolean('monnify_enabled') ? '1' : '0', 'monnify');
        Setting::set('monnify_environment', $request->input('monnify_environment', 'SANDBOX'), 'monnify');
        if ($request->has('monnify_api_key')) {
            Setting::set('monnify_api_key', trim((string) $request->input('monnify_api_key')), 'monnify');
        }
        if ($request->has('monnify_secret_key')) {
            Setting::set('monnify_secret_key', trim((string) $request->input('monnify_secret_key')), 'monnify');
        }
        if ($request->has('monnify_contract_code')) {
            Setting::set('monnify_contract_code', trim((string) $request->input('monnify_contract_code')), 'monnify');
        }

        if ($request->boolean('remove_logo')) {
            $existingLogo = Setting::get('site_logo');
            if ($existingLogo && Storage::disk('public')->exists($existingLogo)) {
                Storage::disk('public')->delete($existingLogo);
            }
            Setting::set('site_logo', '', 'branding');
        } elseif ($request->hasFile('site_logo')) {
            $existingLogo = Setting::get('site_logo');
            if ($existingLogo && Storage::disk('public')->exists($existingLogo)) {
                Storage::disk('public')->delete($existingLogo);
            }
            $logoPath = $request->file('site_logo')->store('logos', 'public');
            Setting::set('site_logo', $logoPath, 'branding');
        }

        return redirect()->route('admin.settings.index', ['tab' => 'configurations'])
            ->with('status', 'Site configurations saved successfully.');
    }

    /**
     * Test Monnify connection using configured credentials.
     */
    public function testMonnifyConnection(\App\Services\Payment\MonnifyService $monnifyService): \Illuminate\Http\JsonResponse
    {
        $this->ensureAuthorized('settings.manage');

        $result = $monnifyService->testConnection();

        return response()->json($result);
    }

    /**
     * Guard helper ensuring the acting user has permission or is Super Admin.
     */
    protected function ensureAuthorized(string $permission): void
    {
        $user = auth()->user();
        if (! $user->hasRole('super_admin') && ! $user->hasPermission($permission)) {
            abort(403, 'Unauthorized access to site settings.');
        }
    }
}
