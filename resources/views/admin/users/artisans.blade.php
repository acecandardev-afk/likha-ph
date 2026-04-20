@extends('layouts.app')

@section('title', 'Artisans')

@section('content')
<div class="container py-2 py-md-3">
    <nav aria-label="Breadcrumb" class="mb-3">
        <ol class="breadcrumb small mb-0">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
            <li class="breadcrumb-item active" aria-current="page">Artisans</li>
        </ol>
    </nav>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <h1 class="h2 fw-semibold mb-0">Artisans</h1>
        <a href="{{ route('admin.users.customers') }}" class="btn btn-outline-primary btn-sm">View customers</a>
    </div>

    @if($artisans->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-person-workspace text-muted" style="font-size: 3rem;"></i>
                <p class="mt-3 mb-0 text-muted">No artisans registered.</p>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Workshop</th>
                                <th>ID</th>
                                <th>Products</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($artisans as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->artisanProfile?->workshop_name ?? '—' }}</td>
                                    <td>
                                        @if($user->artisanProfile?->id_photo_url)
                                            <a class="btn btn-sm btn-outline-primary"
                                               href="{{ $user->artisanProfile->id_photo_url }}"
                                               target="_blank"
                                               rel="noopener noreferrer">
                                                View
                                            </a>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->products_count }}</td>
                                    <td>
                                        @if($user->status === 'pending')
                                            <span class="badge bg-warning text-dark">Pending review</span>
                                        @elseif($user->status === 'suspended')
                                            <span class="badge bg-danger">Suspended</span>
                                        @else
                                            <span class="badge bg-success">Active</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            @if($user->status === 'pending')
                                                <form action="{{ route('admin.users.activate', $user) }}" method="POST"
                                                      data-artisan-confirm-title="Approve artisan"
                                                      data-artisan-confirm="Approve this artisan?"
                                                      class="artisan-confirm-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                                </form>
                                                <form action="{{ route('admin.users.suspend', $user) }}" method="POST"
                                                      data-artisan-confirm-title="Reject artisan"
                                                      data-artisan-confirm="Reject this artisan?"
                                                      class="artisan-confirm-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                                </form>
                                            @elseif($user->status === 'suspended')
                                                <form action="{{ route('admin.users.activate', $user) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-success">Activate</button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.users.suspend', $user) }}" method="POST"
                                                      data-artisan-confirm-title="Suspend artisan"
                                                      data-artisan-confirm="Suspend this artisan?"
                                                      class="artisan-confirm-form">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-warning">Suspend</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-3">{{ $artisans->links() }}</div>
    @endif
</div>
{{-- Bootstrap confirm modal for approve/reject/suspend actions --}}
<div class="modal fade" id="artisanConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="artisanConfirmModalText" class="mb-0 text-muted"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="artisanConfirmModalOk">OK</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const modalEl = document.getElementById('artisanConfirmModal');
            if (!modalEl || !window.bootstrap) return;

            const okBtn = document.getElementById('artisanConfirmModalOk');
            const textEl = document.getElementById('artisanConfirmModalText');
            const modal = new bootstrap.Modal(modalEl);

            let formToSubmit = null;

            document.querySelectorAll('form.artisan-confirm-form[data-artisan-confirm]').forEach((form) => {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    formToSubmit = form;

                    const title = form.getAttribute('data-artisan-confirm-title') || 'Confirm action';
                    const msg = form.getAttribute('data-artisan-confirm') || 'Are you sure?';

                    const titleEl = modalEl.querySelector('.modal-title');
                    if (titleEl) titleEl.textContent = title;
                    if (textEl) textEl.textContent = msg;

                    modal.show();
                });
            });

            okBtn?.addEventListener('click', () => {
                if (!formToSubmit) return;
                modal.hide();
                formToSubmit.submit();
            });

            modalEl.addEventListener('hidden.bs.modal', () => {
                formToSubmit = null;
            });
        });
    </script>
@endpush
@endsection
