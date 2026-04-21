@extends('layouts.app')

@section('title', 'Shipping address')

@section('content')
<div class="container py-2 py-md-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <x-profile-header-nav active="shipping" />
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="rounded-3 d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary" style="width: 48px; height: 48px;">
                    <i class="bi bi-geo-alt"></i>
                </div>
                <div>
                    <h1 class="h2 fw-semibold mb-1">Shipping address</h1>
                    <p class="text-body-secondary small mb-0">Set your default delivery address. It will be used to auto-fill during checkout.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('account.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Country (Fixed to Philippines) -->
                        <div class="mb-3">
                            <label for="country" class="form-label fw-medium">Country</label>
                            <input type="text" class="form-control" value="Philippines" readonly>
                            <input type="hidden" name="country" value="Philippines">
                        </div>

                        <!-- Region -->
                        <div class="mb-3">
                            <label for="region" class="form-label fw-medium">Region</label>
                            <select name="region" id="region" class="form-select @error('region') is-invalid @enderror">
                                <option value="">Select region</option>
                            </select>
                            @error('region')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Province -->
                        <div class="mb-3">
                            <label for="province" class="form-label fw-medium">Province</label>
                            <select name="province" id="province" class="form-select @error('province') is-invalid @enderror" disabled>
                                <option value="">Select province</option>
                            </select>
                            @error('province')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- City -->
                        <div class="mb-3">
                            <label for="city" class="form-label fw-medium">City</label>
                            <select name="city" id="city" class="form-select @error('city') is-invalid @enderror" disabled>
                                <option value="">Select city</option>
                            </select>
                            @error('city')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Barangay -->
                        <div class="mb-3">
                            <label for="barangay" class="form-label fw-medium">Barangay</label>
                            <select name="barangay" id="barangay" class="form-select @error('barangay') is-invalid @enderror" disabled>
                                <option value="">Select barangay</option>
                            </select>
                            @error('barangay')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Street Address -->
                        <div class="mb-3">
                            <label for="street_address" class="form-label fw-medium">Street, house no., landmarks</label>
                            <textarea name="street_address" id="street_address" rows="3" class="form-control @error('street_address') is-invalid @enderror" placeholder="Optional details to help couriers find you">{{ old('street_address', $user->street_address) }}</textarea>
                            <small class="text-muted">Leave blank if you prefer to enter everything at checkout.</small>
                            @error('street_address')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="mb-4">
                            <label for="phone" class="form-label fw-medium">Contact number for delivery</label>
                            <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" placeholder="09XXXXXXXXX or +63XXXXXXXXXXX">
                            @error('phone')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i> Save
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const savedRegion = @json($user->region ?? '');
    const savedProvince = @json($user->province ?? '');
    const savedCity = @json($user->city ?? '');
    const savedBarangay = @json($user->barangay ?? '');

    // Load regions on page load and restore saved selections.
    loadRegions().then(() => {
        populateSavedAddress();
    });

    function selectOptionByText(select, text) {
        if (!text) {
            return null;
        }

        const normalized = text.toString().trim().toLowerCase();
        const option = Array.from(select.options).find(opt => opt.textContent.trim().toLowerCase() === normalized);

        if (option) {
            select.value = option.value;
            return option.value;
        }

        return null;
    }

    function populateSavedAddress() {
        if (!savedRegion) {
            return;
        }

        const regionSelect = document.getElementById('region');
        const regionId = selectOptionByText(regionSelect, savedRegion);

        if (regionId) {
            loadProvinces(regionId, savedProvince);
        }
    }

    // Event listeners for cascading dropdowns
    document.getElementById('region').addEventListener('change', function() {
        const regionId = this.value;
        if (regionId) {
            resetSelect('province');
            resetSelect('city');
            resetSelect('barangay');
            loadProvinces(regionId);
        } else {
            resetSelect('province', true);
            resetSelect('city', true);
            resetSelect('barangay', true);
        }
    });

    document.getElementById('province').addEventListener('change', function() {
        const provinceId = this.value;
        if (provinceId) {
            resetSelect('city');
            resetSelect('barangay');
            loadCities(provinceId);
        } else {
            resetSelect('city', true);
            resetSelect('barangay', true);
        }
    });

    document.getElementById('city').addEventListener('change', function() {
        const cityId = this.value;
        if (cityId) {
            resetSelect('barangay');
            loadBarangays(cityId);
        } else {
            resetSelect('barangay', true);
        }
    });

    function loadRegions() {
        return fetch('/api/regions')
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('region');
                select.innerHTML = '<option value="">Select region</option>';
                data.forEach(region => {
                    const option = document.createElement('option');
                    option.value = region.id;
                    option.textContent = region.name;
                    select.appendChild(option);
                });
            })
            .catch(error => console.error('Error loading regions:', error));
    }

    function loadProvinces(regionId, selectedProvince = null) {
        return fetch(`/api/provinces/${regionId}`)
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('province');
                select.innerHTML = '<option value="">Select province</option>';
                data.forEach(province => {
                    const option = document.createElement('option');
                    option.value = province.id;
                    option.textContent = province.name;
                    select.appendChild(option);
                });
                select.disabled = false;

                if (selectedProvince) {
                    const provinceId = selectOptionByText(select, selectedProvince);
                    if (provinceId) {
                        loadCities(provinceId, savedCity);
                    }
                }
            })
            .catch(error => console.error('Error loading provinces:', error));
    }

    function loadCities(provinceId, selectedCity = null) {
        return fetch(`/api/cities/${provinceId}`)
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('city');
                select.innerHTML = '<option value="">Select city</option>';
                data.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.id;
                    option.textContent = city.name;
                    select.appendChild(option);
                });
                select.disabled = false;

                if (selectedCity) {
                    const cityId = selectOptionByText(select, selectedCity);
                    if (cityId) {
                        loadBarangays(cityId, savedBarangay);
                    }
                }
            })
            .catch(error => console.error('Error loading cities:', error));
    }

    function loadBarangays(cityId, selectedBarangay = null) {
        return fetch(`/api/barangays/${cityId}`)
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('barangay');
                select.innerHTML = '<option value="">Select barangay</option>';
                data.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay.id;
                    option.textContent = barangay.name;
                    select.appendChild(option);
                });
                select.disabled = false;

                if (selectedBarangay) {
                    selectOptionByText(select, selectedBarangay);
                }
            })
            .catch(error => console.error('Error loading barangays:', error));
    }

    function resetSelect(selectId, disable = false) {
        const select = document.getElementById(selectId);
        select.innerHTML = `<option value="">Select ${selectId}</option>`;
        if (disable) {
            select.disabled = true;
        }
    }
});
</script>
@endpush
@endsection
