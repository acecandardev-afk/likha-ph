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
            <form method="POST" action="{{ route('admin.riders.store') }}" enctype="multipart/form-data" class="row g-2 align-items-end">
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
                <div class="col-md-2"><input name="birth_date" type="date" class="form-control" title="Birth date"></div>
                <div class="col-md-3"><input name="emergency_contact_name" class="form-control" placeholder="Emergency contact name"></div>
                <div class="col-md-3"><input name="emergency_contact_phone" class="form-control" placeholder="Emergency phone"></div>
                <div class="col-md-2"><input name="license_number" class="form-control" placeholder="License no."></div>
                <div class="col-md-2"><input name="license_expiry" class="form-control" placeholder="License expiry"></div>
                <div class="col-md-2"><input name="vehicle_plate" class="form-control" placeholder="Plate no."></div>
                <div class="col-12"><textarea name="bio" class="form-control" rows="2" placeholder="Short bio / notes"></textarea></div>
                <div class="col-md-4"><label class="form-label small mb-0">License photo</label><input type="file" name="license_image" class="form-control form-control-sm" accept="image/*"></div>
                <div class="col-md-4"><label class="form-label small mb-0">ID document</label><input type="file" name="id_document_image" class="form-control form-control-sm" accept="image/*"></div>
                <div class="col-md-4"><label class="form-label small mb-0">Clearance</label><input type="file" name="clearance_document_image" class="form-control form-control-sm" accept="image/*"></div>
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
                                <form method="POST" action="{{ route('admin.riders.update', $rider) }}" enctype="multipart/form-data" class="row g-2">
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
                                    <div class="col-md-2"><input name="birth_date" type="date" class="form-control" value="{{ $rider->birth_date?->format('Y-m-d') }}"></div>
                                    <div class="col-md-3"><input name="emergency_contact_name" class="form-control" value="{{ $rider->emergency_contact_name }}" placeholder="Emergency contact"></div>
                                    <div class="col-md-3"><input name="emergency_contact_phone" class="form-control" value="{{ $rider->emergency_contact_phone }}" placeholder="Emergency phone"></div>
                                    <div class="col-md-2"><input name="license_number" class="form-control" value="{{ $rider->license_number }}" placeholder="License no."></div>
                                    <div class="col-md-2"><input name="license_expiry" class="form-control" value="{{ $rider->license_expiry }}" placeholder="License expiry"></div>
                                    <div class="col-md-2"><input name="vehicle_plate" class="form-control" value="{{ $rider->vehicle_plate }}" placeholder="Plate"></div>
                                    <div class="col-12"><textarea name="bio" class="form-control" rows="2" placeholder="Bio">{{ $rider->bio }}</textarea></div>
                                    <div class="col-md-4"><label class="form-label small mb-0">License photo @if($rider->license_image)<a href="{{ asset('storage/'.$rider->license_image) }}" target="_blank" rel="noopener">current</a>@endif</label><input type="file" name="license_image" class="form-control form-control-sm" accept="image/*"></div>
                                    <div class="col-md-4"><label class="form-label small mb-0">ID @if($rider->id_document_image)<a href="{{ asset('storage/'.$rider->id_document_image) }}" target="_blank" rel="noopener">current</a>@endif</label><input type="file" name="id_document_image" class="form-control form-control-sm" accept="image/*"></div>
                                    <div class="col-md-4"><label class="form-label small mb-0">Clearance @if($rider->clearance_document_image)<a href="{{ asset('storage/'.$rider->clearance_document_image) }}" target="_blank" rel="noopener">current</a>@endif</label><input type="file" name="clearance_document_image" class="form-control form-control-sm" accept="image/*"></div>
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
