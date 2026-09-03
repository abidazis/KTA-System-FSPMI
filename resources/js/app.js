
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function ajaxGet(url, onSuccess, onError) {
    console.log('[AJAX] Requesting:', url);
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.withCredentials = true;
    xhr.setRequestHeader('X-CSRF-TOKEN', getCsrfToken());
    xhr.onload = function() {
        console.log('[AJAX] Response status:', xhr.status);
        console.log('[AJAX] Response body:', xhr.responseText);
        if (xhr.status === 200) {
            onSuccess(JSON.parse(xhr.responseText));
        } else {
            onError('HTTP ' + xhr.status);
        }
    };
    xhr.onerror = function() {
        console.error('[AJAX] Network error for:', url);
        onError('Network error');
    };
    xhr.send();
}

// wilayah dropdown functionality - dipisah agar lebih modular
document.addEventListener('DOMContentLoaded', function() {
    initWilayahDropdowns();
    initBulkActions();
    initPhotoPreview();
    initInlineStatusChange();
});

function initWilayahDropdowns() {
    var provinceSelect = document.getElementById('province_id');
    var regencySelect = document.getElementById('regency_id');
    var districtSelect = document.getElementById('district_id');

    // Skip kalau elemen tidak ada
    if (!provinceSelect) return;

    // Initialize filter dropdowns if exists
    var filterProvince = document.getElementById('filter-province');
    var filterRegency = document.getElementById('filter-regency');
    var filterDistrict = document.getElementById('filter-district');

    // Helper function untuk load regencies
    function loadRegencies(targetRegencySelect, targetDistrictSelect, provinceId, selectedId) {
        if (!provinceId) {
            if (targetRegencySelect) {
                targetRegencySelect.innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';
                targetRegencySelect.disabled = true;
            }
            if (targetDistrictSelect) {
                targetDistrictSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
                targetDistrictSelect.disabled = true;
            }
            return;
        }

        if (targetRegencySelect) {
            targetRegencySelect.innerHTML = '<option value="">Memuat...</option>';
            targetRegencySelect.disabled = true;
        }

        ajaxGet(
            '/api/regencies?province_id=' + provinceId,
            function(data) {
                if (!targetRegencySelect) return;
                targetRegencySelect.innerHTML = '<option value="">-- Pilih Kabupaten/Kota --</option>';
                for (var i = 0; i < data.length; i++) {
                    var sel = (selectedId && selectedId == data[i].id) ? ' selected' : '';
                    targetRegencySelect.innerHTML += '<option value="' + data[i].id + '"' + sel + '>' + data[i].name + '</option>';
                }
                targetRegencySelect.disabled = false;

                if (targetDistrictSelect) {
                    targetDistrictSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
                    targetDistrictSelect.disabled = true;
                }
            },
            function(error) {
                console.error('Error loading regencies:', error);
                if (targetRegencySelect) {
                    targetRegencySelect.innerHTML = '<option value="">Gagal memuat - coba lagi</option>';
                }
            }
        );
    }

    // Helper function untuk load districts
    function loadDistricts(targetDistrictSelect, regencyId, selectedId) {
        if (!regencyId) {
            if (targetDistrictSelect) {
                targetDistrictSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
                targetDistrictSelect.disabled = true;
            }
            return;
        }

        if (targetDistrictSelect) {
            targetDistrictSelect.innerHTML = '<option value="">Memuat...</option>';
            targetDistrictSelect.disabled = true;
        }

        ajaxGet(
            '/api/districts?regency_id=' + regencyId,
            function(data) {
                if (!targetDistrictSelect) return;
                targetDistrictSelect.innerHTML = '<option value="">-- Pilih Kecamatan --</option>';
                for (var i = 0; i < data.length; i++) {
                    var sel = (selectedId && selectedId == data[i].id) ? ' selected' : '';
                    targetDistrictSelect.innerHTML += '<option value="' + data[i].id + '"' + sel + '>' + data[i].name + '</option>';
                }
                targetDistrictSelect.disabled = false;
            },
            function(error) {
                console.error('Error loading districts:', error);
                if (targetDistrictSelect) {
                    targetDistrictSelect.innerHTML = '<option value="">Gagal memuat - coba lagi</option>';
                }
            }
        );
    }

    // Create form - province change
    provinceSelect.addEventListener('change', function() {
        console.log('[Dropdown] Province changed to:', this.value);
        loadRegencies(regencySelect, districtSelect, this.value, null);
    });

    // Create form - regency change
    if (regencySelect) {
        regencySelect.addEventListener('change', function() {
            loadDistricts(districtSelect, this.value, null);
        });
    }

    // Filter form - province change
    if (filterProvince) {
        filterProvince.addEventListener('change', function() {
            if (filterRegency) {
                if (!this.value) {
                    filterRegency.innerHTML = '<option value="">Semua Kabupaten/Kota</option>';
                    return;
                }
                ajaxGet(
                    '/api/regencies?province_id=' + this.value,
                    function(data) {
                        filterRegency.innerHTML = '<option value="">Semua Kabupaten/Kota</option>';
                        for (var i = 0; i < data.length; i++) {
                            filterRegency.innerHTML += '<option value="' + data[i].id + '">' + data[i].name + '</option>';
                        }
                    },
                    function() {}
                );
            }
        });
    }

    // Filter form - regency change
    if (filterRegency) {
        filterRegency.addEventListener('change', function() {
            if (filterDistrict) {
                if (!this.value) {
                    filterDistrict.innerHTML = '<option value="">Semua Kecamatan</option>';
                    return;
                }
                ajaxGet(
                    '/api/districts?regency_id=' + this.value,
                    function(data) {
                        filterDistrict.innerHTML = '<option value="">Semua Kecamatan</option>';
                        for (var i = 0; i < data.length; i++) {
                            filterDistrict.innerHTML += '<option value="' + data[i].id + '">' + data[i].name + '</option>';
                        }
                    },
                    function() {}
                );
            }
        });
    }
}

function initBulkActions() {
    var selectAll = document.getElementById('select-all');
    if (!selectAll) return;

    selectAll.addEventListener('change', function() {
        var checkboxes = document.querySelectorAll('.member-checkbox');
        checkboxes.forEach(function(cb) {
            cb.checked = selectAll.checked;
        });
        updateBulkActionsDisplay();
    });

    document.querySelectorAll('.member-checkbox').forEach(function(cb) {
        cb.addEventListener('change', updateBulkActionsDisplay);
    });

    function updateBulkActionsDisplay() {
        var checked = document.querySelectorAll('.member-checkbox:checked');
        var bulkActions = document.getElementById('bulk-actions');
        var count = document.getElementById('selected-count');
        if (bulkActions && count) {
            if (checked.length > 0) {
                bulkActions.style.display = 'inline-flex';
                count.textContent = checked.length + ' dipilih';
            } else {
                bulkActions.style.display = 'none';
            }
        }
    }

    // Toggle status dropdown based on action
    var bulkActionSelect = document.getElementById('bulk-action-select');
    var newStatusSelect = document.getElementById('new-status-select');
    var bulkSubmitBtn = document.getElementById('bulk-submit-btn');

    if (bulkActionSelect && newStatusSelect && bulkSubmitBtn) {
        bulkActionSelect.addEventListener('change', function() {
            if (this.value === 'update_status') {
                newStatusSelect.style.display = 'inline-block';
            } else {
                newStatusSelect.style.display = 'none';
            }
        });

        bulkSubmitBtn.addEventListener('click', function(e) {
            var action = bulkActionSelect.value;
            if (!action) {
                e.preventDefault();
                alert('Pilih aksi terlebih dahulu!');
                return false;
            }
            if (action === 'update_status') {
                var newStatus = newStatusSelect.value;
                if (!newStatus) {
                    e.preventDefault();
                    alert('Pilih status baru!');
                    return false;
                }
            }
            if (action === 'delete') {
                if (!confirm('Yakin ingin menghapus ' + document.querySelectorAll('.member-checkbox:checked').length + ' anggota?')) {
                    e.preventDefault();
                    return false;
                }
            }
            return true;
        });
    }
}

function initPhotoPreview() {
    var fotoInput = document.getElementById('foto');
    if (!fotoInput) return;

    fotoInput.addEventListener('change', function() {
        var preview = document.getElementById('photo-preview');
        if (!preview) return;

        preview.innerHTML = '';
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<img src="' + e.target.result + '" style="max-width:200px;border-radius:0.5rem;">';
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
}

// Inline status change for members table
function initInlineStatusChange() {
    document.querySelectorAll('.status-select').forEach(function(select) {
        select.addEventListener('change', function() {
            var memberId = this.getAttribute('data-member-id');
            var newStatus = this.value;
            var selectElement = this;

            if (!confirm('Ubah status anggota ini menjadi "' + newStatus + '"?')) {
                // Revert to previous value
                return;
            }

            // Send AJAX request
            ajaxPost('/members/' + memberId + '/status', { status: newStatus },
                function(response) {
                    // Show success feedback
                    selectElement.style.borderColor = '#059669';
                    selectElement.style.backgroundColor = '#d1fae5';
                    setTimeout(function() {
                        selectElement.style.borderColor = '';
                        selectElement.style.backgroundColor = '';
                    }, 1000);
                },
                function(error) {
                    alert('Gagal mengubah status: ' + error);
                    // Could revert select value here
                }
            );
        });
    });
}

function ajaxPost(url, data, onSuccess, onError) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.withCredentials = true;
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('X-CSRF-TOKEN', getCsrfToken());
    xhr.setRequestHeader('X-HTTP-Method-Override', 'PATCH');
    xhr.onload = function() {
        if (xhr.status === 200 || xhr.status === 302) {
            onSuccess(JSON.parse(xhr.responseText) || {});
        } else {
            onError('HTTP ' + xhr.status);
        }
    };
    xhr.onerror = function() {
        onError('Network error');
    };
    xhr.send(JSON.stringify(data));
}

// Debug function - exposed globally
window.testAPI = function() {
    var result = document.getElementById('api-result');
    if (!result) return;

    result.textContent = 'Testing...';

    ajaxGet(
        '/api/regencies?province_id=1',
        function(data) {
            result.textContent = 'Success! Loaded ' + data.length + ' regencies';
            result.style.color = 'green';
            console.log('API Test Success:', data);
        },
        function(error) {
            result.textContent = 'Error: ' + error;
            result.style.color = 'red';
            console.error('API Test Error:', error);
        }
    );
};
