@extends('layouts.app')

@section('title', 'Riders')

@section('content')
<div class="container py-2 py-md-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0">Rider Management</h1>
    </div>

    <div class="card mb-3">
        <div class="card-header"><strong>Add rider</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.riders.store') }}" class="row g-2">
                @csrf
                <div class="col-md-3"><input name="full_name" class="form-control" placeholder="Full name" required></div>
                <div class="col-md-2"><input name="contact_number" class="form-control" placeholder="Contact number" required></div>
                <div class="col-md-3"><input name="email" type="email" class="form-control" placeholder="Email" required></div>
                <div class="col-md-2"><input name="vehicle_type" class="form-control" placeholder="Vehicle type"></div>
                <div class="col-md-2">
                    <select name="status" class="form-select" required>
                        <option value="available">Available</option>
                        <option value="busy">Busy</option>
                        <option value="offline">Offline</option>
                    </select>
                </div>
                <div class="col-md-8"><input name="address" class="form-control" placeholder="Address"></div>
                <div class="col-md-2"><input name="password" type="password" class="form-control" placeholder="Password" required></div>
                <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Create rider</button></div>
            </form>
        </div>
    </div>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-3">
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach(['available' => 'Available', 'busy' => 'Busy', 'offline' => 'Offline'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Rider ID</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Vehicle</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riders as $rider)
                        <tr>
                            <td>{{ $rider->rider_id }}</td>
                            <td>{{ $rider->full_name }}</td>
                            <td>{{ $rider->contact_number }}</td>
                            <td>{{ $rider->email }}</td>
                            <td>{{ $rider->vehicle_type ?? '—' }}</td>
                            <td><x-status-badge :status="$rider->status" type="delivery" /></td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#edit-rider-{{ $rider->id }}">Edit</button>
                                @if($rider->status === 'offline')
                                    <form action="{{ route('admin.riders.activate', $rider) }}" method="POST" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-success">Activate</button></form>
                                @else
                                    <form action="{{ route('admin.riders.deactivate', $rider) }}" method="POST" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-warning">Deactivate</button></form>
                                @endif
                            </td>
                        </tr>
                        <tr class="collapse" id="edit-rider-{{ $rider->id }}">
                            <td colspan="7">
                                <form method="POST" action="{{ route('admin.riders.update', $rider) }}" class="row g-2">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-md-3"><input name="full_name" class="form-control" value="{{ $rider->full_name }}" required></div>
                                    <div class="col-md-2"><input name="contact_number" class="form-control" value="{{ $rider->contact_number }}" required></div>
                                    <div class="col-md-3"><input name="email" type="email" class="form-control" value="{{ $rider->email }}" required></div>
                                    <div class="col-md-2"><input name="vehicle_type" class="form-control" value="{{ $rider->vehicle_type }}"></div>
                                    <div class="col-md-2">
                                        <select name="status" class="form-select" required>
                                            <option value="available" @selected($rider->status === 'available')>Available</option>
                                            <option value="busy" @selected($rider->status === 'busy')>Busy</option>
                                            <option value="offline" @selected($rider->status === 'offline')>Offline</option>
                                        </select>
                                    </div>
                                    <div class="col-md-10"><input name="address" class="form-control" value="{{ $rider->address }}" placeholder="Address"></div>
                                    <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Save</button></div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No riders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">{{ $riders->links() }}</div>
</div>
@endsection
