/**
 * Philippine address cascades. Prefers server-passed bootstrap (no extra network).
 */

function hasBootstrapData(b) {
    return b && Array.isArray(b.regions) && b.regions.length > 0;
}

export function setSelectValue(select, value) {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    const s = value.toString();
    select.value = s;
    if (select.value === s) {
        return s;
    }

    const n = parseInt(s, 10);
    if (!Number.isNaN(n)) {
        const ns = String(n);
        select.value = ns;
        if (select.value === ns) {
            return select.value;
        }
    }

    const normalized = s.trim().toLowerCase();
    const option = Array.from(select.options).find(
        (o) => o.textContent.trim().toLowerCase() === normalized
    );
    if (option) {
        select.value = option.value;
        return option.value;
    }

    return null;
}

function resetSelectEl(select, placeholder) {
    select.innerHTML = `<option value="">${placeholder}</option>`;
}

function fillOptions(select, items, labelKey, valueKey, placeholder) {
    select.innerHTML = `<option value="">${placeholder}</option>`;
    for (const item of items) {
        const option = document.createElement('option');
        option.value = String(item[valueKey]);
        option.textContent = item[labelKey];
        select.appendChild(option);
    }
}

function buildApiUrl(path) {
    if (typeof window.__APP_BASE__ === 'string' && window.__APP_BASE__ !== '') {
        return `${window.__APP_BASE__.replace(/\/$/, '')}${path}`;
    }
    return path;
}

function loadRegionsFromApi() {
    return fetch(buildApiUrl('/api/regions'))
        .then((r) => {
            if (!r.ok) {
                throw new Error('regions');
            }
            return r.json();
        });
}

function loadProvincesFromApi(regionId) {
    return fetch(buildApiUrl(`/api/provinces/${regionId}`))
        .then((r) => {
            if (!r.ok) {
                throw new Error('provinces');
            }
            return r.json();
        });
}

function loadCitiesFromApi(provinceId) {
    return fetch(buildApiUrl(`/api/cities/${provinceId}`))
        .then((r) => {
            if (!r.ok) {
                throw new Error('cities');
            }
            return r.json();
        });
}

function loadBarangaysFromApi(cityId) {
    return fetch(buildApiUrl(`/api/barangays/${cityId}`))
        .then((r) => {
            if (!r.ok) {
                throw new Error('barangays');
            }
            return r.json();
        });
}

/**
 * @param {Object} options
 * @param {Object} [options.bootstrap] — { regions, provinces, cities, barangays }
 * @param {string} [options.savedCountry]
 * @param {string|number} [options.savedRegion]
 * @param {string|number} [options.savedProvince]
 * @param {string|number} [options.savedCity]
 * @param {string|number} [options.savedBarangay]
 * @param {boolean} [options.restoreOnLoad=true]
 */
export function initLocationSelectors(options) {
    const countrySelect = document.getElementById('country');
    const regionSelect = document.getElementById('region');
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const barangaySelect = document.getElementById('barangay');

    if (!regionSelect || !provinceSelect || !citySelect || !barangaySelect) {
        return;
    }

    const restoreOnLoad = options.restoreOnLoad !== false;
    const bootstrap = options.bootstrap;
    const mem = hasBootstrapData(bootstrap) ? bootstrap : null;

    if (countrySelect) {
        countrySelect.value = options.savedCountry || 'Philippines';
    }

    function seedDisabled() {
        resetSelectEl(provinceSelect, 'Select province');
        resetSelectEl(citySelect, 'Select city');
        resetSelectEl(barangaySelect, 'Select barangay');
        provinceSelect.disabled = true;
        citySelect.disabled = true;
        barangaySelect.disabled = true;
    }

    resetSelectEl(regionSelect, 'Select region');
    seedDisabled();

    function runRestoreAfterRegions() {
        if (!restoreOnLoad || !options.savedRegion) {
            return;
        }
        const selectedRegion = setSelectValue(regionSelect, options.savedRegion);
        if (selectedRegion) {
            loadProvinces(
                selectedRegion,
                options.savedProvince,
                options.savedCity,
                options.savedBarangay
            );
        }
    }

    function loadProvinces(regionId, selectedProvince, selectedCity, selectedBarangay) {
        if (mem) {
            const rid = parseInt(String(regionId), 10);
            const list = mem.provinces.filter(
                (p) => parseInt(String(p.region_id), 10) === rid
            );
            fillOptions(provinceSelect, list, 'name', 'id', 'Select province');
            provinceSelect.disabled = list.length === 0;

            if (selectedProvince) {
                const sel = setSelectValue(provinceSelect, selectedProvince);
                if (sel) {
                    loadCities(sel, selectedCity, selectedBarangay);
                }
            }
            return Promise.resolve();
        }
        return loadProvincesFromApi(regionId)
            .then((data) => {
                fillOptions(provinceSelect, data, 'name', 'id', 'Select province');
                provinceSelect.disabled = data.length === 0;

                if (selectedProvince) {
                    const sel = setSelectValue(provinceSelect, selectedProvince);
                    if (sel) {
                        loadCities(sel, selectedCity, selectedBarangay);
                    }
                }
            })
            .catch((e) => {
                console.error('Error loading provinces:', e);
            });
    }

    function loadCities(provinceId, selectedCity, selectedBarangay) {
        if (mem) {
            const pid = parseInt(String(provinceId), 10);
            const list = mem.cities.filter(
                (c) => parseInt(String(c.province_id), 10) === pid
            );
            fillOptions(citySelect, list, 'name', 'id', 'Select city');
            citySelect.disabled = list.length === 0;

            if (selectedCity) {
                const sel = setSelectValue(citySelect, selectedCity);
                if (sel) {
                    loadBarangays(sel, selectedBarangay);
                }
            }
            return Promise.resolve();
        }
        return loadCitiesFromApi(provinceId)
            .then((data) => {
                fillOptions(citySelect, data, 'name', 'id', 'Select city');
                citySelect.disabled = data.length === 0;

                if (selectedCity) {
                    const sel = setSelectValue(citySelect, selectedCity);
                    if (sel) {
                        loadBarangays(sel, selectedBarangay);
                    }
                }
            })
            .catch((e) => {
                console.error('Error loading cities:', e);
            });
    }

    function loadBarangays(cityId, selectedBarangay) {
        if (mem) {
            const cid = parseInt(String(cityId), 10);
            const list = mem.barangays.filter(
                (b) => parseInt(String(b.city_id), 10) === cid
            );
            fillOptions(barangaySelect, list, 'name', 'id', 'Select barangay');
            barangaySelect.disabled = list.length === 0;

            if (selectedBarangay) {
                setSelectValue(barangaySelect, selectedBarangay);
            }
            return Promise.resolve();
        }
        return loadBarangaysFromApi(cityId)
            .then((data) => {
                fillOptions(barangaySelect, data, 'name', 'id', 'Select barangay');
                barangaySelect.disabled = data.length === 0;

                if (selectedBarangay) {
                    setSelectValue(barangaySelect, selectedBarangay);
                }
            })
            .catch((e) => {
                console.error('Error loading barangays:', e);
            });
    }

    regionSelect.addEventListener('change', function () {
        if (!this.value) {
            seedDisabled();
            return;
        }
        resetSelectEl(citySelect, 'Select city');
        resetSelectEl(barangaySelect, 'Select barangay');
        citySelect.disabled = true;
        barangaySelect.disabled = true;
        loadProvinces(this.value, null, null, null);
    });

    provinceSelect.addEventListener('change', function () {
        resetSelectEl(citySelect, 'Select city');
        resetSelectEl(barangaySelect, 'Select barangay');
        citySelect.disabled = true;
        barangaySelect.disabled = true;
        if (!this.value) {
            return;
        }
        loadCities(this.value, null, null);
    });

    citySelect.addEventListener('change', function () {
        resetSelectEl(barangaySelect, 'Select barangay');
        barangaySelect.disabled = true;
        if (!this.value) {
            return;
        }
        loadBarangays(this.value, null);
    });

    if (mem) {
        fillOptions(regionSelect, mem.regions, 'name', 'id', 'Select region');
        runRestoreAfterRegions();
    } else {
        loadRegionsFromApi()
            .then((data) => {
                fillOptions(regionSelect, data, 'name', 'id', 'Select region');
                runRestoreAfterRegions();
            })
            .catch((e) => {
                console.error('Error loading regions:', e);
            });
    }

    const locForm = barangaySelect.closest('form');
    if (locForm) {
        locForm.addEventListener('submit', function () {
            [regionSelect, provinceSelect, citySelect, barangaySelect].forEach((el) => {
                if (el) {
                    el.disabled = false;
                }
            });
        });
    }
}

/**
 * Checkout: optional "use saved" + same cascade (bootstrap preferred).
 * Location POST values live in hidden inputs (#co_*) so disabled <select> never
 * omits region/city/barangay from the request; selects are display-only.
 */
export function initCheckoutAddressForm(options) {
    const {
        bootstrap,
        savedForButton = {},
    } = options;
    const regionSelect = document.getElementById('region');
    const provinceSelect = document.getElementById('province');
    const citySelect = document.getElementById('city');
    const barangaySelect = document.getElementById('barangay');
    const hRegion = document.getElementById('co_region');
    const hProvince = document.getElementById('co_province');
    const hCity = document.getElementById('co_city');
    const hBarangay = document.getElementById('co_barangay');
    if (!regionSelect || !provinceSelect || !citySelect || !barangaySelect) {
        return;
    }
    if (!hRegion || !hProvince || !hCity || !hBarangay) {
        return;
    }

    const mem = hasBootstrapData(bootstrap) ? bootstrap : null;

    function syncCoAddressToHidden() {
        hRegion.value = regionSelect.value || '';
        hProvince.value = provinceSelect.value || '';
        hCity.value = citySelect.value || '';
        hBarangay.value = barangaySelect.value || '';
    }

    function runRestoreFromOldOrHidden() {
        const v = hRegion.value;
        if (!v) {
            return;
        }
        const regionId = setSelectValue(regionSelect, v);
        if (!regionId) {
            return;
        }
        const p = hProvince.value || null;
        const c = hCity.value || null;
        const b = hBarangay.value || null;
        loadProvinces(regionId, p, c, b);
    }

    function loadProvinces(regionId, afterProvince, afterCity, afterBarangay) {
        if (mem) {
            const rid = parseInt(String(regionId), 10);
            const list = mem.provinces.filter(
                (p) => parseInt(String(p.region_id), 10) === rid
            );
            fillOptions(provinceSelect, list, 'name', 'id', 'Select province');
            provinceSelect.disabled = list.length === 0;
            if (afterProvince) {
                const sel = setSelectValue(provinceSelect, afterProvince);
                if (sel) {
                    loadCities(sel, afterCity, afterBarangay);
                } else {
                    syncCoAddressToHidden();
                }
            } else {
                syncCoAddressToHidden();
            }
            return;
        }
        loadProvincesFromApi(regionId)
            .then((data) => {
                fillOptions(provinceSelect, data, 'name', 'id', 'Select province');
                provinceSelect.disabled = data.length === 0;
                if (afterProvince) {
                    const sel = setSelectValue(provinceSelect, afterProvince);
                    if (sel) {
                        loadCities(sel, afterCity, afterBarangay);
                    } else {
                        syncCoAddressToHidden();
                    }
                } else {
                    syncCoAddressToHidden();
                }
            })
            .catch((e) => console.error('Error loading provinces:', e));
    }

    function loadCities(provinceId, afterCity, afterBarangay) {
        if (mem) {
            const pid = parseInt(String(provinceId), 10);
            const list = mem.cities.filter(
                (c) => parseInt(String(c.province_id), 10) === pid
            );
            fillOptions(citySelect, list, 'name', 'id', 'Select city');
            citySelect.disabled = list.length === 0;
            if (afterCity) {
                const sel = setSelectValue(citySelect, afterCity);
                if (sel) {
                    loadBarangays(sel, afterBarangay);
                } else {
                    syncCoAddressToHidden();
                }
            } else {
                syncCoAddressToHidden();
            }
            return;
        }
        loadCitiesFromApi(provinceId)
            .then((data) => {
                fillOptions(citySelect, data, 'name', 'id', 'Select city');
                citySelect.disabled = data.length === 0;
                if (afterCity) {
                    const sel = setSelectValue(citySelect, afterCity);
                    if (sel) {
                        loadBarangays(sel, afterBarangay);
                    } else {
                        syncCoAddressToHidden();
                    }
                } else {
                    syncCoAddressToHidden();
                }
            })
            .catch((e) => console.error('Error loading cities:', e));
    }

    function loadBarangays(cityId, afterBarangay) {
        if (mem) {
            const cid = parseInt(String(cityId), 10);
            const list = mem.barangays.filter(
                (b) => parseInt(String(b.city_id), 10) === cid
            );
            fillOptions(barangaySelect, list, 'name', 'id', 'Select barangay');
            barangaySelect.disabled = list.length === 0;
            if (afterBarangay) {
                setSelectValue(barangaySelect, afterBarangay);
            }
            syncCoAddressToHidden();
            return;
        }
        loadBarangaysFromApi(cityId)
            .then((data) => {
                fillOptions(barangaySelect, data, 'name', 'id', 'Select barangay');
                barangaySelect.disabled = data.length === 0;
                if (afterBarangay) {
                    setSelectValue(barangaySelect, afterBarangay);
                }
                syncCoAddressToHidden();
            })
            .catch((e) => console.error('Error loading barangays:', e));
    }

    function resetCascading(which) {
        if (which === 'region' || which === 'all') {
            resetSelectEl(provinceSelect, 'Select province');
            resetSelectEl(citySelect, 'Select city');
            resetSelectEl(barangaySelect, 'Select barangay');
            provinceSelect.disabled = true;
            citySelect.disabled = true;
            barangaySelect.disabled = true;
        } else if (which === 'province') {
            resetSelectEl(citySelect, 'Select city');
            resetSelectEl(barangaySelect, 'Select barangay');
            citySelect.disabled = true;
            barangaySelect.disabled = true;
        } else if (which === 'city') {
            resetSelectEl(barangaySelect, 'Select barangay');
            barangaySelect.disabled = true;
        }
    }

    regionSelect.addEventListener('change', function () {
        const id = this.value;
        if (id) {
            resetCascading('all');
            loadProvinces(id, null, null, null);
        } else {
            resetCascading('all');
        }
        syncCoAddressToHidden();
    });

    provinceSelect.addEventListener('change', function () {
        const id = this.value;
        if (id) {
            resetCascading('province');
            loadCities(id, null, null);
        } else {
            resetCascading('province');
        }
        syncCoAddressToHidden();
    });

    citySelect.addEventListener('change', function () {
        const id = this.value;
        if (id) {
            resetCascading('city');
            loadBarangays(id, null);
        } else {
            resetCascading('city');
        }
        syncCoAddressToHidden();
    });

    barangaySelect.addEventListener('change', syncCoAddressToHidden);

    if (mem) {
        fillOptions(regionSelect, mem.regions, 'name', 'id', 'Select region');
        runRestoreFromOldOrHidden();
    } else {
        loadRegionsFromApi()
            .then((data) => {
                fillOptions(regionSelect, data, 'name', 'id', 'Select region');
                runRestoreFromOldOrHidden();
            })
            .catch((e) => console.error('Error loading regions:', e));
    }

    const btn = options.useSavedButtonId
        ? document.getElementById(options.useSavedButtonId)
        : null;
    if (btn) {
        btn.addEventListener('click', function () {
            const s = savedForButton;
            const r = s.region;
            if (!r) {
                return;
            }
            const streetEl = options.streetFieldId
                ? document.getElementById(options.streetFieldId)
                : null;
            const phoneEl = options.phoneFieldId
                ? document.getElementById(options.phoneFieldId)
                : null;
            if (streetEl && s.street !== undefined) {
                streetEl.value = s.street || '';
            }
            if (phoneEl && s.phone !== undefined) {
                phoneEl.value = s.phone || '';
            }

            const regionId = setSelectValue(regionSelect, r);
            if (regionId) {
                loadProvinces(regionId, s.province, s.city, s.barangay);
            }
        });
    }

    const form = barangaySelect.closest('form');
    if (form) {
        form.addEventListener(
            'submit',
            function () {
                syncCoAddressToHidden();
            },
            true
        );
    }
}

/**
 * Guihulngan-only checkout: city/region/province are fixed server-side; only barangay is chosen.
 */
export function initGuihulnganCheckoutForm(options) {
    const {
        useSavedButtonId,
        barangaySelectId = 'barangay',
        streetFieldId = 'street_address',
        phoneFieldId = 'phone',
        saved = {},
    } = options;

    const barangaySelect = document.getElementById(barangaySelectId);
    if (!barangaySelect) {
        return;
    }

    const btn = useSavedButtonId ? document.getElementById(useSavedButtonId) : null;
    if (btn) {
        btn.addEventListener('click', function () {
            const s = saved;
            const streetEl = streetFieldId
                ? document.getElementById(streetFieldId)
                : null;
            const phoneEl = phoneFieldId
                ? document.getElementById(phoneFieldId)
                : null;
            if (streetEl && s.street !== undefined) {
                streetEl.value = s.street || '';
            }
            if (phoneEl && s.phone !== undefined) {
                phoneEl.value = s.phone || '';
            }
            if (s.barangay !== undefined && s.barangay !== null && s.barangay !== '') {
                setSelectValue(barangaySelect, s.barangay);
            }
        });
    }
}
