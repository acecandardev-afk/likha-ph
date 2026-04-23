@extends('layouts.app')

@section('title', 'My orders')

@section('content')
@php
    $statusFilter = $statusFilter ?? request('status', 'all');
    $orderStatusFilters = [
        'all' => 'All',
        'pending' => 'Pending',
        'confirmed' => 'Confirmed',
        'shipped' => 'Shipped',
        'on_delivery' => 'On delivery',
        'delivered' => 'Delivered',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];
@endphp
<div class="container py-2 py-md-3">
    <x-profile-header-nav active="my-orders" />
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('customer.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">My orders</li>
        </ol>
    </nav>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
        <h2 class="h4 fw-semibold mb-0">My orders</h2>
    </div>

    <div class="mb-4">
        <label class="form-label small text-muted mb-2">Filter by status</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach($orderStatusFilters as $value => $label)
                <a
                    href="{{ route('customer.orders.index', ['status' => $value]) }}"
                    class="btn btn-sm {{ $statusFilter === $value ? 'btn-primary' : 'btn-outline-secondary' }}"
                >{{ $label }}</a>
            @endforeach
        </div>
    </div>

    @if($orders->isEmpty())
        <div class="alert alert-info">
            @if($statusFilter !== 'all')
                No orders with this status. <a href="{{ route('customer.orders.index') }}" class="alert-link">Show all orders</a>
            @else
                You don’t have any orders yet. <a href="{{ route('products.index') }}" class="alert-link">Start shopping</a> to place your first order.
            @endif
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Order #</th>
                        <th>Artisan</th>
                        <th>Date</th>
                        <th>Est. delivery</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->artisan?->artisanProfile?->workshop_name ?? $order->artisan?->name ?? 'Unknown Artisan' }}</td>
                            <td>{{ $order->created_at?->format('M d, Y') ?? '' }}</td>
                            <td>{{ $order->estimated_delivery_date }}</td>
                            <td><x-status-badge :status="$order->status" type="order" /></td>
                            <td>₱{{ number_format($order->total, 2) }}</td>
                            <td class="text-end">
                                <a href="{{ route('customer.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View details</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
