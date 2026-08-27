<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <title>KTA {{ $member->nik }} - Preview</title>

    <style>
        body {
            margin: 0;
            padding: 30px;
            background: #e5e7eb;
            font-family: Arial, Helvetica, sans-serif;
        }

        .preview-wrapper {
            max-width: 800px;
            margin: auto;
        }

        .preview-header {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
        }

        .preview-header h2 {
            margin: 0 0 5px;
        }

        .preview-header p {
            margin: 0;
            color: #64748b;
        }

        .kta-preview-grid {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 40px;
            flex-wrap: wrap;
        }

        .kta-preview-side {
            text-align: center;
        }

        .kta-preview-label {
            margin-bottom: 12px;
            font-weight: 700;
            color: #334155;
        }

        /* Card: landscape 85.6mm x 54mm */
        .kta-card-wrapper {
            width: 85.6mm;
            height: 54mm;
            position: relative;
            overflow: hidden;
            border-radius: 4px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.15);
            margin: 0 auto;
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 30px;
        }

        .btn {
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-primary {
            background: #2563eb;
            color: #fff;
        }

        .btn-outline {
            background: #fff;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
    </style>
</head>

<body>

<div class="preview-wrapper">

    <div class="preview-header">
        <h2>Preview KTA</h2>
        <p>{{ strtoupper($member->nama) }} &mdash; NIK {{ $member->nik }}</p>
    </div>

    <div class="kta-preview-grid">

        {{-- DEPAN --}}
        <div class="kta-preview-side">
            <div class="kta-preview-label">SISI DEPAN</div>
            <div class="kta-card-wrapper">
                @php
                    $side = 'front';
                    $isPdf = false;
                    $ttd_ketua_path = $ttd_ketua_path ?? null;
                    $ttd_sekretaris_path = $ttd_sekretaris_path ?? null;
                @endphp
                @include('print.partials.kta-card-v2')
            </div>
        </div>

        {{-- BELAKANG --}}
        <div class="kta-preview-side">
            <div class="kta-preview-label">SISI BELAKANG</div>
            <div class="kta-card-wrapper">
                @php
                    $side = 'back';
                    $isPdf = false;
                    $ttd_ketua_path = $ttd_ketua_path ?? null;
                    $ttd_sekretaris_path = $ttd_sekretaris_path ?? null;
                @endphp
                @include('print.partials.kta-card-v2')
            </div>
        </div>

    </div>

    <div class="actions">
        <a href="{{ route('members.show', $member) }}" class="btn btn-outline">Kembali</a>
        @if($member->hasPhoto())
            <a href="{{ route('members.kta.download', $member) }}" class="btn btn-primary">Download PDF</a>
        @endif
    </div>

</div>

</body>
</html>
