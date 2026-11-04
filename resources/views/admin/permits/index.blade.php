@extends('layouts.app')

@section('title', 'Perizinan Keluar Santri')
@section('page_title', 'Pengelolaan Izin Keluar Santri')

@section('content')
<div style="display:flex; flex-direction:column; gap:24px;">

    <!-- Action & Filter Header -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-body" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px;">
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('admin.permits.index') }}" class="btn {{ empty($status) ? 'btn-primary' : 'btn-outline' }} btn-sm">Semua</a>
                <a href="{{ route('admin.permits.index', ['status' => 'pending']) }}" class="btn {{ $status === 'pending' ? 'btn-primary' : 'btn-outline' }} btn-sm">Menunggu (Pending)</a>
                <a href="{{ route('admin.permits.index', ['status' => 'approved']) }}" class="btn {{ $status === 'approved' ? 'btn-primary' : 'btn-outline' }} btn-sm">Disetujui</a>
                <a href="{{ route('admin.permits.index', ['status' => 'used']) }}" class="btn {{ $status === 'used' ? 'btn-primary' : 'btn-outline' }} btn-sm">Sedang Digunakan</a>
                <a href="{{ route('admin.permits.index', ['status' => 'completed']) }}" class="btn {{ $status === 'completed' ? 'btn-primary' : 'btn-outline' }} btn-sm">Selesai</a>
                <a href="{{ route('admin.permits.index', ['status' => 'rejected']) }}" class="btn {{ $status === 'rejected' ? 'btn-primary' : 'btn-outline' }} btn-sm">Ditolak</a>
            </div>

            <a href="{{ route('admin.permits.create') }}" class="btn btn-primary">
                + Ajukan Izin Baru
            </a>
        </div>
    </div>

    <!-- Permits Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Santri</th>
                        <th>Tujuan</th>
                        <th>Mulai Berlaku</th>
                        <th>Durasi</th>
                        <th>Batas Kembali</th>
                        <th>Alasan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permits as $permit)
                        <tr>
                            <td>
                                <strong>{{ $permit->santri->name }}</strong><br>
                                <small style="color:var(--text-muted);">NIUP: {{ $permit->santri->niup }}</small>
                            </td>
                            <td>
                                <span class="badge {{ $permit->destination_type === 'kampus' ? 'badge-teal' : 'badge-kuning' }}">
                                    {{ ucfirst(str_replace('_', ' ', $permit->destination_type)) }}
                                </span>
                                <div style="font-weight:600; margin-top:2px;">{{ $permit->destination_name }}</div>
                            </td>
                            <td>{{ $permit->start_time->format('d/m/Y H:i') }}</td>
                            <td>{{ $permit->duration_hours }} Jam</td>
                            <td><strong>{{ $permit->deadline->format('d/m/Y H:i') }}</strong></td>
                            <td style="max-width:220px;">
                                <div style="font-size:13px;">{{ $permit->reason }}</div>
                                @if($permit->rejection_reason)
                                    <small style="color:var(--danger);">Alasan ditolak: {{ $permit->rejection_reason }}</small>
                                @endif
                            </td>
                            <td>
                                @php
                                    $badgeClass = match($permit->status) {
                                        'approved' => 'badge-hijau',
                                        'pending' => 'badge-kuning',
                                        'rejected' => 'badge-merah',
                                        'used' => 'badge-teal',
                                        'completed' => 'badge-teal',
                                        default => 'badge-kuning'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ ucfirst($permit->status) }}
                                </span>
                            </td>
                            <td>
                                @if($permit->status === 'pending')
                                    <div style="display:flex; gap:6px;">
                                        <form action="{{ route('admin.permits.approve', $permit) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm" title="Setujui Izin">
                                                ✓ Setujui
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-outline btn-sm" style="color:var(--danger); border-color:#FECACA;" onclick="openRejectModal('{{ $permit->id }}', '{{ $permit->santri->name }}')">
                                            ✕ Tolak
                                        </button>
                                    </div>
                                @else
                                    <small style="color:var(--text-muted);">
                                        {{ $permit->approver ? 'Oleh ' . $permit->approver->name : '-' }}
                                    </small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:32px; color:var(--text-muted);">
                                Belum ada perizinan santri pada filter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($permits->hasPages())
            <div style="padding:16px;">
                {{ $permits->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Reject Modal -->
<div id="rejectPermitModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:100; align-items:center; justify-content:center; padding:16px;">
    <div class="card" style="width:100%; max-width:440px; margin:0;">
        <div class="card-header">
            <span class="card-title">Tolak Pengajuan Izin</span>
            <button type="button" onclick="closeRejectModal()" style="background:none; border:none; font-size:18px; cursor:pointer;">✕</button>
        </div>
        <div class="card-body">
            <form id="rejectPermitForm" action="" method="POST">
                @csrf
                <p style="font-size:13px; color:var(--text-muted); margin-bottom:14px;">
                    Alasan penolakan izin santri <strong id="rejSantriName"></strong>:
                </p>

                <div class="form-group">
                    <label class="form-label">Alasan Penolakan</label>
                    <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Contoh: Jadwal bertabrakan dengan kegiatan wajib pondok / Batas kuota perizinan habis" required></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-outline" onclick="closeRejectModal()">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak Izin</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openRejectModal(id, name) {
        document.getElementById('rejSantriName').textContent = name;
        document.getElementById('rejectPermitForm').action = "/admin/permits/" + id + "/reject";
        document.getElementById('rejectPermitModal').style.display = 'flex';
    }

    function closeRejectModal() {
        document.getElementById('rejectPermitModal').style.display = 'none';
    }
</script>
@endsection
