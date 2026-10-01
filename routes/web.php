<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\ChargesController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\Admin\InternalChatController;
use App\Http\Controllers\Admin\ManualProcessingController;
use App\Http\Controllers\Admin\SiteSettingsController;
use App\Http\Controllers\Admin\TransactionAuditController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\UserSupportChatController;
use App\Http\Controllers\MonnifyController;
use App\Http\Controllers\Webhook\MonnifyWebhookController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

// Webhook Handlers (Exempt from CSRF)
Route::post('/webhooks/monnify', [MonnifyWebhookController::class, 'handle'])->name('webhooks.monnify');


// Redirect root to dashboard if logged in, else to login
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dual Verification Routes (Separate Email and Phone Verification)
    Route::prefix('verification')->name('verification.')->group(function () {
        Route::get('/email', [VerificationController::class, 'showEmailNotice'])->name('email.notice');
        Route::post('/email/verify', [VerificationController::class, 'verifyEmail'])->name('email.verify');

        Route::get('/phone', [VerificationController::class, 'showPhoneNotice'])->name('phone.notice');
        Route::post('/phone/verify', [VerificationController::class, 'verifyPhone'])->name('phone.verify');
    });

    // Fully Verified User Portal Routes
    Route::middleware('verified.dual')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Dynamic Services Routes
        Route::get('/services/batch/{identifier}', [ServiceController::class, 'batchDetails'])->name('services.batch.details');
        Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');
        Route::post('/services/{slug}', [ServiceController::class, 'submit'])->name('services.submit');
        Route::get('/services/slip/{reference}', [ServiceController::class, 'slip'])->name('services.slip');

        // Wallet & Financial Ledger Routes
        Route::prefix('wallet')->name('wallet.')->group(function () {
            Route::get('/', [WalletController::class, 'index'])->name('index');
            Route::get('/transactions', [WalletController::class, 'transactions'])->name('transactions');
            Route::get('/send-fund', [WalletController::class, 'sendFundPage'])->name('send-fund');
            Route::get('/transfer', [WalletController::class, 'sendFundPage']);
            Route::post('/deposit', [WalletController::class, 'deposit'])->name('deposit');
            Route::post('/transfer', [WalletController::class, 'transfer'])->name('transfer');
            Route::post('/monnify/initialize', [MonnifyController::class, 'initialize'])->name('monnify.initialize');
            Route::get('/monnify/callback', [MonnifyController::class, 'callback'])->name('monnify.callback');
        });

        Route::get('/transactions', [WalletController::class, 'transactions'])->name('transactions.index');

        // User Profile & Account Settings
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', [ProfileController::class, 'index'])->name('index');
            Route::post('/password', [ProfileController::class, 'updatePassword'])->name('password');
        });

        // Help Desk & Live Customer Support
        Route::get('/support', [SupportController::class, 'index'])->name('support.index');
        Route::prefix('support/chat')->name('support.chat.')->group(function () {
            Route::get('/status', [UserSupportChatController::class, 'status'])->name('status');
            Route::get('/messages', [UserSupportChatController::class, 'messages'])->name('messages');
            Route::post('/messages', [UserSupportChatController::class, 'sendMessage'])->name('send');
            Route::post('/call', [UserSupportChatController::class, 'initiateCall'])->name('call');
            Route::get('/call/status', [UserSupportChatController::class, 'callStatus'])->name('call.status');
            Route::post('/call/{call}/signal', [UserSupportChatController::class, 'signalCall'])->name('call.signal');
            Route::post('/call/{call}/end', [UserSupportChatController::class, 'endCall'])->name('call.end');
            Route::post('/call/{call}/simulate-answer', [UserSupportChatController::class, 'simulateAnswer'])->name('call.simulate_answer');
        });

        // Administrative Backend Routes (Gated: Super Admin, Admin, Staff ONLY)
        Route::middleware('admin.access')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

            // Manual Processing Queue (SRS Section 13)
            Route::prefix('queue')->name('queue.')->group(function () {
                Route::get('/', [ManualProcessingController::class, 'index'])->name('index');
                Route::get('/{serviceRequest}', [ManualProcessingController::class, 'show'])->name('show');
                Route::post('/{serviceRequest}/pick', [ManualProcessingController::class, 'pick'])->name('pick');
                Route::post('/{serviceRequest}/complete', [ManualProcessingController::class, 'complete'])->name('complete');
                Route::post('/{serviceRequest}/fail', [ManualProcessingController::class, 'fail'])->name('fail');
                Route::post('/{serviceRequest}/refund', [ManualProcessingController::class, 'refund'])->name('refund');
            });

            // User Governance & Password Reset (SRS Section 5)
            Route::prefix('users')->name('users.')->group(function () {
                Route::get('/', [UserManagementController::class, 'index'])->name('index');
                Route::get('/{user}', [UserManagementController::class, 'show'])->name('show');
                Route::post('/{user}/status', [UserManagementController::class, 'toggleStatus'])->name('status');
                Route::post('/{user}/password', [UserManagementController::class, 'resetPassword'])->name('password');
                Route::post('/{user}/role', [UserManagementController::class, 'changeRole'])->name('role');
            });

            // Announcements Governance (SRS Section 10.3)
            Route::prefix('announcements')->name('announcements.')->group(function () {
                Route::get('/', [AnnouncementController::class, 'index'])->name('index');
                Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
                Route::post('/', [AnnouncementController::class, 'store'])->name('store');
                Route::get('/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('edit');
                Route::put('/{announcement}', [AnnouncementController::class, 'update'])->name('update');
                Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
                Route::post('/{announcement}/toggle-status', [AnnouncementController::class, 'toggleStatus'])->name('status');
            });

            // Wallet & Finance
            Route::prefix('finance')->name('finance.')->group(function () {
                Route::get('/fund-user', [FinanceController::class, 'fundUserForm'])->name('fund');
                Route::post('/fund-user', [FinanceController::class, 'fundUserSubmit'])->name('fund.submit');
                Route::get('/transactions', [FinanceController::class, 'transactions'])->name('transactions');
                Route::get('/funding-history', [FinanceController::class, 'fundingHistory'])->name('history');
            });

            // Transactions Audit
            Route::prefix('transactions')->name('transactions.')->group(function () {
                Route::get('/nin', [TransactionAuditController::class, 'ninVerifications'])->name('nin');
                Route::get('/bvn', [TransactionAuditController::class, 'bvnVerifications'])->name('bvn');
                Route::get('/deposits', [TransactionAuditController::class, 'depositHistory'])->name('deposits');
                Route::get('/transfers', [TransactionAuditController::class, 'transfers'])->name('transfers');
            });

            // Charges & Tariff Governance
            Route::prefix('charges')->name('charges.')->group(function () {
                Route::get('/', [ChargesController::class, 'index'])->name('index');
                Route::put('/', [ChargesController::class, 'update'])->name('update');
            });

            // Site Settings & Platform Configuration
            Route::prefix('settings')->name('settings.')->group(function () {
                Route::get('/', [SiteSettingsController::class, 'index'])->name('index');
                Route::put('/pricing', [ChargesController::class, 'update'])->name('pricing');
                Route::put('/configurations', [SiteSettingsController::class, 'updateConfigurations'])->name('configurations');
                Route::post('/monnify/test', [SiteSettingsController::class, 'testMonnifyConnection'])->name('monnify.test');
            });

            // Frequently Asked Questions (Support FAQ Governance)
            Route::prefix('faqs')->name('faqs.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Admin\FaqController::class, 'index'])->name('index');
                Route::get('/create', [\App\Http\Controllers\Admin\FaqController::class, 'create'])->name('create');
                Route::post('/', [\App\Http\Controllers\Admin\FaqController::class, 'store'])->name('store');
                Route::get('/{faq}/edit', [\App\Http\Controllers\Admin\FaqController::class, 'edit'])->name('edit');
                Route::put('/{faq}', [\App\Http\Controllers\Admin\FaqController::class, 'update'])->name('update');
                Route::post('/{faq}/toggle-status', [\App\Http\Controllers\Admin\FaqController::class, 'toggleStatus'])->name('toggle-status');
                Route::delete('/{faq}', [\App\Http\Controllers\Admin\FaqController::class, 'destroy'])->name('destroy');
            });

            // Internal Communications (Staff & Admin Chat & Calling)
            Route::prefix('chat')->name('chat.')->group(function () {
                Route::get('/', [InternalChatController::class, 'index'])->name('index');
                Route::get('/rooms', [InternalChatController::class, 'rooms'])->name('rooms');
                Route::get('/rooms/{room}/messages', [InternalChatController::class, 'messages'])->name('messages');
                Route::post('/rooms/{room}/messages', [InternalChatController::class, 'sendMessage'])->name('messages.send');
                Route::post('/rooms/{room}/claim', [InternalChatController::class, 'claimSupport'])->name('rooms.claim');
                Route::post('/direct', [InternalChatController::class, 'startDirectChat'])->name('direct');
                Route::post('/calls/initiate', [InternalChatController::class, 'initiateCall'])->name('calls.initiate');
                Route::get('/calls/check', [InternalChatController::class, 'checkCalls'])->name('calls.check');
                Route::post('/calls/{call}/signal', [InternalChatController::class, 'signalCall'])->name('calls.signal');
                Route::post('/calls/{call}/status', [InternalChatController::class, 'updateCallStatus'])->name('calls.status');
            });
        });
    });
});
