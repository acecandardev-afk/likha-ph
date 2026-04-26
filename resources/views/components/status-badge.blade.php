@props(['status', 'type' => 'order'])

@php
    $statusConfig = [
        'order' => [
            'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Pending'],
            'approved' => ['class' => 'primary', 'icon' => 'check-circle', 'text' => 'Approved'],
            'confirmed' => ['class' => 'info', 'icon' => 'check', 'text' => 'Confirmed'],
            'shipped' => ['class' => 'info', 'icon' => 'truck', 'text' => 'Shipped'],
            'on_delivery' => ['class' => 'primary', 'icon' => 'truck', 'text' => 'On Delivery'],
            'received' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Received'],
            'delivered' => ['class' => 'success', 'icon' => 'check-all', 'text' => 'Delivered'],
            'completed' => ['class' => 'success', 'icon' => 'check-all', 'text' => 'Completed'],
            'cancelled' => ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Cancelled'],
        ],
        'payment' => [
            'awaiting_proof' => ['class' => 'secondary', 'icon' => 'hourglass', 'text' => 'Awaiting Proof'],
            'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Pending Verification'],
            'verified' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Verified'],
            'rejected' => ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Rejected'],
        ],
        'product' => [
            'pending' => ['class' => 'warning', 'icon' => 'clock', 'text' => 'Pending Approval'],
            'approved' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Approved'],
            'rejected' => ['class' => 'danger', 'icon' => 'x-circle', 'text' => 'Rejected'],
        ],
        'delivery' => [
            'pending_assignment' => ['class' => 'secondary', 'icon' => 'hourglass-split', 'text' => 'Pending Assignment'],
            'order_confirmed' => ['class' => 'info', 'icon' => 'check-circle', 'text' => 'Order Confirmed'],
            'preparing_package' => ['class' => 'primary', 'icon' => 'box-seam', 'text' => 'Preparing Package'],
            'package_picked_up' => ['class' => 'primary', 'icon' => 'box-arrow-up-right', 'text' => 'Package Picked Up'],
            'arrived_sort_center' => ['class' => 'info', 'icon' => 'building', 'text' => 'Arrived at Sort Center'],
            'out_for_delivery' => ['class' => 'warning', 'icon' => 'truck', 'text' => 'Out for Delivery'],
            'delivered' => ['class' => 'success', 'icon' => 'check2-circle', 'text' => 'Delivered'],
            'available' => ['class' => 'success', 'icon' => 'check-circle', 'text' => 'Available'],
            'busy' => ['class' => 'warning', 'icon' => 'hourglass-split', 'text' => 'Busy'],
            'offline' => ['class' => 'secondary', 'icon' => 'slash-circle', 'text' => 'Offline'],
        ],
    ];

    $config = $statusConfig[$type][$status] ?? ['class' => 'secondary', 'icon' => 'question', 'text' => ucfirst($status)];
@endphp

<span class="badge rounded-pill bg-{{ $config['class'] }} d-inline-flex align-items-center gap-1">
    <i class="bi bi-{{ $config['icon'] }}"></i>
    <span>{{ $config['text'] }}</span>
</span>