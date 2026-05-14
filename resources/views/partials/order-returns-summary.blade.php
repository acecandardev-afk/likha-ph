@php
    $returns = $order->relationLoaded('itemReturns') ? $order->itemReturns : $order->itemReturns()->with('orderItem')->get();
@endphp
@if($returns->isNotEmpty())
    <div class="card mb-4 border-warning border-opacity-50">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="mb-0 fw-semibold">Returns</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Item</th>
                            <th>Qty</th>
                            <th>Reason</th>
                            <th>Notes</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returns as $ret)
                            <tr>
                                <td class="ps-3 small">{{ $ret->orderItem?->product_name ?? '—' }}</td>
                                <td class="small">{{ $ret->quantity }}</td>
                                <td class="small">{{ $ret->reasonLabel() }}</td>
                                <td class="small text-muted">{{ \Illuminate\Support\Str::limit($ret->notes ?? '', 56) }}</td>
                                <td><span class="badge bg-secondary">{{ $ret->statusLabel() }}</span></td>
                                <td class="text-end pe-3">
                                    <a href="{{ route($returnShowRoute, $ret) }}" class="btn btn-sm btn-outline-primary py-0">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif
