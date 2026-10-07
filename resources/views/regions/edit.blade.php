@extends('layouts.app')

@section('title', 'Edit Wilayah')
@section('header', 'Edit Wilayah')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Form Edit Wilayah</h3>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('regions.index') }}" class="btn btn-outline">Kembali</a>
            <button type="button" class="btn btn-danger" id="delete-province-btn" onclick="showDeleteConfirmation()">
                Hapus Provinsi
            </button>
        </div>
    </div>

    <form action="{{ route('regions.update', $province) }}" method="POST" id="region-form">
        @csrf
        @method('PUT')

        <div style="padding: 1.5rem;">
            {{-- Nama Provinsi --}}
            <div class="form-group">
                <label for="province-name">Nama Provinsi <span class="text-danger">*</span></label>
                <input
                    type="text"
                    id="province-name"
                    name="name"
                    class="form-control"
                    placeholder="Contoh: Jawa Barat"
                    value="{{ old('name', $province->name) }}"
                    required
                >
                @error('name')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>

            {{-- Kabupaten/Kota --}}
            <div class="form-group">
                <label>Kabupaten/Kota <span class="text-danger">*</span></label>
                <div id="regencies-container">
                    {{-- Dynamic regency cards will be rendered here by JS --}}
                </div>
                <button type="button" class="btn btn-outline" id="add-regency-btn" style="margin-top: 0.75rem;">
                    + Tambah Kabupaten/Kota
                </button>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('regions.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </div>
    </form>
</div>

{{-- Hidden Delete Form --}}
<form id="delete-form" action="{{ route('regions.destroy', $province) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

{{-- Modal Popup 1: Konfirmasi Awal --}}
<div id="delete-modal-1" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; max-width: 400px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
        <div style="font-size: 3rem; margin-bottom: 1rem;">⚠️</div>
        <h4 style="margin-bottom: 1rem; color: var(--gray-800);">Konfirmasi Hapus</h4>
        <p style="margin-bottom: 1.5rem; color: var(--gray-600);">
            Anda yakin ingin menghapus Provinsi <strong>{{ $province->name }}</strong> beserta seluruh data di dalamnya?
        </p>
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <button type="button" class="btn btn-outline" onclick="closeDeleteModal(1)">Batal</button>
            <button type="button" class="btn btn-warning" onclick="closeDeleteModal(1); setTimeout(() => showDeleteConfirmationFinal(), 100);">
                Ya, Lanjutkan
            </button>
        </div>
    </div>
</div>

{{-- Modal Popup 2: Konfirmasi Final --}}
<div id="delete-modal-2" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1001; justify-content: center; align-items: center;">
    <div style="background: white; padding: 2rem; border-radius: 12px; max-width: 450px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.3); border: 3px solid #dc3545;">
        <div style="font-size: 3rem; margin-bottom: 1rem;">🚨</div>
        <h4 style="margin-bottom: 0.5rem; color: #dc3545;">PERHATIAN!</h4>
        <p style="margin-bottom: 1rem; color: var(--gray-700); font-weight: 600;">
            Tindakan ini tidak dapat dibatalkan!
        </p>
        <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; text-align: left;">
            <p style="margin: 0; color: #856404; font-size: 0.875rem;">
                <strong>Yang akan dihapus:</strong>
            </p>
            <ul style="margin: 0.5rem 0 0 1.25rem; color: #856404; font-size: 0.875rem;">
                <li>{{ $province->name }}</li>
                <li>{{ $province->regencies->count() }} Kabupaten/Kota</li>
                <li>{{ $province->regencies->sum(fn($r) => $r->districts->count()) }} Kecamatan</li>
            </ul>
        </div>
        <p style="margin-bottom: 1.5rem; color: var(--gray-600);">
            Ketik <strong>HAPUS</strong> untuk mengkonfirmasi:
        </p>
        <input type="text" id="delete-confirm-input" class="form-control" placeholder="Ketik HAPUS di sini" style="margin-bottom: 1rem; text-align: center; font-weight: bold; text-transform: uppercase;">
        <div style="display: flex; gap: 1rem; justify-content: center;">
            <button type="button" class="btn btn-outline" onclick="closeDeleteModal(2)">Batal</button>
            <button type="button" class="btn btn-danger" id="confirm-delete-btn" onclick="submitDelete()" disabled>
                Hapus Sekarang
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Delete confirmation functions - MUST be global to work with inline onclick
function showDeleteConfirmation() {
    document.getElementById('delete-modal-1').style.display = 'flex';
}

function showDeleteConfirmationFinal() {
    document.getElementById('delete-modal-2').style.display = 'flex';
    document.getElementById('delete-confirm-input').value = '';
    document.getElementById('confirm-delete-btn').disabled = true;
}

function closeDeleteModal(modalNumber) {
    document.getElementById('delete-modal-' + modalNumber).style.display = 'none';
}

function submitDelete() {
    document.getElementById('delete-form').submit();
}

(function() {
    // Data from server (passed via Blade)
    const existingData = {
        regencies: {!! json_encode($province->regencies->map(function($r) {
            return [
                'id' => $r->id,
                'name' => $r->name,
                'districts' => $r->districts->map(function($d) {
                    return ['id' => $d->id, 'name' => $d->name];
                })->toArray()
            ];
        })->toArray()) !!}
    };

    // Counter for unique IDs
    let uniqueIdCounter = 0;
    function generateUniqueId() {
        return 'reg_' + (++uniqueIdCounter);
    }

    // Re-index all cards after any change
    function reindexAllCards() {
        const container = document.getElementById('regencies-container');
        const cards = container.querySelectorAll('.regency-card');

        cards.forEach((card, newIndex) => {
            // Update data-index
            card.dataset.index = newIndex;

            // Update regency name input
            const regencyInput = card.querySelector('.regency-name-input');
            if (regencyInput) {
                regencyInput.name = `regencies[${newIndex}][name]`;
            }

            // Update all district inputs
            const districtInputs = card.querySelectorAll('.district-name-input');
            districtInputs.forEach((input, districtIndex) => {
                input.name = `regencies[${newIndex}][districts][${districtIndex}][name]`;
            });

            // Update add-district buttons with correct index
            const addDistrictBtn = card.querySelector('.add-district-btn');
            if (addDistrictBtn) {
                addDistrictBtn.onclick = function() {
                    addDistrict(card, newIndex);
                };
            }
        });
    }

    // Create a regency card
    function createRegencyCard(index, regencyData = null) {
        const name = regencyData ? regencyData.name : '';
        const districts = regencyData ? regencyData.districts : [];
        const uniqueId = generateUniqueId();

        const card = document.createElement('div');
        card.className = 'regency-card';
        card.dataset.uniqueId = uniqueId;
        card.dataset.index = index;

        let districtsHtml = '';
        districts.forEach((d, i) => {
            districtsHtml += createDistrictInput(index, i, d.name);
        });

        card.innerHTML = `
            <div style="background: var(--gray-50); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid var(--gray-200);">
                <div style="display: flex; gap: 0.5rem; align-items: flex-start; margin-bottom: 1rem;">
                    <div style="flex: 1;">
                        <input
                            type="text"
                            name="regencies[${index}][name]"
                            class="form-control regency-name-input"
                            placeholder="Nama Kabupaten/Kota"
                            value="${escapeHtml(name)}"
                            required
                        >
                    </div>
                    <button type="button" class="btn btn-sm btn-danger remove-regency-btn" title="Hapus Kabupaten/Kota">
                        &times;
                    </button>
                </div>
                <div class="districts-container" style="margin-left: 1rem; border-left: 2px solid var(--gray-200); padding-left: 1rem;">
                    <label style="font-size: 0.875rem; color: var(--gray-600); margin-bottom: 0.5rem; display: block;">Kecamatan</label>
                    <div class="districts-list">
                        ${districtsHtml}
                    </div>
                    <button type="button" class="btn btn-sm btn-outline add-district-btn" style="margin-top: 0.5rem; font-size: 0.75rem;">
                        + Tambah Kecamatan
                    </button>
                </div>
            </div>
        `;

        return card;
    }

    // Create a district input
    function createDistrictInput(regencyIndex, districtIndex, districtName = '') {
        return `
            <div class="district-input-group" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;">
                <input
                    type="text"
                    name="regencies[${regencyIndex}][districts][${districtIndex}][name]"
                    class="form-control district-name-input"
                    placeholder="Nama Kecamatan"
                    value="${escapeHtml(districtName)}"
                    style="flex: 1;"
                >
                <button type="button" class="btn btn-sm btn-outline remove-district-btn" title="Hapus Kecamatan">
                    &times;
                </button>
            </div>
        `;
    }

    // Escape HTML for values
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Add a new district to a card
    function addDistrict(card, regencyIndex) {
        const districtsList = card.querySelector('.districts-list');
        const districtCount = districtsList.querySelectorAll('.district-input-group').length;
        const districtHtml = createDistrictInput(regencyIndex, districtCount);

        districtsList.insertAdjacentHTML('beforeend', districtHtml);

        // Attach remove listener to new district
        const newDistrict = districtsList.lastElementChild;
        const removeBtn = newDistrict.querySelector('.remove-district-btn');
        removeBtn.addEventListener('click', function() {
            newDistrict.remove();
            reindexAllCards();
        });
    }

    // Add a new regency
    function addRegency(regencyData = null) {
        const container = document.getElementById('regencies-container');
        const currentIndex = container.querySelectorAll('.regency-card').length;
        const card = createRegencyCard(currentIndex, regencyData);

        // Add event listener to remove button
        const removeBtn = card.querySelector('.remove-regency-btn');
        removeBtn.addEventListener('click', function() {
            card.remove();
            reindexAllCards();
        });

        // Add event listener to add district button
        const addDistrictBtn = card.querySelector('.add-district-btn');
        addDistrictBtn.addEventListener('click', function() {
            addDistrict(card, currentIndex);
        });

        // Attach remove listeners to existing districts
        card.querySelectorAll('.remove-district-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                btn.closest('.district-input-group').remove();
                reindexAllCards();
            });
        });

        container.appendChild(card);
    }

    // Initialize with existing data
    function initializeWithExistingData() {
        existingData.regencies.forEach(regency => {
            addRegency(regency);
        });
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        const addRegencyBtn = document.getElementById('add-regency-btn');
        addRegencyBtn.addEventListener('click', function() {
            addRegency();
        });

        // Load existing data
        initializeWithExistingData();

        // Setup delete confirmation input
        const confirmInput = document.getElementById('delete-confirm-input');
        const confirmBtn = document.getElementById('confirm-delete-btn');

        confirmInput.addEventListener('input', function() {
            if (this.value.toUpperCase() === 'HAPUS') {
                confirmBtn.disabled = false;
            } else {
                confirmBtn.disabled = true;
            }
        });
    });
})();

// Close modal on background click
document.addEventListener('click', function(e) {
    if (e.target.id === 'delete-modal-1') closeDeleteModal(1);
    if (e.target.id === 'delete-modal-2') closeDeleteModal(2);
});
</script>
@endpush
