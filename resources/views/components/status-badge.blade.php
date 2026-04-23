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
    ];

    $config = $statusConfig[$type][$status] ?? ['class' => 'secondary', 'icon' => 'question', 'text' => ucfirst($status)];
@endphp

<span class="badge rounded-pill bg-{{ $config['class'] }} d-inline-flex align-items-center gap-1">
    <i class="bi bi-{{ $config['icon'] }}"></i>
    <span>{{ $config['text'] }}</span>
</span>