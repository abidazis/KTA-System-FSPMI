
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
    initInlineStatusUpdate();
    initBulkEditModal();
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
            '/api/regencies/' + provinceId,
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
            '/api/districts/' + regencyId,
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
                    '/api/regencies/' + this.value,
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
                    '/api/districts/' + this.value,
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
    if (!selectAll) {
        console.log('[BulkActions] select-all not found');
        return;
    }
    console.log('[BulkActions] Initializing...');

    var memberCheckboxes = document.querySelectorAll('.member-checkbox');
    var bulkActions = document.getElementById('bulk-actions');
    var countSpan = document.getElementById('selected-count');
    var bulkActionSelect = document.getElementById('bulk-action-select');
    var newStatusSelect = document.getElementById('new-status-select');
    var bulkSubmitBtn = document.getElementById('bulk-submit-btn');
    var bulkForm = document.getElementById('bulk-form');
    console.log('[BulkActions] Elements found:', { memberCheckboxes: memberCheckboxes.length, bulkActions: !!bulkActions, bulkSubmitBtn: !!bulkSubmitBtn, bulkForm: !!bulkForm });

    // Toggle select all
    selectAll.addEventListener('change', function() {
        memberCheckboxes.forEach(function(cb) {
            cb.checked = selectAll.checked;
        });
        updateBulkUI();
    });

    memberCheckboxes.forEach(function(cb) {
        cb.addEventListener('change', updateBulkUI);
    });

    function updateBulkUI() {
        var checked = document.querySelectorAll('.member-checkbox:checked');
        if (bulkActions && countSpan) {
            if (checked.length > 0) {
                bulkActions.style.display = 'flex';
                countSpan.textContent = checked.length + ' dipilih';
            } else {
                bulkActions.style.display = 'none';
            }
        }
        // Indeterminate state
        if (checked.length > 0 && checked.length < memberCheckboxes.length) {
            selectAll.checked = false;
            selectAll.indeterminate = true;
        } else if (checked.length === memberCheckboxes.length) {
            selectAll.checked = true;
            selectAll.indeterminate = false;
        } else {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
    }

    // Toggle status dropdown based on action
    if (bulkActionSelect && newStatusSelect) {
        bulkActionSelect.addEventListener('change', function() {
            if (this.value === 'update_status') {
                newStatusSelect.style.display = 'inline-block';
            } else {
                newStatusSelect.style.display = 'none';
            }
        });
    }

    // Bulk submit with client-side validation
    if (bulkSubmitBtn && bulkForm) {
        bulkSubmitBtn.addEventListener('click', function(e) {
            console.log('[BulkActions] Proses clicked');
            var action = bulkActionSelect ? bulkActionSelect.value : '';
            var checked = document.querySelectorAll('.member-checkbox:checked');
            console.log('[BulkActions] Action:', action, 'Checked:', checked.length);

            if (checked.length === 0) {
                alert('Pilih minimal satu anggota.');
                return;
            }
            if (!action) {
                alert('Pilih aksi terlebih dahulu!');
                return;
            }
            if (action === 'update_status' && newStatusSelect && !newStatusSelect.value) {
                alert('Pilih status baru!');
                return;
            }
            if (action === 'delete') {
                if (!confirm('Yakin ingin menghapus ' + checked.length + ' anggota?')) {
                    return;
                }
            }
            if (action === 'edit') {
                if (typeof openBulkEditModal === 'function') {
                    openBulkEditModal(checked.length);
                }
                return;
            }

            // Collect checked member IDs
            var memberIds = [];
            checked.forEach(function(cb) { memberIds.push(cb.value); });

            console.log('[BulkActions] Submitting via fetch to:', bulkForm.action, 'IDs:', memberIds);

            // Use JavaScript fetch instead of HTML form submit for better control
            var formData = new FormData(bulkForm);
            // Add checked member IDs (in case not all are in the form)
            formData.delete('member_ids[]');
            memberIds.forEach(function(id) { formData.append('member_ids[]', id); });

            bulkSubmitBtn.disabled = true;
            bulkSubmitBtn.textContent = 'Memproses...';

            fetch(bulkForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': getCsrfToken(),
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(function(response) {
                console.log('[BulkActions] Response status:', response.status);
                if (response.status === 302 || response.redirected) {
                    // Server returned redirect - follow it
                    window.location.href = response.url || response.redirected;
                    return;
                }
                if (response.status === 200 || response.status === 201) {
                    return response.json().catch(function() { return { success: true }; });
                }
                if (response.status === 419) {
                    alert('Sesi habis. Silakan refresh halaman dan coba lagi.');
                    window.location.reload();
                    return;
                }
                if (response.status === 422) {
                    return response.json().then(function(data) {
                        alert('Validasi gagal: ' + (data.message || data.error || 'Cek data input'));
                    });
                }
                return response.text().then(function(text) {
                    console.error('[BulkActions] Error response:', text);
                    alert('Terjadi kesalahan (status ' + response.status + '). Silakan coba lagi.');
                });
            })
            .then(function(data) {
                if (data && data.success) {
                    alert(data.message || 'Berhasil!');
                    window.location.reload();
                }
            })
            .catch(function(err) {
                console.error('[BulkActions] Fetch error:', err);
                alert('Gagal mengirim request. Pastikan koneksi internet stabil.');
            })
            .finally(function() {
                bulkSubmitBtn.disabled = false;
                bulkSubmitBtn.textContent = 'Proses';
            });
        });
    }
}

// ============================================
// INLINE STATUS UPDATE (per-row dropdown in members table)
// ============================================
function initInlineStatusUpdate() {
    var statusSelects = document.querySelectorAll('.status-select');
    if (!statusSelects.length) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]')
        ? document.querySelector('meta[name="csrf-token"]').content
        : (document.querySelector('input[name="_token"]') ? document.querySelector('input[name="_token"]').value : '');

    // Store original status on page load
    statusSelects.forEach(function(select) {
        select.dataset.originalStatus = select.value;
    });

    statusSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            var memberId = this.dataset.memberId;
            var newStatus = this.value;
            var originalStatus = this.dataset.originalStatus || '';

            if (newStatus === originalStatus) return;

            var el = this;
            el.disabled = true;

            fetch('/members/' + memberId + '/status', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    el.dataset.originalStatus = newStatus;
                    // Brief green flash
                    var origBg = el.style.backgroundColor;
                    el.style.backgroundColor = '#22c55e33';
                    setTimeout(function() { el.style.backgroundColor = origBg; }, 1000);
                } else {
                    alert('Gagal mengubah status: ' + (data.message || 'Unknown error'));
                    el.value = originalStatus;
                }
            })
            .catch(function() {
                alert('Terjadi kesalahan saat mengubah status.');
                el.value = originalStatus;
            })
            .finally(function() {
                el.disabled = false;
            });
        });
    });
}

// ============================================
// BULK EDIT MODAL
// ============================================
function initBulkEditModal() {
    var modal = document.getElementById('bulk-edit-modal');
    if (!modal) return;

    var csrfToken = document.querySelector('meta[name="csrf-token"]')
        ? document.querySelector('meta[name="csrf-token"]').content
        : (document.querySelector('input[name="_token"]') ? document.querySelector('input[name="_token"]').value : '');

    var bulkEditCount = document.getElementById('bulk-edit-count');
    var bulkProvinceSelect = document.getElementById('bulk-province-select');
    var bulkRegencySelect = document.getElementById('bulk-regency-select');
    var bulkDistrictSelect = document.getElementById('bulk-district-select');
    var bulkBerlakuSelect = document.getElementById('bulk-berlaku-select');
    var bulkEditCancel = document.getElementById('bulk-edit-cancel');
    var bulkEditConfirm = document.getElementById('bulk-edit-confirm');

    window.openBulkEditModal = function(count) {
        if (bulkEditCount) bulkEditCount.textContent = 'Mengedit ' + count + ' anggota.';
        modal.style.display = 'flex';
    };

    function closeBulkEditModal() {
        modal.style.display = 'none';
        if (bulkProvinceSelect) bulkProvinceSelect.value = '';
        if (bulkRegencySelect) {
            bulkRegencySelect.innerHTML = '<option value="">-- Tidak diubah --</option>';
            bulkRegencySelect.disabled = true;
        }
        if (bulkDistrictSelect) {
            bulkDistrictSelect.innerHTML = '<option value="">-- Tidak diubah --</option>';
            bulkDistrictSelect.disabled = true;
        }
        if (bulkBerlakuSelect) bulkBerlakuSelect.value = '';
    }

    if (bulkEditCancel) {
        bulkEditCancel.addEventListener('click', closeBulkEditModal);
    }
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeBulkEditModal();
    });

    // Cascade: province → regency
    if (bulkProvinceSelect) {
        bulkProvinceSelect.addEventListener('change', function() {
            var provinceId = this.value;
            if (bulkRegencySelect) {
                bulkRegencySelect.innerHTML = '<option value="">Memuat...</option>';
                bulkRegencySelect.disabled = true;
            }
            if (bulkDistrictSelect) {
                bulkDistrictSelect.innerHTML = '<option value="">-- Tidak diubah --</option>';
                bulkDistrictSelect.disabled = true;
            }
            if (provinceId && bulkRegencySelect) {
                fetch('/api/regencies/' + provinceId)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        bulkRegencySelect.innerHTML = '<option value="">-- Tidak diubah --</option>';
                        data.forEach(function(r) {
                            bulkRegencySelect.innerHTML += '<option value="' + r.id + '">' + r.name + '</option>';
                        });
                        bulkRegencySelect.disabled = false;
                    })
                    .catch(function() {
                        bulkRegencySelect.innerHTML = '<option value="">Error</option>';
                    });
            } else if (bulkRegencySelect) {
                bulkRegencySelect.innerHTML = '<option value="">-- Tidak diubah --</option>';
            }
        });
    }

    // Cascade: regency → district
    if (bulkRegencySelect) {
        bulkRegencySelect.addEventListener('change', function() {
            var regencyId = this.value;
            if (bulkDistrictSelect) {
                bulkDistrictSelect.innerHTML = '<option value="">Memuat...</option>';
                bulkDistrictSelect.disabled = true;
            }
            if (regencyId && bulkDistrictSelect) {
                fetch('/api/districts/' + regencyId)
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        bulkDistrictSelect.innerHTML = '<option value="">-- Tidak diubah --</option>';
                        data.forEach(function(d) {
                            bulkDistrictSelect.innerHTML += '<option value="' + d.id + '">' + d.name + '</option>';
                        });
                        bulkDistrictSelect.disabled = false;
                    })
                    .catch(function() {
                        bulkDistrictSelect.innerHTML = '<option value="">Error</option>';
                    });
            } else if (bulkDistrictSelect) {
                bulkDistrictSelect.innerHTML = '<option value="">-- Tidak diubah --</option>';
            }
        });
    }

    // Submit bulk edit via AJAX
    if (bulkEditConfirm) {
        bulkEditConfirm.addEventListener('click', function() {
            var checked = document.querySelectorAll('.member-checkbox:checked');
            var memberIds = Array.from(checked).map(function(cb) { return cb.value; });

            var payload = {
                member_ids: memberIds,
                action: 'edit',
                province_id: bulkProvinceSelect && bulkProvinceSelect.value ? bulkProvinceSelect.value : null,
                regency_id: bulkRegencySelect && bulkRegencySelect.value ? bulkRegencySelect.value : null,
                district_id: bulkDistrictSelect && bulkDistrictSelect.value ? bulkDistrictSelect.value : null,
                berlaku_hingga: bulkBerlakuSelect && bulkBerlakuSelect.value ? bulkBerlakuSelect.value : null,
            };

            if (!payload.province_id && !payload.regency_id && !payload.district_id && !payload.berlaku_hingga) {
                alert('Isi minimal satu field yang ingin diubah.');
                return;
            }

            bulkEditConfirm.disabled = true;
            bulkEditConfirm.textContent = 'Menyimpan...';

            var bulkForm = document.getElementById('bulk-form');
            var actionUrl = bulkForm ? bulkForm.action : '/members/bulk-action';

            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success || data.redirect) {
                    window.location.reload();
                } else {
                    alert('Gagal: ' + (data.message || 'Unknown error'));
                    bulkEditConfirm.disabled = false;
                    bulkEditConfirm.textContent = 'Simpan Perubahan';
                }
            })
            .catch(function() {
                alert('Terjadi kesalahan. Silakan coba lagi.');
                bulkEditConfirm.disabled = false;
                bulkEditConfirm.textContent = 'Simpan Perubahan';
            });
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
// initInlineStatusUpdate is defined above (see "INLINE STATUS UPDATE" section)

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
        '/api/regencies/1',
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
