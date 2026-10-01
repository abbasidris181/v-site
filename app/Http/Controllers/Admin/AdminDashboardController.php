<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Display the reconstructed administrative backend dashboard with daily operational metrics.
     */
    public function index(Request $request): View
    {
        $today = Carbon::today();

        // 1. Top Row: KPIs
        // A. Total Users Balance
        $totalUserBalance = (float) Wallet::sum('balance');

        // B. Today's Total Deposit
        $todayDeposits = (float) WalletTransaction::where('category', 'deposit')
            ->whereDate('created_at', $today)
            ->sum('amount');

        // B. Today's Total Users registered & all-time user count
        $todayUsers = User::whereDate('created_at', $today)->count();
        $totalUsers = User::count();

        // C. Today's NIN Verifications (automated API inquiries)
        $todayNinVerifications = ServiceRequest::whereHas('service', function ($q) {
            $q->whereIn('slug', ['nin-verification', 'nin-verification-2', 'nin-verification-3']);
        })->whereDate('created_at', $today)->count();

        // D. Today's BVN Verifications (automated API inquiries)
        $todayBvnVerifications = ServiceRequest::whereHas('service', function ($q) {
            $q->where('slug', 'bvn-verification');
        })->whereDate('created_at', $today)->count();

        // 2. Bottom Row Left: Financial Overview (Deposit vs Expense Today)
        $todayExpenses = (float) WalletTransaction::whereIn('category', ['service_charge', 'debit'])
            ->whereDate('created_at', $today)
            ->sum('amount');

        $financialTotal = $todayDeposits + $todayExpenses;
        $depositPercentage = $financialTotal > 0 ? (int) round(($todayDeposits / $financialTotal) * 100) : 50;
        $expensePercentage = $financialTotal > 0 ? (100 - $depositPercentage) : 50;

        // 3. Bottom Row Right: Manual Services — Today Volume
        $manualServicesConfig = [
            [
                'code' => 'IPE',
                'name' => 'IPE Clearing',
                'slug' => 'ipe-clearing',
                'bar_color' => 'bg-indigo-600 dark:bg-indigo-500',
                'text_color' => 'text-indigo-600 dark:text-indigo-400',
                'pill_bg' => 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300',
            ],
            [
                'code' => 'NIN',
                'name' => 'NIN Validation',
                'slug' => 'nin-validation',
                'bar_color' => 'bg-blue-600 dark:bg-blue-500',
                'text_color' => 'text-blue-600 dark:text-blue-400',
                'pill_bg' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300',
            ],
            [
                'code' => 'MOD',
                'name' => 'Modification IPE',
                'slug' => 'modification-ipe',
                'bar_color' => 'bg-violet-600 dark:bg-violet-500',
                'text_color' => 'text-violet-600 dark:text-violet-400',
                'pill_bg' => 'bg-violet-50 dark:bg-violet-500/10 text-violet-700 dark:text-violet-300',
            ],
            [
                'code' => 'BVN',
                'name' => 'BVN Retrieval',
                'slug' => 'bvn-retrieval',
                'bar_color' => 'bg-amber-500 dark:bg-amber-400',
                'text_color' => 'text-amber-600 dark:text-amber-400',
                'pill_bg' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300',
            ],
            [
                'code' => 'DELINK',
                'name' => 'Self-Service Delinking',
                'slug' => 'self-service-delinking',
                'bar_color' => 'bg-emerald-600 dark:bg-emerald-500',
                'text_color' => 'text-emerald-600 dark:text-emerald-400',
                'pill_bg' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400',
            ],
            [
                'code' => 'PERS',
                'name' => 'Personalization',
                'slug' => 'personalization',
                'bar_color' => 'bg-teal-600 dark:bg-teal-500',
                'text_color' => 'text-teal-600 dark:text-teal-400',
                'pill_bg' => 'bg-teal-50 dark:bg-teal-500/10 text-teal-700 dark:text-teal-300',
            ],
        ];

        $manualServicesChart = [];
        $totalManualToday = 0;

        foreach ($manualServicesConfig as $cfg) {
            $service = Service::where('slug', $cfg['slug'])->first();
            $countToday = $service ? ServiceRequest::where('service_id', $service->id)->whereDate('created_at', $today)->count() : 0;
            $totalManualToday += $countToday;

            $manualServicesChart[] = array_merge($cfg, [
                'count' => $countToday,
                'service_id' => $service?->id,
            ]);
        }

        $maxCount = max(1, ...array_column($manualServicesChart, 'count'));
        foreach ($manualServicesChart as &$item) {
            $item['height_percent'] = $item['count'] > 0
                ? max(22, min(100, (int) round(($item['count'] / $maxCount) * 100)))
                : 12;
        }
        unset($item);

        return view('admin.dashboard', compact(
            'totalUserBalance',
            'todayDeposits',
            'todayUsers',
            'totalUsers',
            'todayNinVerifications',
            'todayBvnVerifications',
            'todayExpenses',
            'depositPercentage',
            'expensePercentage',
            'manualServicesChart',
            'totalManualToday'
        ));
    }
}
