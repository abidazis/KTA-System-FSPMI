@extends('layouts.app')

@section('title', 'Background KTA')
@section('header', 'Background KTA')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Daftar Background KTA</h3>
        <a href="{{ route('print.index') }}" class="btn btn-outline">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Kembali
        </a>
    </div>

    {{-- Import Form --}}
    <div style="background:var(--gray-50);padding:1.5rem;border-radius:0.5rem;border:1px solid var(--gray-200);margin-bottom:2rem;">
        <h4 style="margin:0 0 1rem;color:var(--primary);">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="vertical-align:middle;margin-right:0.5rem;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
            Import Background Baru
        </h4>

        <form action="{{ route('settings.kta-background.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="name">Nama Template</label>
                <input type="text" name="name" id="name" placeholder="Contoh: KTA FSPMI 2026" required value="{{ old('name') }}">
                @error('name')
                    <span class="error">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="front_image">Background Depan (FRONT)</label>
                    <input type="file" name="front_image" id="front_image" accept="image/png,image/jpeg,image/jpg" required>
                    <small style="color:var(--gray-500);">Format: PNG/JPG. Direkomendasikan PNG transparan.</small>
                    @error('front_image')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="back_image">Background Belakang (BACK)</label>
                    <input type="file" name="back_image" id="back_image" accept="image/png,image/jpeg,image/jpg" required>
                    <small style="color:var(--gray-500);">Format: PNG/JPG. Direkomendasikan PNG transparan.</small>
                    @error('back_image')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label style="display:flex;align-items:center;gap:0.75rem;cursor:pointer;">
                    <input type="checkbox" name="is_active" value="1" style="width:1.125rem;height:1.125rem;">
                    Jadikan template aktif
                </label>
                <small style="color:var(--gray-500);display:block;margin-top:0.25rem;">Template aktif akan digunakan untuk generate KTA.</small>
            </div>

            <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                Import Background
            </button>
        </form>
    </div>

    {{-- Background List --}}
    @if($backgrounds->isEmpty())
        <div style="text-align:center;padding:3rem;color:var(--gray-500);">
            <p style="font-size:1.1rem;">Belum ada background KTA yang diimport.</p>
        </div>
    @else
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th style="text-align:center;">Preview Front</th>
                        <th style="text-align:center;">Preview Back</th>
                        <th style="text-align:center;">Status</th>
                        <th style="text-align:center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($backgrounds as $bg)
                        <tr>
                            <td>
                                <strong style="color:var(--primary);">{{ $bg->name }}</strong>
                                <div style="font-size:0.8rem;color:var(--gray-400);margin-top:0.25rem;">
                                    {{ $bg->created_at->format('d M Y') }}
                                </div>
                            </td>
                            <td style="text-align:center;">
                                @if($bg->front_image)
                                    <img src="{{ $bg->getFrontImageUrl() }}" alt="Front"
                                         style="width:100px;height:auto;border-radius:0.5rem;border:2px solid var(--gray-200);">
                                @else
                                    <span style="color:var(--gray-400);">-</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @if($bg->back_image)
                                    <img src="{{ $bg->getBackImageUrl() }}" alt="Back"
                                         style="width:100px;height:auto;border-radius:0.5rem;border:2px solid var(--gray-200);">
                                @else
                                    <span style="color:var(--gray-400);">-</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                @if($bg->is_active)
                                    <span class="badge badge-active">✓ Aktif</span>
                                @else
                                    <span class="badge badge-inactive">Tidak Aktif</span>
                                @endif
                            </td>
                            <td style="text-align:center;">
                                <div class="actions" style="justify-content:center;">
                                    @if(!$bg->is_active)
                                        <form action="{{ route('settings.kta-background.set-active', $bg) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                Aktifkan
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('settings.kta-background.destroy', $bg) }}" method="POST" style="display:inline;" id="delete-bg-form-{{ $bg->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="btn btn-sm btn-danger" onclick="FSPMIModal.confirmDelete('Yakin hapus background ini?').then(function(ok){ if(ok){document.getElementById('delete-bg-form-{{ $bg->id }}').submit();} });">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
