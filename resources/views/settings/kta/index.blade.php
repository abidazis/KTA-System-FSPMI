@extends('layouts.app')

@section('title', 'Pengaturan KTA')
@section('header', 'Pengaturan KTA')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Pengaturan Masa Berlaku KTA</h3>
        <div style="display: flex; gap: 0.5rem;">
            <button type="button" class="btn btn-outline" onclick="toggleAddForm()">
                + Tambah Pengaturan
            </button>
        </div>
    </div>

    <div id="add-form" style="display: none; padding: 1.5rem; background: var(--gray-50); border-bottom: 1px solid var(--gray-200);">
        <h4 style="margin-bottom: 1rem;">Tambah Pengaturan Baru</h4>
        <form action="{{ route('settings.kta.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Key (Slug)</label>
                    <input type="text" name="key" placeholder="kta_masa_berlaku" required pattern="[a-z0-9_]+" style="font-family: monospace;">
                    <small>Huruf kecil & angka saja</small>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Label</label>
                    <input type="text" name="label" placeholder="Masa Berlaku KTA" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Tipe</label>
                    <select name="type" required>
                        <option value="number">Angka</option>
                        <option value="text">Teks</option>
                        <option value="select">Pilihan</option>
                        <option value="boolean">Ya/Tidak</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 1rem; margin-bottom: 1rem;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Nilai Default</label>
                    <input type="text" name="value" placeholder="5" required>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label>Opsi (JSON, untuk tipe Pilihan)</label>
                    <input type="text" name="options" placeholder='["5 tahun","3 tahun","1 tahun"]'>
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label>Deskripsi</label>
                <input type="text" name="description" placeholder="Lama masa berlaku KTA dalam tahun">
            </div>
            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <button type="button" class="btn btn-outline" onclick="toggleAddForm()">Batal</button>
            </div>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>Pengaturan</th>
                <th>Nilai</th>
                <th>Deskripsi</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($settings as $setting)
            <tr>
                <td>
                    <strong>{{ $setting->label }}</strong>
                    <br>
                    <small style="color: var(--gray-500); font-family: monospace;">{{ $setting->key }}</small>
                </td>
                <td>
                    <form action="{{ route('settings.kta.update', $setting) }}" method="POST" style="display: inline-flex; gap: 0.5rem; align-items: center;">
                        @csrf
                        @method('PUT')
                        @if($setting->type === 'boolean')
                            <select name="value" onchange="this.form.submit()" style="padding: 0.375rem 0.75rem; border-radius: 6px;">
                                <option value="1" {{ $setting->value == '1' ? 'selected' : '' }}>Ya</option>
                                <option value="0" {{ $setting->value == '0' ? 'selected' : '' }}>Tidak</option>
                            </select>
                        @elseif($setting->type === 'select' && $setting->options)
                            <select name="value" onchange="this.form.submit()" style="padding: 0.375rem 0.75rem; border-radius: 6px;">
                                @foreach(json_decode($setting->options, true) as $option)
                                <option value="{{ $option }}" {{ $setting->value == $option ? 'selected' : '' }}>{{ $option }}</option>
                                @endforeach
                            </select>
                        @else
                            <input type="{{ $setting->type === 'number' ? 'number' : 'text' }}"
                                   name="value"
                                   value="{{ $setting->value }}"
                                   style="padding: 0.375rem 0.75rem; border-radius: 6px; width: 100px;"
                                   onblur="this.form.submit()">
                        @endif
                    </form>
                </td>
                <td>
                    <small>{{ $setting->description ?? '-' }}</small>
                </td>
                <td>
                    @if($setting->is_active)
                        <span class="badge badge-active">Aktif</span>
                    @else
                        <span class="badge badge-inactive">Nonaktif</span>
                    @endif
                </td>
                <td>
                    <div style="display: flex; gap: 0.25rem;">
                        <form action="{{ route('settings.kta.toggle', $setting) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-sm btn-outline" title="{{ $setting->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                @if($setting->is_active)
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path><line x1="12" y1="2" x2="12" y2="12"></line></svg>
                                @else
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                @endif
                            </button>
                        </form>
                        <form action="{{ route('settings.kta.destroy', $setting) }}" method="POST" style="display: inline;" onsubmit="return confirm('Yakin hapus pengaturan ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline" title="Hapus">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="empty-state">
                    <p>Belum ada pengaturan. Klik "+ Tambah Pengaturan" untuk menambahkan.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script>
function toggleAddForm() {
    const form = document.getElementById('add-form');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>
@endpush
@endsection
