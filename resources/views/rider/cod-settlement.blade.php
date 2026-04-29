@extends('layouts.app')

@section('title', 'Pay-on-delivery summary')

@section('content')
<div class="container py-3 py-md-4">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('rider.dashboard') }}" class="text-decoration-none">Rider dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pay-on-delivery</li>
        </ol>
    </nav>

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h1 class="h3 fw-semibold mb-1">Pay when delivered — your amounts</h1>
            <p class="text-muted mb-0 small">
                See how cash from each drop-off lines up when an order ships in more than one package.
                The full order total is finalized once every package is delivered — use this page to plan how much cash to turn in each day.
            </p>
        </div>
        <a href="{{ route('rider.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
    </div>

    <div class="alert alert-secondary border-0 small mb-4">
        <div class="fw-semibold mb-2">Quick guide</div>
        <p class="mb-2 mb-md-3">These numbers split each delivery fairly when there is more than one stop.</p>
        <details class="mb-0">
            <summary class="text-primary" style="cursor: pointer;">How we calculate this</summary>
            <div class="mt-2 pt-2 border-top">
                @if(config('cod.allocation_policy') === \App\Services\RiderSettlementService::POLICY_SINGLE_FINAL)
                    <p class="mb-2"><strong>Last delivery:</strong> The full receipt amount appears only when the <em>last</em> package for that order is delivered.</p>
                @else
                    <p class="mb-2"><strong>Split by package:</strong> Each drop-off carries a fair share based on the items in that package.</p>
                @endif
                <p class="mb-0 text-muted">Official amounts for staff are listed under Admin → Payment records. Enter your daily cash turn-in below so the office can match what you handed over.</p>
            </div>
        </details>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form method="get" action="{{ route('rider.cod-settlement') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="date_from" class="form-label small fw-semibold mb-1">From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ old('date_from', $dateFrom->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label small fw-semibold mb-1">To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ old('date_to', $dateTo->format('Y-m-d')) }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Apply range</button>
                    <a href="{{ route('rider.cod-settlement') }}" class="btn btn-outline-secondary">This month</a>
                </div>
            </form>
            @error('date_from')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            @error('date_to')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Cash collected (pay on delivery)</div>
                    <div class="fs-4 fw-bold text-dark">₱{{ number_format($periodTotals['cod_total'], 2) }}</div>
                    <div class="small text-muted mt-2">{{ number_format($periodTotals['packages_count']) }} deliveries</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">For sellers (their goods)</div>
                    <div class="fs-4 fw-bold text-success">₱{{ number_format($periodTotals['seller_share'], 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm border-primary border-opacity-25">
                <div class="card-body">
                    <div class="text-muted small text-uppercase fw-semibold mb-1">Likha fees &amp; shipping</div>
                    <div class="fs-4 fw-bold text-primary">₱{{ number_format($periodTotals['company_side_total'], 2) }}</div>
                    <div class="small text-muted mt-2">Platform ₱{{ number_format($periodTotals['platform_fee'], 2) }} · Ship ₱{{ number_format($periodTotals['shipping'], 2) }} · Tax ₱{{ number_format($periodTotals['tax'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-light border small mb-4 mb-md-5">
        <strong>All-time totals (every delivery):</strong> cash ₱{{ number_format($lifetimeTotals['cod_total'], 2) }}
        · sellers ₱{{ number_format($lifetimeTotals['seller_share'], 2) }}
        · Likha &amp; fees ₱{{ number_format($lifetimeTotals['company_side_total'], 2) }}
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <h2 class="h6 fw-bold mb-1">Daily cash turn-in</h2>
            <p class="small text-muted mb-0">Tell us how much cash you handed to sellers or the office for that calendar day. We compare this with our payment records.</p>
        </div>
        <div class="card-body p-4 pt-3">
            <form method="POST" action="{{ route('rider.cod-remittance.store') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Date</label>
                    <input type="date" name="report_date" class="form-control" value="{{ old('report_date', now()->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Total cash you turned in</label>
                    <input type="number" step="0.01" min="0" name="cod_declared_total" class="form-control" value="{{ old('cod_declared_total') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Amount for sellers (optional)</label>
                    <input type="number" step="0.01" min="0" name="seller_pool_declared" class="form-control" value="{{ old('seller_pool_declared') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold mb-1">Amount for Likha office (optional)</label>
                    <input type="number" step="0.01" min="0" name="platform_pool_declared" class="form-control" value="{{ old('platform_pool_declared') }}">
                </div>
                <div class="col-12">
                    <label class="form-label small fw-semibold mb-1">Notes</label>
                    <textarea name="notes" class="form-control form-control-sm" rows="2" maxlength="2000" placeholder="Optional">{{ old('notes') }}</textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
            <h2 class="h5 fw-bold mb-1">Delivery breakdown</h2>
            <p class="small text-muted mb-0">Per store: products, when it was delivered, and how cash splits for that stop.</p>
        </div>
        <div class="card-body p-4">
            @if(empty($rows))
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                    <p class="mb-0">No completed deliveries in this range.</p>
                </div>
            @else
                <div class="d-lg-none">
                    @foreach($rows as $row)
                        @php($pkg = $row['package'])
                        @php($a = $row['allocation'])
                        <div class="border rounded-3 p-3 mb-3 bg-light bg-opacity-50">
                            <div class="fw-semibold">{{ $row['workshop'] }}</div>
                            <div class="small text-muted font-monospace">{{ $row['order_number'] }} · Pkg #{{ $pkg->sequence }}</div>
                            <div class="small mt-2 mb-2"><span class="text-muted">Delivered</span><br>{{ $pkg->deliveredAtLabel() }}</div>
                            <ul class="list-unstyled small mb-3">
                                @foreach($row['line_items'] as $li)
                                    <li class="py-1 border-bottom border-white">
                                        {{ $li['name'] }}
                                        <span class="text-muted">· Order item #{{ $li['order_item_id'] }}</span>
                                        @if($li['product_id'])
                                            <span class="text-muted">· Product #{{ $li['product_id'] }}</span>
                                        @endif
                                        <br>
                                        <span class="text-muted">× {{ $li['quantity'] }} · ₱{{ number_format($li['line_total'], 2) }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="row row-cols-2 g-2 small">
                                <div><span class="text-muted">Cash at door</span><br><span class="fw-bold">₱{{ number_format($a['cod_total'], 2) }}</span></div>
                                <div><span class="text-muted">Seller</span><br><span class="fw-semibold text-success">₱{{ number_format($a['seller_share'], 2) }}</span></div>
                                <div><span class="text-muted">Likha &amp; fees</span><br><span class="fw-semibold text-primary">₱{{ number_format($a['company_side_total'], 2) }}</span></div>
                                <div><span class="text-muted">Pkg merch</span><br>₱{{ number_format($a['merchandise_in_package'], 2) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="d-none d-lg-block">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-muted text-uppercase">
                                    <th>Store / order</th>
                                    <th>Items</th>
                                    <th>Delivered</th>
                                    <th class="text-end">Cash</th>
                                    <th class="text-end">Seller</th>
                                    <th class="text-end">Likha &amp; fees</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $row)
                                    @php($pkg = $row['package'])
                                    @php($a = $row['allocation'])
                                    <tr>
                                        <td style="min-width: 180px;">
                                            <div class="fw-semibold">{{ $row['workshop'] }}</div>
                                            <div class="small font-monospace text-muted">{{ $row['order_number'] }} · Pkg {{ $pkg->sequence }}</div>
                                        </td>
                                        <td style="min-width: 260px;">
                                            <ul class="list-unstyled small mb-0">
                                                @foreach($row['line_items'] as $li)
                                                    <li class="mb-1">
                                                        {{ $li['name'] }}
                                                        <span class="text-muted">(order line #{{ $li['order_item_id'] }}@if(!empty($li['product_id'])), product #{{ $li['product_id'] }}@endif)</span>
                                                        · ×{{ $li['quantity'] }} · ₱{{ number_format($li['line_total'], 2) }}
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td style="min-width: 220px;"><span class="small">{{ $pkg->deliveredAtLabel() }}</span></td>
                                        <td class="text-end fw-semibold">₱{{ number_format($a['cod_total'], 2) }}</td>
                                        <td class="text-end text-success fw-semibold">₱{{ number_format($a['seller_share'], 2) }}</td>
                                        <td class="text-end text-primary fw-semibold">₱{{ number_format($a['company_side_total'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3">{{ $packages->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
