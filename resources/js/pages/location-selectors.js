// Global variables to store location data
let allRegions = [];
let allProvinces = [];
let allCities = [];
let allBarangays = [];

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
            populateProvinces(this.value, options.savedProvince, options.savedCity, options.savedBarangay);
        }
    });

    provinceSelect.addEventListener('change', function () {
        resetSelect(citySelect, 'Select city');
        resetSelect(barangaySelect, 'Select barangay');
        citySelect.disabled = true;
        barangaySelect.disabled = true;

        if (this.value) {
            populateCities(this.value, options.savedCity, options.savedBarangay);
        }
    });

    citySelect.addEventListener('change', function () {
        resetSelect(barangaySelect, 'Select barangay');
        barangaySelect.disabled = true;

        if (this.value) {
            populateBarangays(this.value, options.savedBarangay);
        }
    });

    // Load all location data upfront
    loadAllLocationData().then(() => {
        populateRegions();
        if (options.savedRegion) {
            const selectedRegion = setSelectValue(regionSelect, options.savedRegion);
            if (selectedRegion) {
                populateProvinces(selectedRegion, options.savedProvince, options.savedCity, options.savedBarangay);
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

function loadAllLocationData() {
    return Promise.all([
        fetch('/api/regions').then(response => response.json()).then(data => allRegions = data),
        fetch('/api/provinces').then(response => response.json()).then(data => allProvinces = data),
        fetch('/api/cities').then(response => response.json()).then(data => allCities = data),
        fetch('/api/barangays').then(response => response.json()).then(data => allBarangays = data)
    ]).catch(error => console.error('Error loading location data:', error));
}

function populateRegions() {
    const select = document.getElementById('region');
    select.innerHTML = '<option value="">Select region</option>';
    allRegions.forEach(region => {
        const option = document.createElement('option');
        option.value = region.id;
        option.textContent = region.name;
        select.appendChild(option);
    });
}

function populateProvinces(regionId, selectedProvince = null, selectedCity = null, selectedBarangay = null) {
    const select = document.getElementById('province');
    select.innerHTML = '<option value="">Select province</option>';

    const regionProvinces = allProvinces.filter(province => province.region_id == regionId);
    regionProvinces.forEach(province => {
        const option = document.createElement('option');
        option.value = province.id;
        option.textContent = province.name;
        select.appendChild(option);
    });
    select.disabled = false;

    if (selectedProvince) {
        const selected = setSelectValue(select, selectedProvince);
        if (selected) {
            populateCities(selected, selectedCity, selectedBarangay);
        }
    }
}

function populateCities(provinceId, selectedCity = null, selectedBarangay = null) {
    const select = document.getElementById('city');
    select.innerHTML = '<option value="">Select city</option>';

    const provinceCities = allCities.filter(city => city.province_id == provinceId);
    provinceCities.forEach(city => {
        const option = document.createElement('option');
        option.value = city.id;
        option.textContent = city.name;
        select.appendChild(option);
    });
    select.disabled = false;

    if (selectedCity) {
        const selected = setSelectValue(select, selectedCity);
        if (selected) {
            populateBarangays(selected, selectedBarangay);
        }
    }
}

function populateBarangays(cityId, selectedBarangay = null) {
    const select = document.getElementById('barangay');
    select.innerHTML = '<option value="">Select barangay</option>';

    const cityBarangays = allBarangays.filter(barangay => barangay.city_id == cityId);
    cityBarangays.forEach(barangay => {
        const option = document.createElement('option');
        option.value = barangay.id;
        option.textContent = barangay.name;
        select.appendChild(option);
    });
    select.disabled = false;

    if (selectedBarangay) {
        setSelectValue(select, selectedBarangay);
    }
}