@extends('layouts.app')

@section('title', 'Tambah Wilayah')
@section('header', 'Tambah Wilayah Baru')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Form Tambah Wilayah</h3>
        <a href="{{ route('regions.index') }}" class="btn btn-outline">Kembali</a>
    </div>

    <form action="{{ route('regions.store') }}" method="POST" id="region-form">
        @csrf

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
                    value="{{ old('name') }}"
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
                    {{-- Dynamic regency cards will be added here --}}
                </div>
                <button type="button" class="btn btn-outline" id="add-regency-btn" style="margin-top: 0.75rem;">
                    + Tambah Kabupaten/Kota
                </button>
            </div>

            <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('regions.index') }}" class="btn btn-outline">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function() {
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

            // Update add-district button with correct index
            const addDistrictBtn = card.querySelector('.add-district-btn');
            if (addDistrictBtn) {
                addDistrictBtn.onclick = function() {
                    addDistrict(card, newIndex);
                };
            }
        });
    }

    // Create a regency card
    function createRegencyCard(index) {
        const card = document.createElement('div');
        card.className = 'regency-card';
        card.dataset.uniqueId = generateUniqueId();
        card.dataset.index = index;

        card.innerHTML = `
            <div style="background: var(--gray-50); padding: 1rem; border-radius: 8px; margin-bottom: 1rem; border: 1px solid var(--gray-200);">
                <div style="display: flex; gap: 0.5rem; align-items: flex-start; margin-bottom: 1rem;">
                    <div style="flex: 1;">
                        <input
                            type="text"
                            name="regencies[${index}][name]"
                            class="form-control regency-name-input"
                            placeholder="Nama Kabupaten/Kota"
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
                        {{-- Districts will be added here --}}
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
    function createDistrictInput(regencyIndex, districtIndex) {
        return `
            <div class="district-input-group" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem; align-items: center;">
                <input
                    type="text"
                    name="regencies[${regencyIndex}][districts][${districtIndex}][name]"
                    class="form-control district-name-input"
                    placeholder="Nama Kecamatan"
                    style="flex: 1;"
                >
                <button type="button" class="btn btn-sm btn-outline remove-district-btn" title="Hapus Kecamatan">
                    &times;
                </button>
            </div>
        `;
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
    function addRegency() {
        const container = document.getElementById('regencies-container');
        const currentIndex = container.querySelectorAll('.regency-card').length;
        const card = createRegencyCard(currentIndex);

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

        container.appendChild(card);
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        const addRegencyBtn = document.getElementById('add-regency-btn');
        addRegencyBtn.addEventListener('click', function() {
            addRegency();
        });

        // Add at least one empty regency on load if none exist
        if (document.querySelectorAll('.regency-card').length === 0) {
            addRegency();
        }
    });
})();
</script>
@endpush
