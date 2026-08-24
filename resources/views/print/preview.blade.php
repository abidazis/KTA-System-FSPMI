@extends('layouts.app')

@section('title', 'Preview Batch Cetak')
@section('header', 'Preview Batch Cetak')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Preview Batch ({{ $count }} KTA)</h3>
    </div>

    <div style="background:#fef3c7;padding:1rem;border-radius:0.5rem;margin-bottom:2rem;">
        <strong>Informasi Penting:</strong>
        <ul style="margin:0.5rem 0 0 1.5rem;">
            <li>Halaman ini menampilkan preview sisi depan KTA</li>
            <li>Pastikan halaman depan dan belakang dicetak dengan urutan yang benar</li>
            <li>Gunakan kertas A4 dan pastikan printer diatur untuk cetak duplex</li>
        </ul>
    </div>

    <div style="
        background:#f8fafc;
        padding:2rem;
        border-radius:0.5rem;
        margin-bottom:2rem;
    ">

        <div style="
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(430px,1fr));
            gap:30px;
            justify-items:center;
        ">

            @foreach($members as $kta)

                <div style="
                    background:#fff;
                    padding:15px;
                    border-radius:10px;
                    box-shadow:0 2px 8px rgba(0,0,0,.08);
                ">

                    <div style="
                        font-size:13px;
                        font-weight:700;
                        margin-bottom:12px;
                        text-align:center;
                    ">
                        {{ strtoupper($kta['member']->nama) }}
                        —
                        {{ $kta['member']->nik }}
                    </div>


                    <div style="
                        display:flex;
                        gap:15px;
                        align-items:flex-start;
                    ">

                        {{-- FRONT --}}
                        <div>

                            <div style="
                                text-align:center;
                                font-size:11px;
                                font-weight:700;
                                margin-bottom:8px;
                            ">
                                DEPAN
                            </div>

                            @php
                                $member = $kta['member'];
                                $side = 'front';
                            @endphp

                            @include('print.partials.kta-card')

                        </div>


                        {{-- BACK --}}
                        <div>

                            <div style="
                                text-align:center;
                                font-size:11px;
                                font-weight:700;
                                margin-bottom:8px;
                            ">
                                BELAKANG
                            </div>

                            @php
                                $member = $kta['member'];
                                $side = 'back';
                            @endphp

                            @include('print.partials.kta-card')

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    </div>

    <form action="{{ route('print.store') }}" method="POST">
        @csrf
        @foreach(request('member_ids', []) as $id)
        <input type="hidden" name="member_ids[]" value="{{ $id }}">
        @endforeach

        <div style="display:flex;gap:1rem;">
            <a href="{{ route('print.create') }}" class="btn btn-outline">Pilih Ulang</a>
            <button type="submit" class="btn btn-primary">Buat Batch Cetak</button>
        </div>
    </form>
</div>
@endsection
