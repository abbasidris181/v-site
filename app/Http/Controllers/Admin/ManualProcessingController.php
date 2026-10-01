<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Services\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManualProcessingController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * Display the dedicated manual processing queue for a specific subservice.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status', 'all');
        $serviceId = $request->query('service_id');
        $serviceSlug = $request->query('service_slug');

        $manualServices = Service::where('type', 'manual')
            ->where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->get();

        // If no service specified, default directly to the first manual service (destroy the generic all-services page)
        if (! $serviceId && ! $serviceSlug) {
            $singleService = $manualServices->first();
            $serviceId = $singleService?->id;
            $serviceSlug = $singleService?->slug;
        } else {
            $singleService = $serviceId 
                ? $manualServices->firstWhere('id', $serviceId) 
                : ($serviceSlug ? $manualServices->firstWhere('slug', $serviceSlug) : null);
            if ($singleService) {
                $serviceId = $singleService->id;
                $serviceSlug = $singleService->slug;
            } else {
                $singleService = $manualServices->first();
                $serviceId = $singleService?->id;
                $serviceSlug = $singleService?->slug;
            }
        }

        $search = $request->query('search');

        $query = ServiceRequest::with(['user', 'service', 'assignedStaff', 'processedBy'])
            ->where('service_id', $serviceId)
            ->latest();

        if ($status && in_array($status, ['pending', 'processing', 'completed', 'failed'])) {
            $query->where('status', $status);
        }

        if ($search) {
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

        $requests = $query->paginate(15)->withQueryString();

        // Metric counts for the active single subservice
        $counts = [
            'all' => ServiceRequest::where('service_id', $serviceId)->count(),
            'pending' => ServiceRequest::where('service_id', $serviceId)->where('status', 'pending')->count(),
            'processing' => ServiceRequest::where('service_id', $serviceId)->where('status', 'processing')->count(),
            'completed' => ServiceRequest::where('service_id', $serviceId)->where('status', 'completed')->count(),
            'failed' => ServiceRequest::where('service_id', $serviceId)->where('status', 'failed')->count(),
        ];

        return view('admin.queue.index', compact(
            'requests',
            'counts',
            'status',
            'search',
            'manualServices',
            'singleService',
            'serviceId',
            'serviceSlug'
        ));
    }


    /**
     * View detailed review of a specific manual service request.
     */
    public function show(ServiceRequest $serviceRequest): View
    {
        $serviceRequest->load(['user', 'service', 'assignedStaff', 'processedBy', 'refundedBy', 'batch']);

        return view('admin.queue.show', compact('serviceRequest'));
    }

    /**
     * Pick / Claim a pending manual request.
     * Transitions from pending -> processing.
     */
    public function pick(ServiceRequest $serviceRequest): RedirectResponse
    {
        if ($serviceRequest->status !== 'pending') {
            return back()->with('error', 'This request is not in pending status.');
        }

        $serviceRequest->update([
            'status' => 'processing',
            'assigned_to' => auth()->id(),
        ]);

        return back()->with('success', "Job #{$serviceRequest->reference} is now in processing.");
    }

    /**
     * Complete a manual request with resolution payload.
     * Transitions from processing -> completed.
     */
    public function complete(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        if (! in_array($serviceRequest->status, ['pending', 'processing', 'completed'])) {
            return back()->with('error', 'Only pending, processing, or completed jobs can be updated.');
        }

        $validated = $request->validate([
            'resolution_reference' => 'nullable|string|max:191',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $serviceRequest->update([
            'status' => 'completed',
            'processed_by' => auth()->id(),
            'completed_at' => $serviceRequest->completed_at ?? now(),
            'admin_notes' => $validated['admin_notes'] ?? null,
            'result_payload' => [
                'resolution_reference' => $validated['resolution_reference'] ?? ($validated['admin_notes'] ?? ('CLR_' . strtoupper(uniqid()))),
                'completed_by' => auth()->user()->full_name,
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        return back()->with('success', "Job #{$serviceRequest->reference} response saved successfully.");
    }

    /**
     * Fail a manual request with a mandatory rejection reason.
     * NOTE: Does NOT trigger an automatic refund!
     */
    public function fail(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        if (! in_array($serviceRequest->status, ['pending', 'processing'])) {
            return back()->with('error', 'Only pending or in-processing jobs can be marked as failed.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|min:5|max:1000',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $serviceRequest->update([
            'status' => 'failed',
            'processed_by' => auth()->id(),
            'rejection_reason' => $validated['rejection_reason'],
            'admin_notes' => $validated['admin_notes'] ?? null,
        ]);

        return back()->with('success', "Job #{$serviceRequest->reference} has been marked as Failed. Customer was NOT automatically refunded. Use the 'Issue Refund' button if a refund is authorized.");
    }

    /**
     * Manually issue a refund for a request.
     */
    public function refund(Request $request, ServiceRequest $serviceRequest): RedirectResponse
    {
        if ($serviceRequest->isRefunded()) {
            return back()->with('error', 'This service request has already been refunded.');
        }

        // If not failed, transition to failed before issuing refund
        if ($serviceRequest->status !== 'failed') {
            $serviceRequest->update([
                'status' => 'failed',
                'rejection_reason' => 'Refunded by Administrator',
                'processed_by' => auth()->id(),
            ]);
        }

        // Perform refund via WalletService under pessimistic row lock
        $this->walletService->refund(
            user: $serviceRequest->user,
            amount: (float) $serviceRequest->amount_charged,
            description: "Refund for Failed Service: {$serviceRequest->service->name} (#{$serviceRequest->reference})",
            idempotencyKey: "REFUND_SR_{$serviceRequest->id}",
            metadata: [
                'service_request_id' => $serviceRequest->id,
                'service_reference' => $serviceRequest->reference,
                'authorized_by_id' => auth()->id(),
                'authorized_by_name' => auth()->user()->full_name,
            ]
        );

        $serviceRequest->update([
            'refunded_at' => now(),
            'refunded_by' => auth()->id(),
        ]);

        return back()->with('success', "Refund of {$serviceRequest->formatted_amount} successfully credited to {$serviceRequest->user->full_name}'s wallet.");
    }
}
