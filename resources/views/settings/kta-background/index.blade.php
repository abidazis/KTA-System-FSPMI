@extends('layouts.app')

@section('title', 'Pengaturan Background KTA')
@section('header', 'Pengaturan Background KTA')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Daftar Background KTA</h3>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
            <a href="{{ route('print.index') }}" class="btn btn-sm btn-outline">Kembali</a>
        </div>
    </div>

    <div class="card-body">

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Form Import --}}
        <div class="mb-4" style="background:#f8fafc;padding:1.5rem;border-radius:0.5rem;border:1px solid #e2e8f0;">
            <h4 style="margin:0 0 1rem;font-size:1rem;font-weight:600;">Import Background Baru</h4>

            <form action="{{ route('settings.kta-background.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label for="name" style="font-weight:500;display:block;margin-bottom:0.25rem;">Nama Template</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: KTA FSPMI 2024" required value="{{ old('name') }}">
                    @error('name')
                        <span class="text-danger" style="font-size:0.875rem;">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="front_image" style="font-weight:500;display:block;margin-bottom:0.25rem;">
                            Background Depan (FRONT)
                        </label>
                        <input type="file" name="front_image" id="front_image" class="form-control" accept="image/png,image/jpeg,image/jpg" required>
                        <small style="color:#64748b;">Format: PNG/JPG/JPEG. Direkomendasikan PNG transparan.</small>
                        @error('front_image')
                            <span class="text-danger" style="font-size:0.875rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="back_image" style="font-weight:500;display:block;margin-bottom:0.25rem;">
                            Background Belakang (BACK)
                        </label>
                        <input type="file" name="back_image" id="back_image" class="form-control" accept="image/png,image/jpeg,image/jpg" required>
                        <small style="color:#64748b;">Format: PNG/JPG/JPEG. Direkomendasikan PNG transparan.</small>
                        @error('back_image')
                            <span class="text-danger" style="font-size:0.875rem;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label style="display:flex;align-items:center;gap:0.5rem;cursor:pointer;">
                        <input type="checkbox" name="is_active" value="1">
                        <span>Jadikan template aktif</span>
                    </label>
                    <small style="color:#64748b;display:block;margin-top:0.25rem;">Template aktif akan digunakan untuk generate KTA.</small>
                </div>

                <button type="submit" class="btn btn-primary">Import Background</button>
            </form>
        </div>

        {{-- Daftar Background --}}
        @if($backgrounds->isEmpty())
            <div style="text-align:center;padding:2rem;color:#64748b;">
                <p style="margin:0;">Belum ada background KTA yang diimport.</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="table" style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:2px solid #e2e8f0;">
                            <th style="text-align:left;padding:0.5rem;">Nama</th>
                            <th style="text-align:center;padding:0.5rem;">Preview Front</th>
                            <th style="text-align:center;padding:0.5rem;">Preview Back</th>
                            <th style="text-align:center;padding:0.5rem;">Status</th>
                            <th style="text-align:center;padding:0.5rem;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backgrounds as $bg)
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:0.75rem;vertical-align:middle;font-weight:500;">
                                    {{ $bg->name }}
                                    <div style="font-size:0.75rem;color:#94a3b8;margin-top:0.25rem;">
                                        {{ $bg->created_at->format('d M Y') }}
                                    </div>
                                </td>
                                <td style="padding:0.5rem;text-align:center;vertical-align:middle;">
                                    @if($bg->front_image)
                                        <img
                                            src="{{ Storage::disk('local')->url($bg->front_image) }}"
                                            alt="Front"
                                            style="width:120px;height:auto;border-radius:4px;border:1px solid #e2e8f0;"
                                        >
                                    @else
                                        <span style="color:#94a3b8;">-</span>
                                    @endif
                                </td>
                                <td style="padding:0.5rem;text-align:center;vertical-align:middle;">
                                    @if($bg->back_image)
                                        <img
                                            src="{{ Storage::disk('local')->url($bg->back_image) }}"
                                            alt="Back"
                                            style="width:120px;height:auto;border-radius:4px;border:1px solid #e2e8f0;"
                                        >
                                    @else
                                        <span style="color:#94a3b8;">-</span>
                                    @endif
                                </td>
                                <td style="padding:0.75rem;text-align:center;vertical-align:middle;">
                                    @if($bg->is_active)
                                        <span style="background:#dcfce7;color:#166534;padding:0.25rem 0.75rem;border-radius:999px;font-size:0.75rem;font-weight:600;">Aktif</span>
                                    @else
                                        <span style="background:#f1f5f9;color:#64748b;padding:0.25rem 0.75rem;border-radius:999px;font-size:0.75rem;font-weight:500;">Tidak Aktif</span>
                                    @endif
                                </td>
                                <td style="padding:0.75rem;text-align:center;vertical-align:middle;">
                                    <div style="display:flex;gap:0.5rem;justify-content:center;flex-wrap:wrap;">
                                        @if(!$bg->is_active)
                                            <form action="{{ route('settings.kta-background.set-active', $bg) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline">Aktifkan</button>
                                            </form>
                                        @endif
                                        <form action="{{ route('settings.kta-background.destroy', $bg) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus background ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
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
</div>
@endsection
