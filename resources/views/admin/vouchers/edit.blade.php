@extends('layouts.app')

@section('title', 'Edit voucher')

@section('content')
<div class="container py-2 py-md-3 col-lg-10">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.vouchers.index') }}">Promo vouchers</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit {{ $voucher->code }}</li>
        </ol>
    </nav>

    <h1 class="h2 fw-semibold mb-4">Edit voucher</h1>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('admin.vouchers.update', $voucher) }}">
                @csrf
                @method('PUT')

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $voucher->code) }}" class="form-control @error('code') is-invalid @enderror" maxlength="40" required autocomplete="off">
                        @error('code')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Label</label>
                        <input type="text" name="label" value="{{ old('label', $voucher->label) }}" class="form-control @error('label') is-invalid @enderror" maxlength="255">
                        @error('label')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Discount type <span class="text-danger">*</span></label>
                        <select name="discount_type" class="form-select @error('discount_type') is-invalid @enderror" required>
                            <option value="percent" @selected(old('discount_type', $voucher->discount_type) === 'percent')>Percent off merchandise</option>
                            <option value="fixed" @selected(old('discount_type', $voucher->discount_type) === 'fixed')>Fixed ₱ off merchandise</option>
                        </select>
                        @error('discount_type')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Value <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="discount_value" value="{{ old('discount_value', $voucher->discount_value) }}" class="form-control @error('discount_value') is-invalid @enderror" required>
                        @error('discount_value')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Minimum merchandise subtotal</label>
                        <input type="number" step="0.01" min="0" name="min_order_amount" value="{{ old('min_order_amount', $voucher->min_order_amount) }}" class="form-control @error('min_order_amount') is-invalid @enderror">
                        @error('min_order_amount')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Max discount cap</label>
                        <input type="number" step="0.01" min="0" name="maximum_discount_amount" value="{{ old('maximum_discount_amount', $voucher->maximum_discount_amount) }}" class="form-control @error('maximum_discount_amount') is-invalid @enderror">
                        @error('maximum_discount_amount')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Max redemptions</label>
                        <input type="number" min="1" name="max_redemptions" value="{{ old('max_redemptions', $voucher->max_redemptions) }}" class="form-control @error('max_redemptions') is-invalid @enderror" placeholder="Blank = unlimited">
                        @error('max_redemptions')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        <small class="text-muted">Times redeemed so far: {{ $voucher->times_redeemed }}</small>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $voucher->is_active))>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Starts at</label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($voucher->starts_at)->format('Y-m-d\TH:i')) }}" class="form-control @error('starts_at') is-invalid @enderror">
                        @error('starts_at')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ends at</label>
                        <input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($voucher->ends_at)->format('Y-m-d\TH:i')) }}" class="form-control @error('ends_at') is-invalid @enderror">
                        @error('ends_at')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save changes</button>
                <a href="{{ route('admin.vouchers.index') }}" class="btn btn-outline-secondary">Back</a>
            </form>
        </div>
    </div>
</div>
@endsection
