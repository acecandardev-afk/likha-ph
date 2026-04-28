@php
    $excludeFlashValidationSummary = request()->routeIs(
        'login',
        'register',
        'register.artisan',
        'register.artisan.store',
        'password.request',
        'password.email'
    );
    $quantityOnlyOnCheckout =
        isset($errors)
        && $errors->count() === 1
        && $errors->has('quantity')
        && request()->routeIs('customer.checkout.index');

    $showFriendlyValidationSummary = isset($errors)
        && $errors->any()
        && ! $excludeFlashValidationSummary
        && ! $quantityOnlyOnCheckout;
@endphp

<div id="likhaToastStack" class="likha-toast-stack" aria-live="polite" aria-atomic="true">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show likha-toast mb-0" role="alert" data-likha-auto-dismiss="3500">
            <div class="d-flex align-items-start gap-2">
                <div class="flex-grow-1 min-w-0">{{ session('success') }}</div>
                <button type="button" class="btn-close flex-shrink-0 mt-0" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show likha-toast mb-0" role="alert" data-likha-auto-dismiss="3500">
            <div class="d-flex align-items-start gap-2">
                <div class="flex-grow-1 min-w-0">{{ session('status') }}</div>
                <button type="button" class="btn-close flex-shrink-0 mt-0" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if(session('resent'))
        <div class="alert alert-success alert-dismissible fade show likha-toast mb-0" role="alert" data-likha-auto-dismiss="3500">
            <div class="d-flex align-items-start gap-2">
                <div class="flex-grow-1 min-w-0">A new verification link has been sent to your email.</div>
                <button type="button" class="btn-close flex-shrink-0 mt-0" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show likha-toast mb-0" role="alert" data-likha-auto-dismiss="3500">
            <div class="d-flex align-items-start gap-2">
                <div class="flex-grow-1 min-w-0">{{ session('warning') }}</div>
                <button type="button" class="btn-close flex-shrink-0 mt-0" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show likha-toast mb-0" role="alert" data-likha-auto-dismiss="6000">
            <div class="d-flex align-items-start gap-2">
                <div class="flex-grow-1 min-w-0">{{ session('error') }}</div>
                <button type="button" class="btn-close flex-shrink-0 mt-0" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    @if($showFriendlyValidationSummary)
        <div class="alert alert-danger alert-dismissible fade show likha-toast mb-0" role="alert" data-likha-auto-dismiss="6000">
            <div class="d-flex align-items-start gap-2">
                <div class="flex-grow-1 min-w-0">
                    We couldn’t complete that action. Please check the highlighted fields and try again.
                </div>
                <button type="button" class="btn-close flex-shrink-0 mt-0" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif
</div>
