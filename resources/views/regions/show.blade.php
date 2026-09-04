@extends('layouts.app')

@section('title', 'Detail Wilayah')
@section('header', $province->name)

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Detail Wilayah: {{ $province->name }}</h3>
        <a href="{{ route('regions.index') }}" class="btn btn-sm btn-outline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Kembali
        </a>
    </div>

    @foreach($province->regencies as $regency)
    <div style="margin-bottom:1.5rem;">
        <h4 style="padding:0.5rem;background:#f1f5f9;border-radius:0.5rem;margin-bottom:0.5rem;">
            {{ $regency->name }}
            <span class="badge badge-draft" style="margin-left:0.5rem;">{{ $regency->districts->count() }} Kecamatan</span>
        </h4>
        <div style="display:flex;flex-wrap:wrap;gap:0.5rem;padding-left:1rem;">
            @foreach($regency->districts as $district)
            <span class="badge badge-ready">{{ $district->name }}</span>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@endsection
