@extends('layouts.app')

@section('title', 'Review Permohonan Koreksi')
@section('page_title', 'Review & Persetujuan Koreksi Event')

@section('content')
<div style="display:flex; flex-direction:column; gap:24px;">

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-body" style="display:flex; align-items:center; gap:8px;">
            <a href="{{ route('admin.corrections.index') }}" class="btn {{ empty($status) ? 'btn-primary' : 'btn-outline' }} btn-sm">Semua</a>
            <a href="{{ route('admin.corrections.index', ['status' => 'pending']) }}" class="btn {{ $status === 'pending' ? 'btn-primary' : 'btn-outline' }} btn-sm">Menunggu Review</a>
            <a href="{{ route('admin.corrections.index', ['status' => 'approved']) }}" class="btn {{ $status === 'approved' ? 'btn-primary' : 'btn-outline' }} btn-sm">Disetujui</a>
            <a href="{{ route('admin.corrections.index', ['status' => 'rejected']) }}" class="btn {{ $status === 'rejected' ? 'btn-primary' : 'btn-outline' }} btn-sm">Ditolak</a>
        </div>
    </div>

    <!-- Corrections Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Santri & Event Sasaran</th>
                        <th>Waktu Lama</th>
                        <th>Waktu Usulan Baru</th>
                        <th>Alasan Petugas</th>
                        <th>Diajukan Oleh</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($corrections as $corr)
                        <tr>
                            <td>
                                <strong>{{ $corr->targetEvent->journey->santri->name ?? '-' }}</strong><br>
                                <small style="color:var(--text-muted);">
                                    NIUP: {{ $corr->targetEvent->journey->santri->niup ?? '-' }} • 
                                    Aktivitas: {{ $corr->targetEvent->getActivityLabel() ?? '-' }}
                                </small>
                            </td>
                            <td>{{ $corr->targetEvent->occurred_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <strong style="color:var(--primary);">
                                    {{ isset($corr->proposed_data['occurred_at']) ? \Carbon\Carbon::parse($corr->proposed_data['occurred_at'])->format('d/m/Y H:i') : '-' }}
                                </strong>
                            </td>
                            <td style="max-width:240px;">
                                <div style="font-size:13px;">{{ $corr->reason }}</div>
                                @if($corr->rejection_reason)
                                    <small style="color:var(--danger);">Ditolak: {{ $corr->rejection_reason }}</small>
                                @endif
                            </td>
                            <td>
                                {{ $corr->requester->name }}<br>
                                <small style="color:var(--text-muted);">{{ $corr->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                <span class="badge {{ $corr->status === 'approved' ? 'badge-hijau' : ($corr->status === 'pending' ? 'badge-kuning' : 'badge-merah') }}">
                                    {{ ucfirst($corr->status) }}
                                </span>
                            </td>
                            <td>
                                @if($corr->status === 'pending')
                                    <div style="display:flex; gap:6px;">
                                        <form action="{{ route('admin.corrections.approve', $corr) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="reason" value="Disetujui oleh pengurus setelah verifikasi">
                                            <button type="submit" class="btn btn-primary btn-sm">Setujui</button>
                                        </form>
                                        <button type="button" class="btn btn-outline btn-sm" onclick="openRejectCorrModal('{{ $corr->id }}')">Tolak</button>
                                    </div>
                                @else
                                    <small style="color:var(--text-muted);">Direview oleh {{ $corr->reviewer->name ?? 'Pengurus' }}</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:32px; color:var(--text-muted);">
                                Tidak ada permohonan koreksi event.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($corrections->hasPages())
            <div style="padding:16px;">
                {{ $corrections->links() }}
            </div>
        @endif
    </div>

</div>

<!-- Reject Modal -->
<div id="rejectCorrModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:100; align-items:center; justify-content:center; padding:16px;">
    <div class="card" style="width:100%; max-width:400px; margin:0;">
        <div class="card-header">
            <span class="card-title">Tolak Koreksi</span>
            <button type="button" onclick="closeRejectCorrModal()" style="background:none; border:none; font-size:18px; cursor:pointer;">✕</button>
        </div>
        <div class="card-body">
            <form id="rejectCorrForm" action="" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Alasan Penolakan Koreksi</label>
                    <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Sebutkan alasan..." required></textarea>
                </div>
                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-outline" onclick="closeRejectCorrModal()">Batal</button>
                    <button type="submit" class="btn btn-danger">Tolak</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openRejectCorrModal(id) {
        document.getElementById('rejectCorrForm').action = "/admin/corrections/" + id + "/reject";
        document.getElementById('rejectCorrModal').style.display = 'flex';
    }
    function closeRejectCorrModal() {
        document.getElementById('rejectCorrModal').style.display = 'none';
    }
</script>
@endsection
