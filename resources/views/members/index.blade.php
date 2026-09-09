@extends('layouts.app')

@section('title', 'Data Anggota')
@section('header', 'Data Anggota')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Daftar Anggota</h3>
        <div style="display:flex;gap:0.75rem;">
            <a href="{{ route('imports.index') }}" class="btn btn-outline">Import</a>
            <a href="{{ route('members.create') }}" class="btn btn-primary">+ Tambah</a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="filters" style="gap:0.5rem;padding:0.75rem;">
        <input type="text" name="search" placeholder="Cari..." value="{{ request('search') }}" style="flex:1;min-width:120px;padding:0.5rem 0.75rem;font-size:0.9rem;">
        <select name="province_id" id="filter-province" style="padding:0.5rem 0.75rem;font-size:0.9rem;min-width:140px;">
            <option value="">Semua Provinsi</option>
            @foreach($provinces as $province)
            <option value="{{ $province->id }}" {{ request('province_id') == $province->id ? 'selected' : '' }}>{{ $province->name }}</option>
            @endforeach
        </select>
        <select name="regency_id" id="filter-regency" disabled style="padding:0.5rem 0.75rem;font-size:0.9rem;min-width:140px;">
            <option value="">Kab/Kota</option>
        </select>
        <select name="district_id" id="filter-district" disabled style="padding:0.5rem 0.75rem;font-size:0.9rem;min-width:140px;">
            <option value="">Kecamatan</option>
        </select>
        <select name="jenis_kelamin" style="padding:0.5rem 0.75rem;font-size:0.9rem;min-width:100px;">
            <option value="">Gender</option>
            <option value="Laki-laki" {{ request('jenis_kelamin') == 'Laki-laki' ? 'selected' : '' }}>Laki-laki</option>
            <option value="Perempuan" {{ request('jenis_kelamin') == 'Perempuan' ? 'selected' : '' }}>Perempuan</option>
        </select>
        <select name="status" style="padding:0.5rem 0.75rem;font-size:0.9rem;min-width:100px;">
            <option value="">Status</option>
            @foreach($statuses as $status)
            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary" style="padding:0.5rem 1rem;font-size:0.9rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </button>
        <a href="{{ route('members.index') }}" class="btn btn-outline" style="padding:0.5rem 0.75rem;font-size:0.9rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="1 4 1 10 7 10"></polyline><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path></svg>
        </a>
    </form>

    {{-- Bulk Actions --}}
    <form id="bulk-form" method="POST" action="/members/bulk-action">
        @csrf
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;padding:1rem;background:var(--gray-50);border-radius:0.5rem;border:1px solid var(--gray-200);">
            <label style="display:flex;align-items:center;gap:0.75rem;cursor:pointer;">
                <input type="checkbox" id="select-all" style="width:1.25rem;height:1.25rem;">
                <span style="font-weight:500;">Pilih Semua</span>
            </label>
            <div id="bulk-actions" style="display:none;">
                <span id="selected-count" style="font-weight:500;color:var(--primary);margin-right:1rem;">0 dipilih</span>
                <select name="action" id="bulk-action-select" style="padding:0.5rem 0.75rem;border:2px solid var(--gray-200);border-radius:0.375rem;font-size:0.95rem;">
                    <option value="">-- Aksi --</option>
                    <option value="update_status">Ubah Status</option>
                    <option value="edit">Edit Massal</option>
                    <option value="delete">Hapus</option>
                </select>
                <select name="new_status" id="new-status-select" style="padding:0.5rem 0.75rem;border:2px solid var(--gray-200);border-radius:0.375rem;font-size:0.95rem;display:none;">
                    <option value="">-- Status --</option>
                    <option value="draft">Draft</option>
                    <option value="ready">Ready</option>
                    <option value="generated">Generated</option>
                    <option value="printed">Printed</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
                <button type="button" class="btn btn-primary" id="bulk-submit-btn">Proses</button>
            </div>
        </div>

    {{-- Bulk Edit Modal --}}
    <div id="bulk-edit-modal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;">
        <div style="background:#fff;border-radius:0.75rem;padding:2rem;max-width:500px;width:90%;max-height:90vh;overflow-y:auto;">
            <h3 style="margin:0 0 1.5rem;font-size:1.25rem;font-weight:600;">Edit Massal Anggota</h3>
            <p id="bulk-edit-count" style="color:var(--gray-600);margin-bottom:1.5rem;"></p>
            <div style="display:flex;flex-direction:column;gap:1rem;">
                <div>
                    <label style="display:block;font-weight:500;margin-bottom:0.5rem;">Provinsi</label>
                    <select name="bulk_province_id" id="bulk-province-select" style="width:100%;padding:0.5rem 0.75rem;border:2px solid var(--gray-200);border-radius:0.375rem;">
                        <option value="">-- Tidak diubah --</option>
                        @foreach($provinces as $province)
                        <option value="{{ $province->id }}">{{ $province->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-weight:500;margin-bottom:0.5rem;">Kab/Kota</label>
                    <select name="bulk_regency_id" id="bulk-regency-select" style="width:100%;padding:0.5rem 0.75rem;border:2px solid var(--gray-200);border-radius:0.375rem;" disabled>
                        <option value="">-- Tidak diubah --</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-weight:500;margin-bottom:0.5rem;">Kecamatan</label>
                    <select name="bulk_district_id" id="bulk-district-select" style="width:100%;padding:0.5rem 0.75rem;border:2px solid var(--gray-200);border-radius:0.375rem;" disabled>
                        <option value="">-- Tidak diubah --</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;font-weight:500;margin-bottom:0.5rem;">Berlaku Hingga</label>
                    <input type="date" name="bulk_berlaku_hingga" id="bulk-berlaku-select" style="width:100%;padding:0.5rem 0.75rem;border:2px solid var(--gray-200);border-radius:0.375rem;">
                </div>
            </div>
            <div style="display:flex;gap:0.75rem;justify-content:flex-end;margin-top:1.5rem;">
                <button type="button" id="bulk-edit-cancel" class="btn btn-outline">Batal</button>
                <button type="button" class="btn btn-primary" id="bulk-edit-confirm">Simpan Perubahan</button>
            </div>
        </div>
    </div>

        <table>
            <thead>
                <tr>
                    <th width="50"></th>
                    <th>NIK</th>
                    <th>Nama</th>
                    <th>Domisili</th>
                    <th>Gender</th>
                    <th>Status</th>
                    <th>Berlaku</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                <tr>
                    <td>
                        <input type="checkbox" name="member_ids[]" value="{{ $member->id }}" class="member-checkbox" style="width:1.125rem;height:1.125rem;">
                    </td>
                    <td><strong><a href="{{ route('members.show', $member) }}" style="color:var(--primary);text-decoration:none;">{{ $member->nik }}</a></strong></td>
                    <td>{{ $member->nama }}</td>
                    <td>
                        <div>{{ $member->district?->name ?? '-' }}</div>
                        <small style="color:var(--gray-500);">{{ $member->regency?->name ?? '' }}</small>
                    </td>
                    <td>{{ $member->jenis_kelamin }}</td>
                    <td>
                        <select name="status" class="status-select" data-member-id="{{ $member->id }}" style="padding:0.375rem 0.5rem;border-radius:0.25rem;border:2px solid var(--gray-200);font-size:0.85rem;">
                            <option value="draft" {{ $member->status == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="ready" {{ $member->status == 'ready' ? 'selected' : '' }}>Ready</option>
                            <option value="generated" {{ $member->status == 'generated' ? 'selected' : '' }}>Generated</option>
                            <option value="printed" {{ $member->status == 'printed' ? 'selected' : '' }}>Printed</option>
                            <option value="active" {{ $member->status == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ $member->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </td>
                    <td>{{ $member->berlaku_hingga->format('d/m/Y') }}</td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('members.show', $member) }}" class="btn btn-sm btn-outline" title="Detail">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                            </a>
                            <a href="{{ route('members.edit', $member) }}" class="btn btn-sm btn-primary" title="Edit">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"></path></svg>
                            </a>
                            <form method="POST" action="{{ route('members.destroy', $member) }}" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus anggota ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="empty-state">
                        <p>Belum ada data anggota.</p>
                        <a href="{{ route('members.create') }}" class="btn btn-primary">+ Tambah Anggota Baru</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:1.5rem;">
            {{ $members->withQueryString()->links() }}
        </div>
    </form>
</div>

<script>
// ============================================
// FILTER DROPDOWN - Province/Regency/District Cascade
// ============================================

const filterProvince = document.getElementById('filter-province');
const filterRegency = document.getElementById('filter-regency');
const filterDistrict = document.getElementById('filter-district');

// Load regencies when province changes
filterProvince.addEventListener('change', function() {
    const provinceId = this.value;
    filterRegency.innerHTML = '<option value="">Memuat...</option>';
    filterDistrict.innerHTML = '<option value="">Semua Kecamatan</option>';
    filterDistrict.disabled = true;

    if (provinceId) {
        fetch('/api/regencies/' + provinceId)
            .then(r => r.json())
            .then(data => {
                filterRegency.innerHTML = '<option value="">Semua Kab/Kota</option>';
                data.forEach(r => {
                    filterRegency.innerHTML += `<option value="${r.id}">${r.name}</option>`;
                });
                filterRegency.disabled = false;
                // Preserve selection if exists
                @if(request('regency_id'))
                    filterRegency.value = '{{ request('regency_id') }}';
                @endif
            })
            .catch(err => {
                filterRegency.innerHTML = '<option value="">Error memuat</option>';
            });
    } else {
        filterRegency.innerHTML = '<option value="">Semua Kab/Kota</option>';
        filterRegency.disabled = true;
    }
});

// Load districts when regency changes
filterRegency.addEventListener('change', function() {
    const regencyId = this.value;
    filterDistrict.innerHTML = '<option value="">Memuat...</option>';

    if (regencyId) {
        fetch('/api/districts/' + regencyId)
            .then(r => r.json())
            .then(data => {
                filterDistrict.innerHTML = '<option value="">Semua Kecamatan</option>';
                data.forEach(d => {
                    filterDistrict.innerHTML += `<option value="${d.id}">${d.name}</option>`;
                });
                filterDistrict.disabled = false;
                // Preserve selection if exists
                @if(request('district_id'))
                    filterDistrict.value = '{{ request('district_id') }}';
                @endif
            })
            .catch(err => {
                filterDistrict.innerHTML = '<option value="">Error memuat</option>';
            });
    } else {
        filterDistrict.innerHTML = '<option value="">Semua Kecamatan</option>';
        filterDistrict.disabled = true;
    }
});

// Initialize on page load - check if province already selected
document.addEventListener('DOMContentLoaded', function() {
    @if(request('province_id'))
        // Province is selected, load its regencies
        filterProvince.dispatchEvent(new Event('change'));
    @endif

    @if(request('regency_id') && request('province_id'))
        // After regencies loaded, also load districts
        setTimeout(function() {
            @if(request('district_id'))
                filterRegency.dispatchEvent(new Event('change'));
            @endif
        }, 500);
    @endif
});
</script>

{{-- =============================================
     BULK ACTIONS - COMPLETE SELF-CONTAINED
     ============================================= --}}
<script>
(function() {
    console.log('[BulkActions] Initializing...');

    var selectAll = document.getElementById('select-all');
    var bulkActions = document.getElementById('bulk-actions');
    var countSpan = document.getElementById('selected-count');
    var bulkActionSelect = document.getElementById('bulk-action-select');
    var newStatusSelect = document.getElementById('new-status-select');
    var bulkSubmitBtn = document.getElementById('bulk-submit-btn');
    var bulkForm = document.getElementById('bulk-form');

    if (!selectAll) {
        console.log('[BulkActions] select-all not found');
        return;
    }

    var memberCheckboxes = document.querySelectorAll('.member-checkbox');
    console.log('[BulkActions] Found', memberCheckboxes.length, 'checkboxes');

    // ====== SELECT ALL TOGGLE ======
    selectAll.addEventListener('change', function() {
        memberCheckboxes.forEach(function(cb) {
            cb.checked = selectAll.checked;
        });
        updateBulkUI();
    });

    // ====== INDIVIDUAL CHECKBOX TOGGLE ======
    memberCheckboxes.forEach(function(cb) {
        cb.addEventListener('change', updateBulkUI);
    });

    // ====== UPDATE UI ======
    function updateBulkUI() {
        var checked = document.querySelectorAll('.member-checkbox:checked');
        var checkedCount = checked.length;

        if (bulkActions && countSpan) {
            if (checkedCount > 0) {
                bulkActions.style.display = 'flex';
                countSpan.textContent = checkedCount + ' dipilih';
            } else {
                bulkActions.style.display = 'none';
            }
        }

        // Indeterminate state for select all
        if (checkedCount > 0 && checkedCount < memberCheckboxes.length) {
            selectAll.checked = false;
            selectAll.indeterminate = true;
        } else if (checkedCount === memberCheckboxes.length) {
            selectAll.checked = true;
            selectAll.indeterminate = false;
        } else {
            selectAll.checked = false;
            selectAll.indeterminate = false;
        }
    }

    // ====== SHOW/HIDE STATUS DROPDOWN ======
    if (bulkActionSelect && newStatusSelect) {
        bulkActionSelect.addEventListener('change', function() {
            if (this.value === 'update_status') {
                newStatusSelect.style.display = 'inline-block';
            } else {
                newStatusSelect.style.display = 'none';
            }
        });
    }

    // ====== SUBMIT BUTTON CLICK ======
    if (bulkSubmitBtn) {
        bulkSubmitBtn.addEventListener('click', function() {
            console.log('[BulkActions] Proses clicked');
            var action = bulkActionSelect ? bulkActionSelect.value : '';
            var checked = document.querySelectorAll('.member-checkbox:checked');
            console.log('[BulkActions] Action:', action, '| Checked:', checked.length);

            // Validation
            if (checked.length === 0) {
                alert('Pilih minimal satu anggota.');
                return;
            }
            if (!action) {
                alert('Pilih aksi terlebih dahulu!');
                return;
            }
            if (action === 'update_status' && (!newStatusSelect || !newStatusSelect.value)) {
                alert('Pilih status baru!');
                return;
            }
            if (action === 'delete') {
                if (!confirm('Yakin ingin menghapus ' + checked.length + ' anggota?')) {
                    return;
                }
            }

            // For "edit" action - open modal
            if (action === 'edit') {
                var modal = document.getElementById('bulk-edit-modal');
                var countEl = document.getElementById('bulk-edit-count');
                if (modal) {
                    if (countEl) countEl.textContent = checked.length + ' anggota dipilih';
                    modal.style.display = 'flex';
                }
                return;
            }

            // ====== SUBMIT VIA FETCH ======
            var memberIds = [];
            checked.forEach(function(cb) { memberIds.push(cb.value); });

            var formData = new FormData();
            // Append each member ID as separate entry so PHP gets a proper array
            memberIds.forEach(function(id) {
                formData.append('member_ids[]', id);
            });
            formData.append('action', action);
            if (action === 'update_status') {
                formData.append('new_status', newStatusSelect.value);
            }

            bulkSubmitBtn.disabled = true;
            bulkSubmitBtn.textContent = 'Memproses...';

            // Use absolute URL to avoid relative path issues
            var bulkActionUrl = '/members/bulk-action';
            console.log('[BulkActions] Fetching to:', bulkActionUrl);

            fetch(bulkActionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            })
            .then(function(response) {
                console.log('[BulkActions] Response status:', response.status);
                if (response.status === 200 || response.status === 201) {
                    return response.json().catch(function() {
                        return { success: true, message: 'Berhasil!' };
                    });
                }
                if (response.status === 422) {
                    return response.json().then(function(data) {
                        throw new Error(data.message || 'Validasi gagal');
                    });
                }
                if (response.status === 500) {
                    throw new Error('Server error');
                }
                throw new Error('Status: ' + response.status);
            })
            .then(function(data) {
                console.log('[BulkActions] Response data:', data);
                if (data.success) {
                    alert(data.message || 'Berhasil!');
                    window.location.reload();
                } else {
                    alert(data.message || 'Terjadi kesalahan');
                }
            })
            .catch(function(err) {
                console.error('[BulkActions] Error:', err);
                alert('Gagal: ' + err.message);
            })
            .finally(function() {
                bulkSubmitBtn.disabled = false;
                bulkSubmitBtn.textContent = 'Proses';
            });
        });
    }

    // ====== BULK EDIT MODAL ======
    var bulkEditCancel = document.getElementById('bulk-edit-cancel');
    var bulkEditConfirm = document.getElementById('bulk-edit-confirm');
    var bulkEditModal = document.getElementById('bulk-edit-modal');

    if (bulkEditCancel) {
        bulkEditCancel.addEventListener('click', function() {
            if (bulkEditModal) bulkEditModal.style.display = 'none';
        });
    }

    if (bulkEditModal) {
        bulkEditModal.addEventListener('click', function(e) {
            if (e.target === bulkEditModal) {
                bulkEditModal.style.display = 'none';
            }
        });
    }

    if (bulkEditConfirm) {
        bulkEditConfirm.addEventListener('click', function() {
            var checked = document.querySelectorAll('.member-checkbox:checked');
            if (checked.length === 0) {
                alert('Pilih minimal satu anggota.');
                return;
            }

            var provinceId = document.getElementById('bulk-province-select');
            var regencyId = document.getElementById('bulk-regency-select');
            var districtId = document.getElementById('bulk-district-select');
            var berlaku = document.getElementById('bulk-berlaku-select');

            var formData = new FormData();
            // Append each member ID as separate entry so PHP gets a proper array
            checked.forEach(function(cb) {
                formData.append('member_ids[]', cb.value);
            });
            formData.append('action', 'edit');
            if (provinceId && provinceId.value) formData.append('bulk_province_id', provinceId.value);
            if (regencyId && regencyId.value) formData.append('bulk_regency_id', regencyId.value);
            if (districtId && districtId.value) formData.append('bulk_district_id', districtId.value);
            if (berlaku && berlaku.value) formData.append('bulk_berlaku_hingga', berlaku.value);

            bulkEditConfirm.disabled = true;
            bulkEditConfirm.textContent = 'Memproses...';

            // Use absolute URL to avoid relative path issues
            fetch('/members/bulk-action', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    alert(data.message);
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(function(err) {
                alert('Gagal: ' + err.message);
            })
            .finally(function() {
                bulkEditConfirm.disabled = false;
                bulkEditConfirm.textContent = 'Simpan Perubahan';
                if (bulkEditModal) bulkEditModal.style.display = 'none';
            });
        });
    }

    // ====== BULK PROVINCE DROPDOWN ======
    var bulkProvinceSelect = document.getElementById('bulk-province-select');
    var bulkRegencySelect = document.getElementById('bulk-regency-select');
    var bulkDistrictSelect = document.getElementById('bulk-district-select');

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

            if (!provinceId) {
                if (bulkRegencySelect) {
                    bulkRegencySelect.innerHTML = '<option value="">-- Tidak diubah --</option>';
                    bulkRegencySelect.disabled = true;
                }
                return;
            }

            fetch('/api/regencies/' + provinceId)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (bulkRegencySelect) {
                        bulkRegencySelect.innerHTML = '<option value="">-- Tidak diubah --</option>';
                        data.forEach(function(r) {
                            bulkRegencySelect.innerHTML += '<option value="' + r.id + '">' + r.name + '</option>';
                        });
                        bulkRegencySelect.disabled = false;
                    }
                })
                .catch(function() {
                    if (bulkRegencySelect) bulkRegencySelect.innerHTML = '<option value="">Gagal memuat</option>';
                });
        });
    }

    if (bulkRegencySelect) {
        bulkRegencySelect.addEventListener('change', function() {
            var regencyId = this.value;
            if (bulkDistrictSelect) {
                bulkDistrictSelect.innerHTML = '<option value="">Memuat...</option>';
                bulkDistrictSelect.disabled = true;
            }

            if (!regencyId) {
                if (bulkDistrictSelect) {
                    bulkDistrictSelect.innerHTML = '<option value="">-- Tidak diubah --</option>';
                    bulkDistrictSelect.disabled = true;
                }
                return;
            }

            fetch('/api/districts/' + regencyId)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (bulkDistrictSelect) {
                        bulkDistrictSelect.innerHTML = '<option value="">-- Tidak diubah --</option>';
                        data.forEach(function(d) {
                            bulkDistrictSelect.innerHTML += '<option value="' + d.id + '">' + d.name + '</option>';
                        });
                        bulkDistrictSelect.disabled = false;
                    }
                })
                .catch(function() {
                    if (bulkDistrictSelect) bulkDistrictSelect.innerHTML = '<option value="">Gagal memuat</option>';
                });
        });
    }

    // ====== INLINE STATUS UPDATE ======
    var statusSelects = document.querySelectorAll('.status-select');
    statusSelects.forEach(function(select) {
        var originalStatus = select.value;
        select.addEventListener('change', function() {
            var memberId = this.dataset.memberId;
            var newStatus = this.value;
            if (!memberId) return;

            if (!confirm('Ubah status anggota ini?')) {
                this.value = originalStatus;
                return;
            }

            this.disabled = true;
            var btn = this;

            fetch('/members/' + memberId + '/status', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ status: newStatus })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    originalStatus = newStatus;
                    btn.style.backgroundColor = '#22c55e33';
                    setTimeout(function() { btn.style.backgroundColor = ''; }, 1500);
                } else {
                    alert(data.message || 'Gagal');
                    btn.value = originalStatus;
                }
            })
            .catch(function() {
                alert('Gagal mengubah status');
                btn.value = originalStatus;
            })
            .finally(function() {
                btn.disabled = false;
            });
        });
    });

    console.log('[BulkActions] Init complete');
})();
</script>
@endsection
