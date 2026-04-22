@extends('layouts.app')

@section('title', 'Apply as an artisan')

@section('content')
<div class="container py-3 py-md-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h1 class="h2 fw-semibold mb-1">Apply to sell as an artisan</h1>
            <p class="text-muted mb-0">Fill in your workshop details. You can update the rest later in your artisan profile.</p>
        </div>
    </div>

    <div class="card border-0 shadow-soft-hover">
        <div class="card-body p-4 p-md-5">
            <form method="POST" action="{{ route('register.artisan.store') }}" enctype="multipart/form-data" novalidate>
                @csrf

                <div class="mb-3">
                    <label for="workshop_name" class="form-label">Workshop / business name <span class="text-danger">*</span></label>
                    <input id="workshop_name" type="text" class="form-control form-control-lg @error('workshop_name') is-invalid @enderror" name="workshop_name" value="{{ old('workshop_name') }}" required autofocus placeholder="e.g. Guihulngan Weaves">
                    @error('workshop_name')
                        <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="country" class="form-label">Country <span class="text-danger">*</span></label>
                        <select name="country" id="country" class="form-select @error('country') is-invalid @enderror" required>
                            <option value="">Select country</option>
                            <option value="Philippines" @selected(old('country', auth()->user()->country ?? 'Philippines') === 'Philippines')>Philippines</option>
                        </select>
                        @error('country')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="region" class="form-label">Region <span class="text-danger">*</span></label>
                        <select name="region" id="region" class="form-select @error('region') is-invalid @enderror" required>
                            <option value="">Select region</option>
                        </select>
                        @error('region')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="province" class="form-label">Province <span class="text-danger">*</span></label>
                        <select name="province" id="province" class="form-select @error('province') is-invalid @enderror" required disabled>
                            <option value="">Select province</option>
                        </select>
                        @error('province')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="city" class="form-label">City <span class="text-danger">*</span></label>
                        <select name="city" id="city" class="form-select @error('city') is-invalid @enderror" required disabled>
                            <option value="">Select city</option>
                        </select>
                        @error('city')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="barangay" class="form-label">Barangay <span class="text-danger">*</span></label>
                        <select name="barangay" id="barangay" class="form-select @error('barangay') is-invalid @enderror" required disabled>
                            <option value="">Select barangay</option>
                        </select>
                        @error('barangay')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-12 col-md-6">
                        <label for="street_address" class="form-label">Street, house no., landmarks <span class="text-muted fw-normal">(optional)</span></label>
                        <textarea id="street_address" name="street_address" class="form-control @error('street_address') is-invalid @enderror" rows="3" placeholder="e.g. 123 Mabini St.">{{ old('street_address', auth()->user()->street_address ?? '') }}</textarea>
                        @error('street_address')
                            <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mt-3">
                    <label for="id_photo" class="form-label">Government-issued ID photo <span class="text-danger">*</span></label>
                    <input
                        id="id_photo"
                        type="file"
                        class="form-control @error('id_photo') is-invalid @enderror"
                        name="id_photo"
                        accept="image/jpeg,image/png,image/jpg,image/webp"
                        required
                    >
                    <small class="text-muted d-block mt-2">JPG/PNG/WebP, up to 4MB. Make sure the name and photo are readable.</small>
                    @error('id_photo')
                        <span class="invalid-feedback d-block" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                <div class="d-flex flex-column flex-sm-row gap-2 gap-sm-3 mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        Submit application
                    </button>
                    <a href="{{ route('customer.dashboard') }}" class="btn btn-outline-secondary btn-lg">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    initLocationSelectors({
        savedCountry: @json(old('country', auth()->user()->country ?? 'Philippines')),
        savedRegion: @json(old('region', auth()->user()->region ?? '')),
        savedProvince: @json(old('province', auth()->user()->province ?? '')),
        savedCity: @json(old('city', auth()->user()->city ?? '')),
        savedBarangay: @json(old('barangay', auth()->user()->barangay ?? '')),
    });
});

function initLocationSelectors(options) {
    const countrySelect = document.getElementById('country');
    const regionSelect = document.getElementById('region');
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const barangaySelect = document.getElementById('barangay');

    if (!countrySelect || !regionSelect || !provinceSelect || !citySelect || !barangaySelect) {
        return;
    }

    countrySelect.value = options.savedCountry || 'Philippines';

    regionSelect.innerHTML = '<option value="">Select region</option>';
    provinceSelect.innerHTML = '<option value="">Select province</option>';
    citySelect.innerHTML = '<option value="">Select city</option>';
    barangaySelect.innerHTML = '<option value="">Select barangay</option>';
    provinceSelect.disabled = true;
    citySelect.disabled = true;
    barangaySelect.disabled = true;

    regionSelect.addEventListener('change', function () {
        resetSelect(provinceSelect, 'Select province');
        resetSelect(citySelect, 'Select city');
        resetSelect(barangaySelect, 'Select barangay');
        provinceSelect.disabled = true;
        citySelect.disabled = true;
        barangaySelect.disabled = true;

        if (this.value) {
            loadProvinces(this.value, options.savedProvince, options.savedCity, options.savedBarangay);
        }
    });

    provinceSelect.addEventListener('change', function () {
        resetSelect(citySelect, 'Select city');
        resetSelect(barangaySelect, 'Select barangay');
        citySelect.disabled = true;
        barangaySelect.disabled = true;

        if (this.value) {
            loadCities(this.value, options.savedCity, options.savedBarangay);
        }
    });

    citySelect.addEventListener('change', function () {
        resetSelect(barangaySelect, 'Select barangay');
        barangaySelect.disabled = true;

        if (this.value) {
            loadBarangays(this.value, options.savedBarangay);
        }
    });

    loadRegions().then(() => {
        if (options.savedRegion) {
            const selectedRegion = setSelectValue(regionSelect, options.savedRegion);
            if (selectedRegion) {
                loadProvinces(selectedRegion, options.savedProvince, options.savedCity, options.savedBarangay);
            }
        }
    });
}

function setSelectValue(select, value) {
    if (!value) {
        return null;
    }

    select.value = value;
    if (select.value === value.toString()) {
        return select.value;
    }

    const normalized = value.toString().trim().toLowerCase();
    const option = Array.from(select.options).find(opt => opt.textContent.trim().toLowerCase() === normalized);
    if (option) {
        select.value = option.value;
        return option.value;
    }

    return null;
}

function resetSelect(select, placeholder) {
    select.innerHTML = `<option value="">${placeholder}</option>`;
}

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

function loadProvinces(regionId, selectedProvince = null, selectedCity = null, selectedBarangay = null) {
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
                const selected = setSelectValue(select, selectedProvince);
                if (selected) {
                    loadCities(selected, selectedCity, selectedBarangay);
                }
            }
        })
        .catch(error => console.error('Error loading provinces:', error));
}

function loadCities(provinceId, selectedCity = null, selectedBarangay = null) {
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
                const selected = setSelectValue(select, selectedCity);
                if (selected) {
                    loadBarangays(selected, selectedBarangay);
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
                setSelectValue(select, selectedBarangay);
            }
        })
        .catch(error => console.error('Error loading barangays:', error));
}
</script>
@endpush

