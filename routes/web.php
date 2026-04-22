<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ArtisanProfileController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\DirectMessageController;
use App\Http\Controllers\Auth\ArtisanRegistrationController;

// Admin Controllers
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProductApprovalController;
use App\Http\Controllers\Admin\PaymentVerificationController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\SaleController as AdminSaleController;

// Artisan Controllers
use App\Http\Controllers\Artisan\DashboardController as ArtisanDashboardController;
use App\Http\Controllers\Artisan\ProductController as ArtisanProductController;
use App\Http\Controllers\Artisan\OrderController as ArtisanOrderController;
use App\Http\Controllers\Artisan\ProfileController as ArtisanProfileEditController;

// Customer Controllers
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\OrderController as CustomerOrderController;
use App\Http\Controllers\Customer\ReviewController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\ConfirmPasswordController;
use App\Http\Controllers\Auth\VerificationController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/up', function () {
    return response('OK', 200);
});

// Backwards-compatible /home URL used by auth scaffolding
Route::get('/home', function () {
    if (auth()->user()?->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('home');
});

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
Route::get('/logout', fn () => redirect()->route('home'));

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

    // Products
    Route::resource('products', ArtisanProductController::class);

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [ArtisanOrderController::class, 'index'])->name('index');
        Route::get('/{order}', [ArtisanOrderController::class, 'show'])->name('show');
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
        Route::post('/', [CheckoutController::class, 'store'])->name('store');
    });

    // Orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [CustomerOrderController::class, 'index'])->name('index');
        Route::get('/{order}', [CustomerOrderController::class, 'show'])->name('show');
        Route::post('/{order}/payment-proof', [CustomerOrderController::class, 'uploadPaymentProof'])->name('payment-proof');
        Route::patch('/{order}/cancel', [CustomerOrderController::class, 'cancel'])->name('cancel');
        Route::patch('/{order}/mark-received', [CustomerOrderController::class, 'markReceived'])->name('mark-received');
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