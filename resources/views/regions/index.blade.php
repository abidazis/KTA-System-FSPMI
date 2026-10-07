@extends('layouts.app')

@section('title', 'Data Wilayah')
@section('header', 'Data Wilayah')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Daftar Wilayah Indonesia</h3>
        <div style="display:flex;gap:0.75rem;">
            <a href="{{ route('regions.create') }}" class="btn btn-primary">+ Tambah Provinsi</a>
            <a href="{{ route('regions.template') }}" class="btn btn-success">
                <span style="margin-right: 0.25rem;">📥</span> Download Template
            </a>
            <form action="{{ route('regions.import') }}" method="POST" enctype="multipart/form-data" style="display:inline;">
                @csrf
                <input type="file" name="file" accept=".xlsx,.xls" style="display:none;" id="region-file" onchange="this.form.submit()">
                <label for="region-file" class="btn btn-outline" style="cursor:pointer;">Import Excel</label>
            </form>
        </div>
    </div>

    <div style="padding: 1rem;">
        {{-- Summary Cards --}}
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: var(--primary); color: white; padding: 1rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;">{{ $provinces->total() }}</div>
                <div style="font-size: 0.875rem;">Provinsi</div>
            </div>
            <div style="background: var(--success); color: white; padding: 1rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;">{{ $provinces->sum(fn($p) => $p->regencies->count()) }}</div>
                <div style="font-size: 0.875rem;">Kabupaten/Kota</div>
            </div>
            <div style="background: var(--warning); color: white; padding: 1rem; border-radius: 8px; text-align: center;">
                <div style="font-size: 2rem; font-weight: bold;">{{ $provinces->sum(fn($p) => $p->regencies->sum(fn($r) => $r->districts->count())) }}</div>
                <div style="font-size: 0.875rem;">Kecamatan</div>
            </div>
        </div>

        {{-- Province List with Expandable --}}
        <div class="region-accordion">
            @forelse($provinces as $province)
            <div class="region-item" style="border: 1px solid var(--gray-200); border-radius: 8px; margin-bottom: 0.75rem; overflow: hidden;">
                {{-- Province Header --}}
                <div class="region-header" style="display: flex; align-items: center; padding: 1rem; background: var(--gray-50); cursor: pointer;" onclick="toggleRegion(this)">
                    <span class="toggle-icon" style="margin-right: 0.75rem; font-size: 1.25rem; transition: transform 0.2s;">▶</span>
                    <div style="flex: 1;">
                        <strong style="color: var(--primary); font-size: 1rem;">{{ $province->name }}</strong>
                    </div>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <span class="badge badge-ready">{{ $province->regencies->count() }} Kabupaten/Kota</span>
                        <span class="badge badge-generated">{{ $province->regencies->sum(fn($r) => $r->districts->count()) }} Kecamatan</span>
                        <a href="{{ route('regions.edit', $province) }}" class="btn btn-sm btn-outline" onclick="event.stopPropagation();">Edit</a>
                        <a href="{{ route('regions.show', $province) }}" class="btn btn-sm btn-primary" onclick="event.stopPropagation();">Detail</a>
                    </div>
                </div>

                {{-- Regency/District List (Hidden by default) --}}
                <div class="region-content" style="display: none; padding: 1rem; background: white;">
                    @forelse($province->regencies as $regency)
                    <div style="margin-bottom: 1rem; padding-left: 1rem; border-left: 3px solid var(--primary);">
                        <div style="display: flex; align-items: center; margin-bottom: 0.5rem;">
                            <strong style="color: var(--gray-700);">{{ $regency->name }}</strong>
                            <span class="badge badge-ready" style="margin-left: 0.5rem; font-size: 0.75rem;">{{ $regency->districts->count() }} Kec.</span>
                        </div>
                        <div style="padding-left: 1rem; font-size: 0.875rem; color: var(--gray-600);">
                            {{ $regency->districts->pluck('name')->join(', ') }}
                        </div>
                    </div>
                    @empty
                    <p style="color: var(--gray-500); font-style: italic; padding-left: 1rem;">Belum ada kabupaten/kota.</p>
                    @endforelse
                </div>
            </div>
            @empty
            <div class="empty-state" style="text-align: center; padding: 3rem;">
                <p style="color: var(--gray-500); margin-bottom: 1rem;">Belum ada data wilayah.</p>
                <a href="{{ route('regions.create') }}" class="btn btn-primary">+ Tambah Provinsi</a>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($provinces->hasPages())
        <div style="margin-top: 1.5rem;">
            {{ $provinces->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .region-item:hover .region-header {
        background: var(--gray-100);
    }
    .region-content {
        border-top: 1px solid var(--gray-200);
    }
</style>
@endpush

@push('scripts')
<script>
    function toggleRegion(header) {
        const item = header.closest('.region-item');
        const content = item.querySelector('.region-content');
        const icon = item.querySelector('.toggle-icon');

        if (content.style.display === 'none') {
            content.style.display = 'block';
            icon.style.transform = 'rotate(90deg)';
        } else {
            content.style.display = 'none';
            icon.style.transform = 'rotate(0deg)';
        }
    }
</script>
@endpush
