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
                const option = document.createElement('option';
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
                const option = document.createElement('option';
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