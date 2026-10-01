<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\ServiceRequest;
use App\Services\ServiceCatalogService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ServiceCatalogService $catalogService,
        protected WalletService $walletService
    ) {}

    /**
     * Display the user dashboard with dynamic greeting, wallet balance,
     * announcements, dynamic service grid cards, and recent history.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        $categories = $this->catalogService->getActiveCatalog();
        $announcements = Announcement::where('is_active', true)->latest()->get();

        $recentRequests = ServiceRequest::where('user_id', $user->id)
            ->with('service')
            ->latest()
            ->take(5)
            ->get();

        $virtualAccounts = $user->getMonnifyVirtualAccounts();

        return view('dashboard', [
            'user' => $user,
            'wallet' => $wallet,
            'categories' => $categories,
            'announcements' => $announcements,
            'recentRequests' => $recentRequests,
            'virtualAccounts' => $virtualAccounts,
        ]);
    }
}
