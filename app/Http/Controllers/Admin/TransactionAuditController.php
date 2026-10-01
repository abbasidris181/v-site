<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionAuditController extends Controller
{
    /**
     * Audit log of all automated NIN verification requests.
     */
    public function ninVerifications(Request $request): View
    {
        $service = Service::where('slug', 'nin-verification')->first();
        $query = ServiceRequest::with('user')
            ->where(function ($q) use ($service) {
                if ($service) {
                    $q->where('service_id', $service->id);
                } else {
                    $q->where('tracking_input', 'like', '%');
                }
            })
            ->latest();

        if ($status = $request->query('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('tracking_input', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $records = $query->paginate(20)->withQueryString();

        $serviceId = $service?->id;
        $totalCount = ServiceRequest::where('service_id', $serviceId)->count();
        $completedCount = ServiceRequest::where('service_id', $serviceId)->where('status', 'completed')->count();
        $failedCount = ServiceRequest::where('service_id', $serviceId)->where('status', 'failed')->count();
        $totalVolume = (float) ServiceRequest::where('service_id', $serviceId)->sum('amount_charged');

        return view('admin.transactions.nin', compact(
            'records',
            'service',
            'totalCount',
            'completedCount',
            'failedCount',
            'totalVolume'
        ));
    }

    /**
     * Audit log of all automated BVN verification requests.
     */
    public function bvnVerifications(Request $request): View
    {
        $service = Service::where('slug', 'bvn-verification')->first();
        $query = ServiceRequest::with('user')
            ->where(function ($q) use ($service) {
                if ($service) {
                    $q->where('service_id', $service->id);
                }
            })
            ->latest();

        if ($status = $request->query('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('tracking_input', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $records = $query->paginate(20)->withQueryString();

        $serviceId = $service?->id;
        $totalCount = ServiceRequest::where('service_id', $serviceId)->count();
        $completedCount = ServiceRequest::where('service_id', $serviceId)->where('status', 'completed')->count();
        $failedCount = ServiceRequest::where('service_id', $serviceId)->where('status', 'failed')->count();
        $totalVolume = (float) ServiceRequest::where('service_id', $serviceId)->sum('amount_charged');

        return view('admin.transactions.bvn', compact(
            'records',
            'service',
            'totalCount',
            'completedCount',
            'failedCount',
            'totalVolume'
        ));
    }

    /**
     * Audit log of all wallet deposit top-ups.
     */
    public function depositHistory(Request $request): View
    {
        $query = WalletTransaction::with('user')
            ->where('category', 'deposit')
            ->latest();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $deposits = $query->paginate(20)->withQueryString();

        $totalDepositsCount = WalletTransaction::where('category', 'deposit')->count();
        $totalDepositsAmount = (float) WalletTransaction::where('category', 'deposit')->sum('amount');

        return view('admin.transactions.deposits', compact(
            'deposits',
            'totalDepositsCount',
            'totalDepositsAmount'
        ));
    }

    /**
     * Audit log of all wallet-to-wallet transfers.
     */
    public function transfers(Request $request): View
    {
        // Using transfer_out ensures 1 row per unique transfer event with sender and recipient
        $query = WalletTransaction::with(['user', 'counterpartUser'])
            ->where('category', 'transfer_out')
            ->latest();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('counterpartUser', function ($cq) use ($search) {
                      $cq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('surname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $transfers = $query->paginate(20)->withQueryString();

        $totalTransfersCount = WalletTransaction::where('category', 'transfer_out')->count();
        $totalTransfersVolume = (float) WalletTransaction::where('category', 'transfer_out')->sum('amount');

        return view('admin.transactions.transfers', compact(
            'transfers',
            'totalTransfersCount',
            'totalTransfersVolume'
        ));
    }
}
