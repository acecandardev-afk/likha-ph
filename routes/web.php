<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AnalyticsController as AdminAnalyticsController;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLogController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CodTreasuryController as AdminCodTreasuryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DeliveryController as AdminDeliveryController;
use App\Http\Controllers\Admin\DeliveryReportController as AdminDeliveryReportController;
use App\Http\Controllers\Admin\FinancialDisputeController as AdminFinancialDisputeController;
use App\Http\Controllers\Admin\LedgerController as AdminLedgerController;
use App\Http\Controllers\Admin\PaymentVerificationController;
// Admin Controllers
use App\Http\Controllers\Admin\ProductApprovalController;
use App\Http\Controllers\Admin\RiderController as AdminRiderController;
use App\Http\Controllers\Admin\SaleController as AdminSaleController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\VoucherController as AdminVoucherController;
use App\Http\Controllers\Artisan\CodHandoffRedirectController;
use App\Http\Controllers\Artisan\DashboardController as ArtisanDashboardController;
use App\Http\Controllers\Artisan\EarningsController as ArtisanEarningsController;
use App\Http\Controllers\Artisan\LedgerController as ArtisanLedgerController;
use App\Http\Controllers\Artisan\OrderController as ArtisanOrderController;
use App\Http\Controllers\Artisan\ProductController as ArtisanProductController;
use App\Http\Controllers\Artisan\ProfileController as ArtisanProfileEditController;
use App\Http\Controllers\ArtisanProfileController;
use App\Http\Controllers\Auth\ArtisanRegistrationController;
// Artisan Controllers
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
// Customer Controllers
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\DeliveryReportController as CustomerDeliveryReportController;
use App\Http\Controllers\Customer\FinancialDisputeRedirectController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\OrderFinancialDisputeController as CustomerOrderFinancialDisputeController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\DirectMessageController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Rider\DashboardController as RiderDashboardController;
use App\Http\Controllers\Rider\DeliveryController as RiderDeliveryController;
use App\Http\Controllers\Rider\RemittanceController as RiderRemittanceController;
use App\Http\Controllers\Rider\SettlementController as RiderSettlementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/legal/seller-agreement', [LegalController::class, 'sellerAgreement'])->name('legal.seller-agreement');

Route::get('/up', [HealthController::class, 'index']);

// Backwards-compatible /home URL used by auth scaffolding
Route::get('/home', [HomeController::class, 'index']);

// Products
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

// Artisan Profiles
Route::get('/artisans', [ArtisanProfileController::class, 'index'])->name('artisans.index');
Route::get('/artisans/{artisan}', [ArtisanProfileController::class, 'show'])->name('artisans.show');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Auth routes (explicit; avoids dependency on laravel/ui in production)
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
// Avoid 500s when /logout is opened directly (GET). Real logout is POST.
Route::get('/logout', [HomeController::class, 'index']);

// POST-only endpoint uses POST route below; GET uses Laravel's built-in redirect (no app controller — avoids deploy/autoload issues).
Route::redirect('/rider/cod-remittance', '/rider/cod-settlement', 302)->name('rider.cod-remittance.redirect');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Password reset
Route::get('/password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// Password confirmation
Route::get('/password/confirm', [ConfirmPasswordController::class, 'showConfirmForm'])->name('password.confirm');
Route::post('/password/confirm', [ConfirmPasswordController::class, 'confirm']);

// Email verification
Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::post('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');

// Artisan: for guests it registers a new artisan account.
// For logged-in customers it upgrades the current account into an artisan.
Route::get('/register/artisan', [ArtisanRegistrationController::class, 'createOrApply'])->name('register.artisan');
Route::post('/register/artisan', [ArtisanRegistrationController::class, 'storeOrApply'])->name('register.artisan.store');

// Artisan waiting page after application submission
Route::middleware('auth')->group(function () {
    Route::get('/artisan/application/pending', [ArtisanRegistrationController::class, 'pending'])->name('artisan.apply.pending');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/insights', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('/activity', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/ledger', [AdminLedgerController::class, 'index'])->name('ledger.index');
    Route::get('/ledger/journals/{journal}', [AdminLedgerController::class, 'show'])->name('ledger.show');

    Route::get('/cod-treasury', [AdminCodTreasuryController::class, 'index'])->name('cod-treasury.index');
    Route::get('/financial-disputes', [AdminFinancialDisputeController::class, 'index'])->name('financial-disputes.index');
    Route::patch('/financial-disputes/{dispute}', [AdminFinancialDisputeController::class, 'resolve'])
        ->middleware('throttle:120,1')
        ->name('financial-disputes.resolve');

    Route::resource('vouchers', AdminVoucherController::class)->except(['show']);

    // Product Approval
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/pending', [ProductApprovalController::class, 'index'])->name('pending');
        Route::get('/approved', [ProductApprovalController::class, 'approved'])->name('approved');
        Route::get('/rejected', [ProductApprovalController::class, 'rejected'])->name('rejected');
        Route::get('/{product}/review', [ProductApprovalController::class, 'show'])->name('review');
        Route::patch('/{product}/approve', [ProductApprovalController::class, 'approve'])->name('approve');
        Route::patch('/{product}/reject', [ProductApprovalController::class, 'reject'])->name('reject');
    });

    // Payment Verification
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/pending', [PaymentVerificationController::class, 'index'])->name('pending');
        Route::get('/verified', [PaymentVerificationController::class, 'verified'])->name('verified');
        Route::get('/{payment}/review', [PaymentVerificationController::class, 'show'])->name('review');
        Route::patch('/{payment}/verify', [PaymentVerificationController::class, 'verify'])->name('verify');
        Route::patch('/{payment}/reject', [PaymentVerificationController::class, 'reject'])->name('reject');
    });

    // User Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/artisans', [UserManagementController::class, 'artisans'])->name('artisans');
        Route::get('/customers', [UserManagementController::class, 'customers'])->name('customers');
        Route::patch('/{user}/suspend', [UserManagementController::class, 'suspend'])->name('suspend');
        Route::patch('/{user}/activate', [UserManagementController::class, 'activate'])->name('activate');
    });

    // Riders
    Route::prefix('riders')->name('riders.')->group(function () {
        Route::get('/', [AdminRiderController::class, 'index'])->name('index');
        Route::post('/', [AdminRiderController::class, 'store'])->name('store');
        Route::get('/{rider}', [AdminRiderController::class, 'show'])->name('show');
        Route::put('/{rider}', [AdminRiderController::class, 'update'])->name('update');
        Route::patch('/{rider}/activate', [AdminRiderController::class, 'activate'])->name('activate');
        Route::patch('/{rider}/deactivate', [AdminRiderController::class, 'deactivate'])->name('deactivate');
    });

    // Delivery monitoring (per package)
    Route::prefix('deliveries')->name('deliveries.')->group(function () {
        Route::get('/', [AdminDeliveryController::class, 'index'])->name('index');
        Route::patch('/packages/{orderPackage}/assign', [AdminDeliveryController::class, 'assign'])->name('assign');
        Route::patch('/packages/{orderPackage}/status', [AdminDeliveryController::class, 'updateStatus'])->name('status');
    });

    Route::prefix('delivery-reports')->name('delivery-reports.')->group(function () {
        Route::get('/', [AdminDeliveryReportController::class, 'index'])->name('index');
        Route::get('/{deliveryReport}', [AdminDeliveryReportController::class, 'show'])->name('show');
        Route::patch('/{deliveryReport}', [AdminDeliveryReportController::class, 'update'])->name('update');
    });

    // Categories
    Route::resource('categories', AdminCategoryController::class)->except(['show', 'create', 'edit']);

    // Sales (POS)
    Route::get('/sales', [AdminSaleController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [AdminSaleController::class, 'create'])->name('sales.create');
    Route::post('/sales', [AdminSaleController::class, 'store'])->name('sales.store');
    Route::get('/sales/{sale}', [AdminSaleController::class, 'show'])->name('sales.show');
});

/*
|--------------------------------------------------------------------------
| Artisan Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'artisan'])->prefix('artisan')->name('artisan.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [ArtisanDashboardController::class, 'index'])->name('dashboard');

    Route::get('/after-delivery', [ArtisanEarningsController::class, 'index'])->name('earnings.index');
    Route::get('/ledger', [ArtisanLedgerController::class, 'index'])->name('ledger.index');
    Route::get('/ledger/journals/{journal}', [ArtisanLedgerController::class, 'show'])->name('ledger.show');

    // Products
    Route::resource('products', ArtisanProductController::class);

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [ArtisanOrderController::class, 'index'])->name('index');
        Route::get('/{order}/cod-handoff', CodHandoffRedirectController::class)
            ->name('cod-handoff.redirect');
        Route::post('/{order}/cod-handoff', [ArtisanOrderController::class, 'storeCodHandoff'])
            ->middleware('throttle:15,1')
            ->name('cod-handoff.store');
        Route::get('/{order}', [ArtisanOrderController::class, 'show'])->name('show');
        Route::patch('/{order}/approve', [ArtisanOrderController::class, 'approve'])->name('approve');
        Route::patch('/{order}/complete', [ArtisanOrderController::class, 'complete'])->name('complete');
    });

    // Profile
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/edit', [ArtisanProfileEditController::class, 'edit'])->name('edit');
        Route::put('/', [ArtisanProfileEditController::class, 'update'])->name('update');
        Route::delete('/photo', [ArtisanProfileEditController::class, 'removeProfileImage'])->name('remove-photo');
    });
});

/*
|--------------------------------------------------------------------------
| Customer Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'customer'])->prefix('customer')->name('customer.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [CustomerDashboardController::class, 'index'])->name('dashboard');

    // Cart
    Route::prefix('cart')->name('cart.')->group(function () {
        Route::get('/', [CartController::class, 'index'])->name('index');
        Route::post('/add/{product}', [CartController::class, 'add'])->name('add');
        Route::patch('/{cart}', [CartController::class, 'update'])->name('update');
        Route::delete('/{cart}', [CartController::class, 'remove'])->name('remove');
        Route::delete('/', [CartController::class, 'clear'])->name('clear');
    });

    // Checkout
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/preview-totals', [CheckoutController::class, 'previewTotals'])
            ->middleware('throttle:60,1')
            ->name('preview-totals');
        Route::post('/', [CheckoutController::class, 'store'])->name('store');
    });

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [CustomerOrderController::class, 'index'])->name('index');
        Route::get('/{order}', [CustomerOrderController::class, 'show'])->name('show');
        Route::get('/{order}/tracking', [CustomerOrderController::class, 'tracking'])->name('tracking');
        Route::get('/{order}/financial-disputes', FinancialDisputeRedirectController::class)
            ->name('financial-disputes.redirect');
        Route::post('/{order}/payment-proof', [CustomerOrderController::class, 'uploadPaymentProof'])->name('payment-proof');
        Route::patch('/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('cancel');
        Route::patch('/{order}/mark-received', [CustomerOrderController::class, 'markReceived'])->name('mark-received');
        Route::post('/{order}/financial-disputes', [CustomerOrderFinancialDisputeController::class, 'store'])
            ->middleware('throttle:8,1')
            ->name('financial-disputes.store');
    });

    Route::prefix('delivery-reports')->name('delivery-reports.')->group(function () {
        Route::get('/package/{orderPackage}/create', [CustomerDeliveryReportController::class, 'create'])->name('create');
        Route::post('/package/{orderPackage}', [CustomerDeliveryReportController::class, 'store'])->name('store');
    });

    // Reviews
    Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/orders/{order}/products/{product}', [ReviewController::class, 'create'])->name('create');
        Route::post('/orders/{order}/products/{product}', [ReviewController::class, 'store'])->name('store');
    });
});

/*
|--------------------------------------------------------------------------
| Shared Authenticated Routes (Customer & Artisan)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {
    // Account settings (shipping address) - available to all authenticated users
    Route::get('/account/shipping', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account/shipping', [AccountController::class, 'update'])->name('account.update');

    // Notifications
    Route::get('/notifications', [NotificationsController::class, 'index'])->name('notifications.index');

    // Order Messages
    Route::prefix('orders/{order}/messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::post('/', [MessageController::class, 'store'])->name('store');
        Route::get('/fetch', [MessageController::class, 'fetch'])->name('fetch');
    });

    // Profile chat (user ↔ artisan)
    Route::get('/chats', [DirectMessageController::class, 'conversations'])->name('chats.index');
    Route::prefix('chat/with/{user}')->name('chat.')->group(function () {
        Route::get('/', [DirectMessageController::class, 'index'])->name('index');
        Route::post('/', [DirectMessageController::class, 'store'])->name('store');
    });
});

/*
|--------------------------------------------------------------------------
| Rider Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'rider'])->prefix('rider')->name('rider.')->group(function () {
    Route::get('/dashboard', [RiderDashboardController::class, 'index'])->name('dashboard');
    Route::get('/cod-settlement', [RiderSettlementController::class, 'index'])->name('cod-settlement');
    Route::post('/cod-remittance', [RiderRemittanceController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('cod-remittance.store');
    Route::prefix('deliveries')->name('deliveries.')->group(function () {
        Route::get('/', [RiderDeliveryController::class, 'index'])->name('index');
        Route::get('/package/{orderPackage}', [RiderDeliveryController::class, 'show'])->name('show');
        Route::patch('/package/{orderPackage}/status', [RiderDeliveryController::class, 'updateStatus'])->name('status');
    });
});
