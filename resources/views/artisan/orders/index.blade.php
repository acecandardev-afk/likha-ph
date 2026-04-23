@extends('layouts.app')

@section('title', 'Orders')

@section('content')
<div class="container py-2 py-md-3">
    <x-profile-header-nav active="customer-orders" />
    <h1 class="h2 fw-semibold mb-4">Orders</h1>

    @if($orders->isEmpty())
        <div class="alert alert-info">
            You don’t have any orders yet. Orders from customers will appear here.
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
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
                                    <td>{{ $order->customer?->name ?? 'Unknown Customer' }}</td>
                                    <td>{{ $order->created_at?->format('M d, Y') ?? '' }}</td>
                                    <td>{{ $order->estimated_delivery_date }}</td>
                                    <td><x-status-badge :status="$order->status" type="order" /></td>
                                    <td>₱{{ number_format($order->total, 2) }}</td>
                                    <td class="text-end">
                                        @if($order->canBeApproved())
                                            <form action="{{ route('artisan.orders.approve', $order) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success">Approve order</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('artisan.orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View details</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-3">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
