<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChargesController extends Controller
{
    /**
     * Display the dedicated Service Charges and Tariffs management portal.
     */
    public function index(Request $request): View
    {
        $this->ensureAuthorized('settings.manage');

        $activeServiceSlug = $request->query('service');
        $activeService = $activeServiceSlug ? Service::where('slug', $activeServiceSlug)->first() : null;

        $categories = ServiceCategory::with(['services' => function ($q) {
            $q->orderBy('sort_order');
        }])->orderBy('sort_order')->get();

        $allServices = Service::with('category')->orderBy('sort_order')->get();

        return view('admin.charges.index', compact('categories', 'allServices', 'activeService', 'activeServiceSlug'));
    }

    /**
     * Update pricing, gateway drivers, and active status for catalog services.
     */
    public function update(Request $request): RedirectResponse
    {
        $this->ensureAuthorized('settings.manage');

        $request->validate([
            'services' => ['required', 'array'],
            'services.*.price' => ['required', 'numeric', 'min:0'],
            'services.*.action' => ['nullable', 'string'],
            'services.*.is_active' => ['nullable'],
            'services.*.provider_driver' => ['nullable', 'string', 'max:50'],
        ]);

        $submittedServices = $request->input('services', []);

        foreach ($submittedServices as $id => $data) {
            $service = Service::find($id);
            if ($service) {
                if (isset($data['action'])) {
                    $isActive = in_array(strtolower((string) $data['action']), ['enable', 'enabled', '1', 'true'], true);
                } else {
                    $isActive = isset($data['is_active']) && ($data['is_active'] === '1' || $data['is_active'] === true || $data['is_active'] === 'enable');
                }

                $service->update([
                    'price' => $data['price'],
                    'provider_driver' => (isset($data['provider_driver']) && ! empty($data['provider_driver'])) ? $data['provider_driver'] : $service->provider_driver,
                    'is_active' => $isActive,
                ]);
            }
        }

        if ($request->is('*settings*')) {
            return redirect()->route('admin.settings.index', ['tab' => 'pricing'])
                ->with('status', 'Service pricing and catalog parameters updated successfully.');
        }

        $redirectUrl = route('admin.charges.index');
        if ($request->filled('service')) {
            $redirectUrl = route('admin.charges.index', ['service' => $request->input('service')]);
        }

        return redirect($redirectUrl)->with('status', 'Service charges and pricing parameters updated successfully.');
    }

    /**
     * Guard helper ensuring the acting user has permission or is Super Admin.
     */
    protected function ensureAuthorized(string $permission): void
    {
        $user = auth()->user();
        if (! $user->hasRole('super_admin') && ! $user->hasPermission($permission)) {
            abort(403, 'Unauthorized access to service charges management.');
        }
    }
}
