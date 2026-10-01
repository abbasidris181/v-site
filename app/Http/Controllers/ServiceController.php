<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientWalletBalanceException;
use App\Models\ServiceBatch;
use App\Models\ServiceRequest;
use App\Services\ApiServiceOrchestrator;
use App\Services\BulkSubmissionService;
use App\Services\ServiceCatalogService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use InvalidArgumentException;
use RuntimeException;

class ServiceController extends Controller
{
    public function __construct(
        protected ServiceCatalogService $catalogService,
        protected WalletService $walletService,
        protected BulkSubmissionService $bulkService,
        protected ApiServiceOrchestrator $apiOrchestrator
    ) {}

    /**
     * Display the uniform service submission page with input requirements
     * and the logged-in user's previous submissions history for this service.
     */
    public function show(string $slug, Request $request): View
    {
        $service = $this->catalogService->findBySlug($slug);

        if (! $service) {
            abort(404, 'The requested identity service does not exist or is currently inactive.');
        }

        $user = $request->user();
        $wallet = $this->walletService->getOrCreateWallet($user);

        // Fetch user's history for this specific service (SRS Section 23)
        $requests = ServiceRequest::where('user_id', $user->id)
            ->where('service_id', $service->id)
            ->with(['batch', 'service'])
            ->latest()
            ->paginate(10);

        $latestCompleted = ServiceRequest::where('user_id', $user->id)
            ->where('service_id', $service->id)
            ->where('status', 'completed')
            ->whereNotNull('result_payload')
            ->latest()
            ->first();

        return view('services.show', [
            'service' => $service,
            'user' => $user,
            'wallet' => $wallet,
            'requests' => $requests,
            'latestCompleted' => $latestCompleted,
        ]);
    }

    /**
     * Display printable/downloadable official verification slip (SRS Section 12, 15, 16).
     */
    public function slip(string $reference, Request $request): View
    {
        $serviceRequest = ServiceRequest::where('reference', $reference)
            ->with(['user', 'service'])
            ->firstOrFail();

        $user = $request->user();

        // Ownership Authorization: only owner or staff/admin with backend access
        if ($serviceRequest->user_id !== $user->id && ! $user->hasAdminBackendAccess()) {
            abort(403, 'You are not authorized to view or print this verification slip.');
        }

        if ($serviceRequest->status !== 'completed' || empty($serviceRequest->result_payload)) {
            abort(404, 'This verification record has no completed slip payload.');
        }

        $payload = $serviceRequest->result_payload['data'] ?? [];
        $isBvn = str_contains($serviceRequest->service->slug, 'bvn');

        $viewName = $isBvn ? 'services.slips.bvn-slip' : 'services.slips.nin-slip';

        return view($viewName, [
            'serviceRequest' => $serviceRequest,
            'data' => $payload,
            'user' => $user,
        ]);
    }

    /**
     * Process service request submission (API verifications or manual processing).
     */
    public function submit(string $slug, Request $request): RedirectResponse
    {
        $service = $this->catalogService->findBySlug($slug);

        if (! $service) {
            abort(404, 'Service not found.');
        }

        $user = $request->user();

        // 1. Synchronous Automated API Services (NIN & BVN Verification - SRS Section 12, 15, 16)
        if ($service->type === 'api') {
            if (in_array($service->slug, ['nin-verification', 'nin-verification-2', 'nin-verification-3'])) {
                $mode = $request->input('verification_mode', 'nin');
                $trackingInput = '';
                $extraPayload = ['mode' => $mode];
                $validationErrors = [];

                // Consent validation for NIN verification services
                $hasConsent = $request->boolean('consent') || $request->input('consent') === '1' || $request->input('consent') === 'on' || $request->input('check_consent') === '1';
                if (! $hasConsent) {
                    $validationErrors['consent'] = 'You must check consent confirming you have obtained the identity owner\'s permission to verify this record.';
                }

                if ($mode === 'phone') {
                    $rawPhone = trim((string) $request->input('phone_number', $request->input('tracking_input')));
                    if (empty($rawPhone)) {
                        $validationErrors['phone_number'] = 'Please provide a registered phone number.';
                    } else {
                        // Normalize phone number (handle +234, 234, 0...)
                        $clean = preg_replace('/[^\d\+]/', '', $rawPhone);
                        if (str_starts_with($clean, '+234')) {
                            $clean = '0' . ltrim(substr($clean, 4), '0');
                        } elseif (str_starts_with($clean, '234') && strlen($clean) >= 13) {
                            $clean = '0' . ltrim(substr($clean, 3), '0');
                        } elseif (strlen($clean) === 10 && ! str_starts_with($clean, '0')) {
                            $clean = '0' . $clean;
                        }

                        if (! preg_match('/^[0-9]{11}$/', $clean)) {
                            $validationErrors['phone_number'] = 'Please provide a valid 11-digit phone number (e.g. 08012345678 or +2348012345678).';
                            $validationErrors['tracking_input'] = 'Please provide a valid 11-digit phone number (e.g. 08012345678 or +2348012345678).';
                        } else {
                            $trackingInput = $clean;
                            $extraPayload['phone_number'] = $rawPhone;
                            $extraPayload['normalized_phone'] = $clean;
                        }
                    }
                } elseif ($mode === 'demographics') {
                    if (empty($request->input('first_name'))) {
                        $validationErrors['first_name'] = 'First name is required.';
                    }
                    if (empty($request->input('dob'))) {
                        $validationErrors['dob'] = 'Date of birth is required.';
                    }

                    $fn = trim((string) $request->input('first_name'));
                    $rawMn = trim((string) $request->input('middle_name'));
                    $isMaskedMn = empty($rawMn)
                        || preg_match('/^[\*\s—\-]+$/', $rawMn)
                        || in_array(strtolower($rawMn), ['null', 'nil', 'none', 'n/a', 'na'], true);
                    $mn = ! $isMaskedMn ? $rawMn : '';
                    $ln = trim((string) ($request->input('last_name') ?? $request->input('lastname') ?? ''));
                    $dob = trim((string) $request->input('dob'));
                    $gender = trim((string) $request->input('gender'));

                    $nameParts = array_filter([$fn, $mn, $ln]);
                    $trackingInput = !empty($nameParts) ? implode(' ', $nameParts) . " ({$dob})" : "{$fn} ({$dob})";
                    $extraPayload['first_name'] = $fn;
                    $extraPayload['middle_name'] = !empty($mn) ? $mn : null;
                    if ($ln !== '') {
                        $extraPayload['last_name'] = $ln;
                        $extraPayload['surname'] = $ln;
                        $extraPayload['lastname'] = $ln;
                    }
                    $extraPayload['dob'] = $dob;
                    if (!empty($gender)) {
                        $extraPayload['gender'] = ucfirst(strtolower($gender));
                    }
                } else {
                    // NIN mode (11-digit int) - strip any spaces or hyphens
                    $rawNin = preg_replace('/[^0-9]/', '', trim((string) $request->input('nin', $request->input('tracking_input'))));
                    if (! preg_match('/^[0-9]{11}$/', $rawNin)) {
                        $validationErrors['nin'] = 'Please provide a valid 11-digit National Identity Number (NIN).';
                        $validationErrors['tracking_input'] = 'Please provide a valid 11-digit National Identity Number (NIN).';
                    } else {
                        $trackingInput = $rawNin;
                        $extraPayload['nin'] = $rawNin;
                    }
                }

                if (! empty($validationErrors)) {
                    return back()->withErrors($validationErrors)->withInput();
                }

                $extraPayload['consent_obtained'] = true;

                try {
                    $completedRequest = $this->apiOrchestrator->execute(
                        user: $user,
                        service: $service,
                        trackingInput: $trackingInput,
                        extraPayload: $extraPayload
                    );

                    return redirect()->route('services.slip', $completedRequest->reference)
                        ->with('api_result', $completedRequest)
                        ->with('success', sprintf(
                            'Verification Successful! Identity record for %s retrieved. All response information and downloadable slips are ready below.',
                            $completedRequest->tracking_input
                        ));
                } catch (InsufficientWalletBalanceException $e) {
                    return back()->with('error', $e->getMessage())->withInput();
                } catch (InvalidArgumentException $e) {
                    return back()->withErrors(['tracking_input' => $e->getMessage()])->withInput();
                } catch (RuntimeException $e) {
                    return back()->with('error', $e->getMessage())->withInput();
                }
            }

            // Other API services (e.g. BVN Verification)
            if ($service->slug === 'bvn-verification') {
                $bvnErrors = [];

                $hasConsent = $request->boolean('consent') || $request->input('consent') === '1' || $request->input('consent') === 'on' || $request->input('check_consent') === '1';
                if (! $hasConsent) {
                    $bvnErrors['consent'] = 'You must check consent confirming you have obtained the identity owner\'s permission to verify this record.';
                }

                $rawBvn = trim((string) $request->input('tracking_input', $request->input('bvn', '')));
                if (empty($rawBvn)) {
                    $bvnErrors['tracking_input'] = 'Bank Verification Number is required.';
                } elseif (! preg_match('/^[0-9]{11}$/', $rawBvn)) {
                    $bvnErrors['tracking_input'] = 'Please provide a valid 11-digit Bank Verification Number (BVN).';
                }

                if (! empty($bvnErrors)) {
                    return back()->withErrors($bvnErrors)->withInput();
                }

                try {
                    $completedRequest = $this->apiOrchestrator->execute(
                        user: $user,
                        service: $service,
                        trackingInput: $rawBvn,
                        extraPayload: ['consent_obtained' => true]
                    );

                    return redirect()->route('services.slip', $completedRequest->reference)
                        ->with('api_result', $completedRequest)
                        ->with('success', sprintf(
                            'Verification Successful! Identity record for %s retrieved. All response information and downloadable slips are ready below.',
                            $completedRequest->tracking_input
                        ));
                } catch (InsufficientWalletBalanceException $e) {
                    return back()->with('error', $e->getMessage())->withInput();
                } catch (InvalidArgumentException $e) {
                    return back()->withErrors(['tracking_input' => $e->getMessage()])->withInput();
                } catch (RuntimeException $e) {
                    return back()->with('error', $e->getMessage())->withInput();
                }
            }

            $request->validate([
                'tracking_input' => ['required', 'string'],
            ]);

            try {
                $completedRequest = $this->apiOrchestrator->execute(
                    user: $user,
                    service: $service,
                    trackingInput: $request->input('tracking_input')
                );

                return back()->with('api_result', $completedRequest)
                    ->with('success', sprintf(
                        'Verification Successful! Identity record for %s retrieved. You can view or print the official slip.',
                        $completedRequest->tracking_input
                    ));
            } catch (InsufficientWalletBalanceException $e) {
                return back()->with('error', $e->getMessage())->withInput();
            } catch (InvalidArgumentException $e) {
                return back()->withErrors(['tracking_input' => $e->getMessage()])->withInput();
            } catch (RuntimeException $e) {
                return back()->with('error', $e->getMessage())->withInput();
            }
        }

        // 2. Bulk / Multi-line Submissions (SRS Section 14, 17, 18, 21)
        if ($service->is_bulk_allowed) {
            $request->validate([
                'tracking_input' => ['required', 'string'],
            ]);

            try {
                $result = $this->bulkService->processBulkSubmission(
                    user: $user,
                    service: $service,
                    rawInput: $request->input('tracking_input')
                );

                $acceptedCount = count($result['accepted_entries']);
                $insufficientCount = count($result['insufficient_balance_entries']);
                $invalidCount = count($result['invalid_entries']);

                if ($acceptedCount === 0) {
                    $message = $insufficientCount > 0
                        ? "Submission halted: Insufficient wallet balance to process any valid entries. Please fund your wallet."
                        : "Submission halted: All submitted entries failed formatting validation.";

                    return back()->with('bulk_result', $result)->with('error', $message)->withInput();
                }

                $statusMessage = sprintf(
                    'Partial Batch Processed: %d accepted (₦%s charged). %s',
                    $acceptedCount,
                    number_format($result['total_charged'], 2),
                    $insufficientCount > 0 ? "{$insufficientCount} entries rejected due to insufficient wallet balance." : ''
                );

                return back()->with('bulk_result', $result)->with('success', trim($statusMessage));
            } catch (InvalidArgumentException $e) {
                return back()->withErrors(['tracking_input' => $e->getMessage()])->withInput();
            }
        }

        // 2. Multi-field Custom Schema Submissions (e.g. BVN Retrieval - SRS Section 20)
        if ($service->fields_schema) {
            $rules = [];
            foreach ($service->fields_schema as $field) {
                $rules[$field['name']] = ($field['required'] ?? false) ? ['required', 'string', 'max:191'] : ['nullable', 'string', 'max:191'];
            }

            $validated = $request->validate($rules);

            try {
                $createdRequest = $this->bulkService->processSingleSubmission(
                    user: $user,
                    service: $service,
                    inputPayload: $validated
                );

                return back()->with('success', 'Request submitted successfully!');
            } catch (InsufficientWalletBalanceException $e) {
                return back()->with('error', $e->getMessage())->withInput();
            }
        }

        // 3. Single Input Manual Submissions (e.g. Self-Service Delinking - SRS Section 22)
        $request->validate([
            'tracking_input' => ['required', 'string'],
        ]);

        $entryValidation = $this->bulkService->normalizeAndValidateEntry($request->input('tracking_input'), $service);
        if (! $entryValidation['is_valid']) {
            return back()->withErrors(['tracking_input' => $entryValidation['error']])->withInput();
        }

        try {
            $createdRequest = $this->bulkService->processSingleSubmission(
                user: $user,
                service: $service,
                inputPayload: ['tracking_input' => $entryValidation['normalized']]
            );

            return back()->with('success', 'Request submitted successfully!');
        } catch (InsufficientWalletBalanceException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Retrieve interactive batch details with all constituent inputs and replies formatted with =.
     */
    public function batchDetails(string $identifier, Request $request): JsonResponse
    {
        $user = $request->user();

        // Locate ServiceBatch by reference or id
        $batch = ServiceBatch::where('batch_reference', $identifier)
            ->orWhere('id', $identifier)
            ->first();

        if (! $batch && is_numeric($identifier) && strlen((string) $identifier) === 8) {
            $batch = ServiceBatch::where(function ($q) use ($user) {
                    if (! $user->hasAdminBackendAccess()) {
                        $q->where('user_id', $user->id);
                    }
                })
                ->get()
                ->first(fn ($b) => $b->batch_number === (string) $identifier);
        }

        if ($batch) {
            if ($batch->user_id !== $user->id && ! $user->hasAdminBackendAccess()) {
                abort(403, 'Unauthorized to view this batch.');
            }

            $requests = ServiceRequest::where('batch_id', $batch->id)
                ->orderBy('id', 'asc')
                ->get();

            $batchNumber = $batch->batch_number;
            $serviceName = $batch->service?->name ?? 'Bulk Batch';
            $createdAt = $batch->created_at->format('M d, Y h:i A');
        } else {
            // Check if identifier maps to a ServiceRequest's batch_id, reference or generated batch_number
            $primaryRequest = ServiceRequest::where(function ($q) use ($identifier) {
                    $q->where('batch_id', $identifier)
                      ->orWhere('reference', $identifier);
                })
                ->where(function ($q) use ($user) {
                    if (! $user->hasAdminBackendAccess()) {
                        $q->where('user_id', $user->id);
                    }
                })
                ->first();

            if (! $primaryRequest && is_numeric($identifier) && strlen((string) $identifier) === 8) {
                $primaryRequest = ServiceRequest::where(function ($q) use ($user) {
                        if (! $user->hasAdminBackendAccess()) {
                            $q->where('user_id', $user->id);
                        }
                    })
                    ->get()
                    ->first(fn ($r) => $r->batch_number === (string) $identifier);
            }

            if (! $primaryRequest) {
                abort(404, 'Batch or request not found.');
            }

            $batchNumber = $primaryRequest->batch_number;
            $serviceName = $primaryRequest->service?->name ?? 'Service';
            $createdAt = $primaryRequest->created_at->format('M d, Y h:i A');

            $requests = $primaryRequest->batch_id 
                ? ServiceRequest::where('batch_id', $primaryRequest->batch_id)->orderBy('id', 'asc')->get()
                : collect([$primaryRequest]);
        }

        $items = $requests->map(function (ServiceRequest $req) {
            $input = $req->tracking_input;
            $reply = $req->reply_text;

            return [
                'id' => $req->id,
                'reference' => $req->reference,
                'input' => $input,
                'status' => $req->status,
                'reply' => $reply,
                'format_input_reply' => "{$input} = {$reply}",
                'format_reply_input' => "{$reply} = {$input}",
            ];
        });

        $completedItems = $requests->where('status', 'completed');
        $pendingItems = $requests->whereIn('status', ['pending', 'processing']);
        $failedItems = $requests->where('status', 'failed');

        $lines = $items->map(function ($item) {
            $input = $item['input'];
            $reply = $item['reply'];
            if (!empty($reply)) {
                return "{$input}={$reply}";
            }
            return $input;
        })->implode("\n");

        $totalCount = $items->count();
        $successfulCount = $completedItems->count();
        $failedCount = $failedItems->count();
        $pendingCount = $pendingItems->count();

        $summarySection = "Summary:\nTotal Records: {$totalCount}\nTotal Successful: {$successfulCount}";
        if ($pendingCount > 0) {
            $summarySection .= "\nTotal Pending: {$pendingCount}";
        }
        $summarySection .= "\nTotal Failed: {$failedCount}";

        $reportText = "{$lines}\n\n{$summarySection}";

        return response()->json([
            'batch_number' => $batchNumber,
            'service_name' => $serviceName,
            'created_at' => $createdAt,
            'total' => $items->count(),
            'completed' => $completedItems->count(),
            'processing' => $requests->where('status', 'processing')->count(),
            'pending' => $pendingItems->count(),
            'failed' => $failedItems->count(),
            'successful_count' => $completedItems->count(),
            'successful_amount' => '₦' . number_format((float) $completedItems->sum('amount_charged'), 2),
            'pending_count' => $pendingItems->count(),
            'pending_amount' => '₦' . number_format((float) $pendingItems->sum('amount_charged'), 2),
            'failed_count' => $failedItems->count(),
            'failed_amount' => '₦' . number_format((float) $failedItems->sum('amount_charged'), 2),
            'grand_total_count' => $items->count(),
            'grand_total_amount' => '₦' . number_format((float) $requests->sum('amount_charged'), 2),
            'items' => $items,
            'report_text' => $reportText,
            'lines_input_reply' => $items->pluck('format_input_reply')->implode("\n"),
            'lines_reply_input' => $items->pluck('format_reply_input')->implode("\n"),
        ]);
    }
}
