@extends('layouts.app')

@section('title', 'Kelola Nomor Anggota')

@section('header', 'Kelola Nomor Anggota')

@section('content')
{{-- Stats Cards --}}
<div class="stats-grid">
    <div class="stat-card primary">
        <div class="stat-icon">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>
        </div>
        <div class="stat-value">{{ number_format($stats['total']) }}</div>
        <div class="stat-label">Total Nomor</div>
    </div>
    <div class="stat-card success">
        <div class="stat-icon">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <div class="stat-value">{{ number_format($stats['available']) }}</div>
        <div class="stat-label">Tersedia</div>
    </div>
    <div class="stat-card warning">
        <div class="stat-icon">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        </div>
        <div class="stat-value">{{ number_format($stats['used']) }}</div>
        <div class="stat-label">Terpakai</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        </div>
        <div class="stat-value" id="next-number-preview">
            @if($activeFormula)
                <span style="font-size: 1.25rem;">{{ $activeFormula->buildSampleNumber() }}</span>
            @else
                <span style="font-size: 1rem; color: var(--warning);">Belum Ada Formula</span>
            @endif
        </div>
        <div class="stat-label">Nomor Berikutnya</div>
    </div>
</div>

{{-- Generate New Numbers --}}
@if($activeFormula)
<div class="card">
    <div class="card-header">
        <h3>Generate Nomor Anggota Baru</h3>
    </div>
    <div style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
            <label for="generate-count">Jumlah</label>
            <input type="number" id="generate-count" value="10" min="1" max="100" style="width: 100%;">
        </div>
        <div class="form-group" style="margin-bottom: 0; min-width: 200px;">
            <label for="generate-company">Perusahaan (opsional)</label>
            <select id="generate-company">
                <option value="">Semua / Tanpa Perusahaan</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}">{{ $company->kode }} - {{ $company->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="button" class="btn btn-primary" onclick="generateNumbers()">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Generate
        </button>
    </div>
</div>
@else
<div class="alert alert-warning">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 0.5rem;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
    <strong>Perhatian:</strong> Tidak ada formula aktif. Silakan aktifkan atau buat formula terlebih dahulu di <a href="{{ route('member-number-formulas.index') }}" style="color: var(--warning); font-weight: 600;">Formulasi Nomor Anggota</a>.
</div>
@endif

{{-- Filter & Search --}}
<div class="card">
    <div class="card-header">
        <h3>Daftar Nomor Anggota</h3>
    </div>

    <form method="GET" action="{{ route('member-numbers.index') }}" class="filters">
        <div class="form-group" style="margin-bottom: 0; flex: 2; min-width: 200px;">
            <label for="search">Cari</label>
            <input type="text" name="search" id="search" value="{{ $filters['search'] }}" placeholder="Nomor atau nama anggota...">
        </div>
        <div class="form-group" style="margin-bottom: 0; min-width: 150px;">
            <label for="status">Status</label>
            <select name="status" id="status">
                <option value="all" {{ $filters['status'] == 'all' ? 'selected' : '' }}>Semua</option>
                <option value="available" {{ $filters['status'] == 'available' ? 'selected' : '' }}>Tersedia</option>
                <option value="used" {{ $filters['status'] == 'used' ? 'selected' : '' }}>Terpakai</option>
            </select>
        </div>
        <div class="form-group" style="margin-bottom: 0; min-width: 180px;">
            <label for="company_id">Perusahaan</label>
            <select name="company_id" id="company_id">
                <option value="">Semua</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ $filters['company_id'] == $company->id ? 'selected' : '' }}>{{ $company->kode }}</option>
                @endforeach
            </select>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: flex-end; margin-bottom: 0;">
            <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            <a href="{{ route('member-numbers.index') }}" class="btn btn-outline btn-sm">Reset</a>
        </div>
    </form>

    @if($memberNumbers->isEmpty())
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path></svg>
            <p>Belum ada nomor anggota.</p>
            @if($activeFormula)
                <p style="font-size: 0.9rem; color: var(--gray-400);">Gunakan tombol Generate di atas untuk membuat nomor baru.</p>
            @endif
        </div>
    @else
        <table>
            <thead>
                <tr>
                    <th>Nomor Anggota</th>
                    <th>Perusahaan</th>
                    <th>Status</th>
                    <th>Pengguna</th>
                    <th>Tanggal Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($memberNumbers as $mn)
                <tr>
                    <td>
                        <code style="background: var(--gray-100); padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-family: 'Courier New', monospace; font-size: 0.95rem; font-weight: 600; color: var(--primary);">
                            {{ $mn->number }}
                        </code>
                    </td>
                    <td>
                        @if($mn->company)
                            <span>{{ $mn->company->kode }}</span>
                        @else
                            <span style="color: var(--gray-400);">-</span>
                        @endif
                    </td>
                    <td>
                        @if($mn->isAvailable())
                            <span class="badge badge-ready">Tersedia</span>
                        @else
                            <span class="badge badge-generated">Terpakai</span>
                        @endif
                    </td>
                    <td>
                        @if($mn->member)
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                {{ $mn->member->nama }}
                            </div>
                        @else
                            <span style="color: var(--gray-400);">-</span>
                        @endif
                    </td>
                    <td style="color: var(--gray-500); font-size: 0.9rem;">
                        {{ $mn->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td>
                        @if($mn->isAvailable())
                            <button type="button"
                                    class="btn btn-success btn-sm"
                                    onclick="openAssignModal({{ $mn->id }}, '{{ $mn->number }}')"
                                    title="Berikan kepada anggota">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                Gunakan
                            </button>
                        @else
                            <a href="{{ route('members.show', $mn->member) }}" class="btn btn-outline btn-sm" title="Lihat anggota">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                Lihat
                            </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div style="padding: 1rem;">
            {{ $memberNumbers->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- Assign Modal --}}
<div id="assign-modal" class="fspmi-modal-overlay">
    <div class="fspmi-modal" style="max-width: 500px;">
        <div class="fspmi-modal-header">
            <div class="fspmi-modal-icon info">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
            </div>
            <div class="fspmi-modal-header-text">
                <div class="fspmi-modal-title">Berikan Nomor Anggota</div>
                <div class="fspmi-modal-subtitle" id="assign-modal-number">Nomor: -</div>
            </div>
        </div>
        <div class="fspmi-modal-body">
            <div class="form-group">
                <label for="assign-member-search">Cari Anggota</label>
                <input type="text" id="assign-member-search" placeholder="Ketik nama anggota..." autocomplete="off">
                <input type="hidden" id="assign-member-id">
                <input type="hidden" id="assign-member-number-id">
            </div>
            <div id="assign-member-list" style="max-height: 200px; overflow-y: auto; border: 1px solid var(--gray-200); border-radius: 0.5rem; padding: 0.5rem;">
                <div style="color: var(--gray-400); text-align: center; padding: 1rem;">Ketik untuk mencari anggota...</div>
            </div>
        </div>
        <div class="fspmi-modal-footer">
            <button type="button" class="btn btn-cancel" onclick="closeAssignModal()">Batal</button>
            <button type="button" class="btn btn-confirm success" id="assign-confirm-btn" disabled onclick="confirmAssign()">
                Berikan
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Generate Numbers
function generateNumbers() {
    var count = parseInt(document.getElementById('generate-count').value) || 10;
    var companyId = document.getElementById('generate-company').value || null;

    if (count < 1 || count > 100) {
        FSPMIModal.warning('Jumlah harus antara 1 dan 100.');
        return;
    }

    FSPMIModal.confirm(
        'Generate ' + count + ' nomor anggota baru?',
        'Konfirmasi Generate',
        'Ya, Generate',
        'Batal',
        'info'
    ).then(function(ok) {
        if (!ok) return;

        var btn = event.target.closest('button');
        btn.disabled = true;
        btn.innerHTML = '<svg class="animate-spin" width="18" height="18" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Generating...';

        fetch('{{ route('member-numbers.generate') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ count: count, company_id: companyId })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Generate';

            if (data.success) {
                FSPMIModal.success(data.message, 'Berhasil!')
                    .then(function() { location.reload(); });
            } else {
                FSPMIModal.error(data.message, 'Gagal');
            }
        })
        .catch(function(error) {
            btn.disabled = false;
            btn.innerHTML = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg> Generate';
            FSPMIModal.error('Terjadi kesalahan saat generate nomor.');
        });
    });
}

// Assign Modal
function openAssignModal(memberNumberId, memberNumber) {
    document.getElementById('assign-member-number-id').value = memberNumberId;
    document.getElementById('assign-modal-number').textContent = 'Nomor: ' + memberNumber;
    document.getElementById('assign-member-search').value = '';
    document.getElementById('assign-member-id').value = '';
    document.getElementById('assign-member-list').innerHTML = '<div style="color: var(--gray-400); text-align: center; padding: 1rem;">Ketik untuk mencari anggota...</div>';
    document.getElementById('assign-confirm-btn').disabled = true;
    document.getElementById('assign-modal').classList.add('active');
    document.getElementById('assign-member-search').focus();
}

function closeAssignModal() {
    document.getElementById('assign-modal').classList.remove('active');
}

// Search members
var memberSearchTimeout;
document.getElementById('assign-member-search').addEventListener('input', function(e) {
    var search = e.target.value.trim();

    clearTimeout(memberSearchTimeout);

    if (search.length < 2) {
        document.getElementById('assign-member-list').innerHTML = '<div style="color: var(--gray-400); text-align: center; padding: 1rem;">Ketik minimal 2 karakter...</div>';
        return;
    }

    memberSearchTimeout = setTimeout(function() {
        var companyId = document.getElementById('generate-company').value || '';

        fetch('{{ route('member-numbers.available-members') }}?search=' + encodeURIComponent(search) + '&company_id=' + companyId, {
            headers: { 'Accept': 'application/json' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success && data.data.length > 0) {
                var html = '';
                data.data.forEach(function(member) {
                    html += '<div class="member-item" style="padding: 0.75rem; cursor: pointer; border-bottom: 1px solid var(--gray-100);" onclick="selectMember(' + member.id + ', \'' + member.nama.replace(/'/g, "\\'") + '\')">';
                    html += '<div style="font-weight: 600;">' + member.nama + '</div>';
                    if (member.company) {
                        html += '<div style="font-size: 0.8rem; color: var(--gray-500);">' + member.company + '</div>';
                    }
                    html += '</div>';
                });
                document.getElementById('assign-member-list').innerHTML = html;
            } else {
                document.getElementById('assign-member-list').innerHTML = '<div style="color: var(--gray-400); text-align: center; padding: 1rem;">Tidak ada anggota tanpa nomor.</div>';
            }
        });
    }, 300);
});

function selectMember(memberId, memberName) {
    document.getElementById('assign-member-id').value = memberId;
    document.getElementById('assign-member-search').value = memberName;
    document.getElementById('assign-confirm-btn').disabled = false;
    document.getElementById('assign-member-list').innerHTML = '<div style="padding: 0.75rem; background: var(--success-light); border-radius: 0.375rem;"><strong>' + memberName + '</strong> dipilih</div>';
}

function confirmAssign() {
    var memberNumberId = document.getElementById('assign-member-number-id').value;
    var memberId = document.getElementById('assign-member-id').value;

    if (!memberId) {
        FSPMIModal.warning('Silakan pilih anggota terlebih dahulu.');
        return;
    }

    var btn = document.getElementById('assign-confirm-btn');
    btn.disabled = true;
    btn.textContent = 'Memproses...';

    fetch('/settings/member-numbers/' + memberNumberId + '/assign', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ member_id: memberId })
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
        btn.disabled = false;
        btn.textContent = 'Berikan';

        if (data.success) {
            closeAssignModal();
            FSPMIModal.success(data.message, 'Berhasil!')
                .then(function() { location.reload(); });
        } else {
            FSPMIModal.error(data.message, 'Gagal');
        }
    })
    .catch(function(error) {
        btn.disabled = false;
        btn.textContent = 'Berikan';
        FSPMIModal.error('Terjadi kesalahan saat memberikan nomor.');
    });
}

// Close modal on backdrop click
document.getElementById('assign-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAssignModal();
    }
});

// Close modal on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeAssignModal();
    }
});
</script>
@endpush
