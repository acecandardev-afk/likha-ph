@extends('layouts.app')

@section('title', 'Delivery progress')

@section('content')
<div class="container py-2 py-md-3">
    <a href="{{ route('rider.deliveries.index') }}" class="btn btn-outline-secondary btn-sm mb-3">Back to deliveries</a>

    @if ($errors->has('delivery'))
        <div class="alert alert-danger">{{ $errors->first('delivery') }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <strong>Order {{ $order->order_number }} · Package {{ $orderPackage->sequence }}</strong>
            <x-status-badge :status="$orderPackage->delivery_status" type="delivery" />
        </div>
        <div class="card-body">
            <p class="mb-1"><strong>Customer:</strong> {{ $order->customer?->name }}</p>
            <p class="mb-1"><strong>Phone:</strong> {{ $order->shipping_phone ?? 'N/A' }}</p>
            <p class="mb-0"><strong>Address:</strong> {{ $order->formattedShippingAddress() }}</p>
        </div>
    </div>

    @if($orderPackage->items->isNotEmpty())
    <div class="card mb-3">
        <div class="card-header"><strong>Items in this package</strong></div>
        <div class="card-body py-2">
            @foreach($orderPackage->items as $pi)
                @php $oi = $pi->orderItem; @endphp
                <div class="small py-1 border-bottom">{{ $oi->product_name ?? 'Item' }} × {{ $pi->quantity }}</div>
            @endforeach
        </div>
    </div>
    @endif

    @if($orderPackage->isDelivered())
        <div class="card mb-3 border-success">
            <div class="card-header bg-success-subtle"><strong>Delivery complete</strong></div>
            <div class="card-body">
                <p class="mb-2"><strong>Completed:</strong> {{ $orderPackage->delivery_completed_at?->format('M d, Y h:i A') ?? '—' }}</p>
                @if($orderPackage->delivery_proof_image_url)
                    <p class="mb-2 small text-muted">Proof of handoff</p>
                    <a href="{{ $orderPackage->delivery_proof_image_url }}" target="_blank" rel="noopener">
                        <img src="{{ $orderPackage->delivery_proof_image_url }}" alt="Delivery proof" class="img-fluid rounded border" style="max-height: 280px;">
                    </a>
                @else
                    <p class="text-muted small mb-0">No proof photo on file.</p>
                @endif
                <p class="text-muted small mt-3 mb-0">This package is finalized. Progress cannot be edited.</p>
            </div>
        </div>
    @else
        <div class="card mb-3">
            <div class="card-header"><strong>Update delivery progress</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('rider.deliveries.status', $orderPackage) }}" class="row g-2">
                    @csrf
                    @method('PATCH')
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold" for="delivery_status_progress">Current step</label>
                        <select name="delivery_status" id="delivery_status_progress" class="form-select" required>
                            @foreach($progressStatusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($orderPackage->delivery_status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold" for="progress_note">Note (optional)</label>
                        <input class="form-control" id="progress_note" name="note" placeholder="Optional note">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Save progress</button>
                    </div>
                </form>
                <p class="small text-muted mt-3 mb-0">When the package has been handed to the customer, use <strong>Mark as delivered</strong> and upload a proof photo.</p>
                <button type="button" class="btn btn-success mt-2" data-bs-toggle="modal" data-bs-target="#markDeliveredModal">
                    <i class="bi bi-check-circle me-1"></i> Mark as delivered…
                </button>
            </div>
        </div>

        <div class="modal fade" id="markDeliveredModal" tabindex="-1" aria-labelledby="markDeliveredModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="{{ route('rider.deliveries.status', $orderPackage) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="mark_delivered" value="1">
                        <div class="modal-header">
                            <h2 class="modal-title h5" id="markDeliveredModalLabel">Confirm delivery</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-3">Only continue if you have physically handed this package to the customer (or their representative). Upload a clear photo showing the handoff or drop-off confirmation.</p>
                            <div class="mb-3">
                                <label for="delivery_proof_modal" class="form-label fw-semibold">Proof photo <span class="text-danger">*</span></label>
                                <input type="file" name="proof_image" id="delivery_proof_modal" class="form-control @error('proof_image') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/webp" required>
                                @error('proof_image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <div class="form-text">JPEG, PNG, or WebP. Max 4 MB.</div>
                            </div>
                            <div class="mb-2">
                                <label for="delivered_note" class="form-label">Note (optional)</label>
                                <input type="text" name="note" id="delivered_note" class="form-control" maxlength="255" placeholder="e.g. Received by customer">
                            </div>
                            <div class="form-check">
                                <input class="form-check-input @error('confirm_handoff') is-invalid @enderror" type="checkbox" name="confirm_handoff" id="confirm_handoff" value="yes" required>
                                <label class="form-check-label" for="confirm_handoff">
                                    I confirm the package was delivered as described above.
                                </label>
                                @error('confirm_handoff')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">
                                Submit delivery
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header"><strong>Timeline</strong></div>
        <div class="card-body">
            @php
                $events = $order->deliveryHistory->filter(function ($e) use ($orderPackage) {
                    return $e->order_package_id === null || (int) $e->order_package_id === (int) $orderPackage->id;
                });
            @endphp
            @forelse($events as $event)
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between">
                        <div><x-status-badge :status="$event->status" type="delivery" /></div>
                        <small class="text-muted">{{ $event->status_at?->format('M d, Y h:i A') }}</small>
                    </div>
                    @if($event->note)<div class="small mt-1">{{ $event->note }}</div>@endif
                    <small class="text-muted">Updated by: {{ $event->actor?->name ?? 'System' }}</small>
                </div>
            @empty
                <p class="text-muted mb-0">No tracking entries yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
