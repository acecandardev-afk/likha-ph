@extends('layouts.app')

@section('title', 'Activity log')

@section('content')
<div class="container py-2 py-md-3">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2 mb-4">
        <div>
            <h1 class="h2 fw-semibold mb-1">Activity log</h1>
            <p class="text-muted small mb-0">A short history of sensitive changes so you know who acted and when.</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Back to dashboard</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">When</th>
                            <th scope="col">Staff</th>
                            <th scope="col">Action</th>
                            <th scope="col">Summary</th>
                            <th scope="col">Related</th>
                            <th class="d-none d-md-table-cell" scope="col">IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="small text-nowrap">{{ $log->created_at?->format('M j, Y g:i a') }}</td>
                                <td class="small">{{ $log->user?->name ?? '—' }}</td>
                                <td class="small"><span class="badge bg-secondary bg-opacity-25 text-dark">{{ $log->action }}</span></td>
                                <td class="small">{{ $log->description }}</td>
                                <td class="small text-muted">
                                    @if($log->subject_type && $log->subject_id)
                                        {{ class_basename($log->subject_type) }} #{{ $log->subject_id }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="small d-none d-md-table-cell text-muted">{{ $log->ip_address ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No activity recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
            <div class="card-footer">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
@endsection
