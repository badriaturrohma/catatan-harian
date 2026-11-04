@extends('layouts.app')

@section('title', 'Dashboard Pengurus')
@section('page_title', 'Dashboard Monitoring Pengurus')

@section('content')
<div style="display:flex; flex-direction:column; gap:24px;">

    <!-- Filter & Realtime Header -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-body" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px;">
            <div>
                <h2 style="font-size:18px; font-weight:800; color:var(--text-main);">Ringkasan Monitoring Hari Ini</h2>
                <div style="font-size:13px; color:var(--text-muted); margin-top:2px;">
                    Terakhir diperbarui: <strong>{{ now()->format('H:i:s') }} WIB</strong> • Auto-refresh evaluasi aktif
                </div>
            </div>

            <form action="{{ route('admin.dashboard') }}" method="GET" style="display:flex; align-items:center; gap:10px;">
                <select name="campus_id" class="form-select" onchange="this.form.submit()" style="min-width:200px;">
                    <option value="">-- Semua Kampus --</option>
                    @foreach($campuses as $c)
                        <option value="{{ $c->id }}" {{ $campusId == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
                @if($campusId)
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline btn-sm">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Stat Cards Metric Grid -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px;">
        <div class="card" style="margin-bottom:0;">
            <div class="card-body">
                <div style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Santri Aktif</div>
                <div style="font-size:26px; font-weight:800; color:var(--text-main); margin-top:4px;">{{ $totalSantriActive }}</div>
                <div style="font-size:12px; color:var(--primary); margin-top:2px;">Terdaftar di sistem</div>
            </div>
        </div>

        <div class="card" style="margin-bottom:0;">
            <div class="card-body">
                <div style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Berangkat Hari Ini</div>
                <div style="font-size:26px; font-weight:800; color:var(--primary); margin-top:4px;">{{ $totalDepartedToday }}</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">Perjalanan tercatat</div>
            </div>
        </div>

        <div class="card" style="margin-bottom:0;">
            <div class="card-body">
                <div style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Perjalanan Terbuka</div>
                <div style="font-size:26px; font-weight:800; color:#2563EB; margin-top:4px;">{{ $totalOpenJourneys }}</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">Belum kembali pondok</div>
            </div>
        </div>

        <div class="card" style="margin-bottom:0;">
            <div class="card-body">
                <div style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Selesai Hari Ini</div>
                <div style="font-size:26px; font-weight:800; color:var(--success); margin-top:4px;">{{ $totalCompletedToday }}</div>
                <div style="font-size:12px; color:var(--success); margin-top:2px;">Lengkap kembali</div>
            </div>
        </div>

        <div class="card" style="margin-bottom:0; border-color:#FDE68A; background:#FFFBEB;">
            <div class="card-body">
                <div style="font-size:12px; font-weight:700; color:#B45309; text-transform:uppercase;">Peringatan Kuning</div>
                <div style="font-size:26px; font-weight:800; color:var(--warning); margin-top:4px;">{{ $alertYellowCount }}</div>
                <div style="font-size:12px; color:#B45309; margin-top:2px;">Perlu pemantauan</div>
            </div>
        </div>

        <div class="card" style="margin-bottom:0; border-color:#FECACA; background:#FEF2F2;">
            <div class="card-body">
                <div style="font-size:12px; font-weight:700; color:#B91C1C; text-transform:uppercase;">Peringatan Merah</div>
                <div style="font-size:26px; font-weight:800; color:var(--danger); margin-top:4px;">{{ $alertRedCount }}</div>
                <div style="font-size:12px; color:#B91C1C; margin-top:2px;">Segera tindak lanjut</div>
            </div>
        </div>
    </div>

    <!-- Active Alerts Table (Priority) -->
    @if($activeAlerts->isNotEmpty())
        <div class="card" style="border-color:#FCA5A5;">
            <div class="card-header" style="background:#FEF2F2;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:18px;">⚠️</span>
                    <span class="card-title" style="color:#991B1B;">Daftar Peringatan Perjalanan Memerlukan Perhatian ({{ $activeAlerts->count() }})</span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Tingkat</th>
                            <th>Santri</th>
                            <th>Jenis Masalah</th>
                            <th>Terdeteksi Sejak</th>
                            <th>Tindak Lanjut Tercatat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activeAlerts as $alert)
                            <tr>
                                <td>
                                    <span class="badge {{ $alert->highest_level === 'merah' ? 'badge-merah' : 'badge-kuning' }}">
                                        {{ strtoupper($alert->highest_level) }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $alert->journey->santri->name }}</strong><br>
                                    <small style="color:var(--text-muted);">NIUP: {{ $alert->journey->santri->niup }} • {{ $alert->journey->santri->campus->name ?? '-' }}</small>
                                </td>
                                <td>
                                    <strong style="color:{{ $alert->highest_level === 'merah' ? 'var(--danger)' : 'var(--warning)' }}">
                                        {{ $alert->getTypeLabel() }}
                                    </strong>
                                </td>
                                <td>
                                    {{ $alert->detected_at->format('H:i') }} WIB<br>
                                    <small style="color:var(--text-muted);">({{ $alert->detected_at->diffForHumans() }})</small>
                                </td>
                                <td>
                                    @if($alert->followups->isNotEmpty())
                                        <div style="font-size:13px; color:var(--text-main);">
                                            "{{ Str::limit($alert->followups->last()->notes, 50) }}"
                                        </div>
                                        <small style="color:var(--text-muted);">Oleh {{ $alert->followups->last()->actor->name }} ({{ $alert->followups->last()->created_at->format('H:i') }})</small>
                                    @else
                                        <span style="color:#94A3B8; font-size:13px; font-style:italic;">Belum ada tindak lanjut</span>
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline btn-sm" onclick="openFollowupModal('{{ $alert->id }}', '{{ $alert->journey->santri->name }}', '{{ $alert->getTypeLabel() }}')">
                                        + Tindak Lanjut
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Open Journeys in Progress -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">🚶 Santri Sedang Dalam Perjalanan ({{ $openJourneys->count() }})</span>
            <a href="{{ route('admin.reports.daily') }}" class="btn btn-outline btn-sm">Lihat Rekap Harian ➔</a>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Santri</th>
                        <th>Kampus / Kamar</th>
                        <th>Jenis</th>
                        <th>Berangkat</th>
                        <th>Status Perjalanan</th>
                        <th>Aktivitas Terakhir</th>
                        <th>Peringatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($openJourneys as $j)
                        <tr>
                            <td>
                                <strong>{{ $j->santri->name }}</strong><br>
                                <small style="color:var(--text-muted);">NIUP: {{ $j->santri->niup }}</small>
                            </td>
                            <td>
                                {{ $j->santri->campus->name ?? '-' }}<br>
                                <small style="color:var(--text-muted);">{{ $j->santri->room->name ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge {{ $j->type === 'izin' ? 'badge-kuning' : 'badge-teal' }}">
                                    {{ ucfirst($j->type) }}
                                </span>
                                @if($j->permit)
                                    <br><small style="font-size:11px; color:#B45309;">{{ $j->permit->destination_name }}</small>
                                @endif
                            </td>
                            <td>{{ $j->started_at->format('H:i') }} WIB</td>
                            <td>
                                <span class="badge badge-teal" style="font-weight:700;">
                                    {{ ucfirst(str_replace('_', ' ', $j->status)) }}
                                </span>
                            </td>
                            <td>
                                @if($j->latestEvent)
                                    {{ $j->latestEvent->getActivityLabel() }}<br>
                                    <small style="color:var(--text-muted);">{{ $j->latestEvent->occurred_at->format('H:i') }} WIB</small>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($j->activeAlertEpisode)
                                    <span class="badge {{ $j->activeAlertEpisode->highest_level === 'merah' ? 'badge-merah' : 'badge-kuning' }}">
                                        {{ $j->activeAlertEpisode->highest_level === 'merah' ? '🔴 Kritis' : '🟡 Perhatian' }}
                                    </span>
                                @else
                                    <span class="badge badge-hijau">Normal</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:32px; color:var(--text-muted);">
                                Tidak ada santri yang sedang dalam perjalanan terbuka saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Followup Modal -->
<div id="followupModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:100; align-items:center; justify-content:center; padding:16px;">
    <div class="card" style="width:100%; max-width:440px; margin:0;">
        <div class="card-header">
            <span class="card-title">Catat Tindak Lanjut Peringatan</span>
            <button type="button" onclick="closeFollowupModal()" style="background:none; border:none; font-size:18px; cursor:pointer;">✕</button>
        </div>
        <div class="card-body">
            <form id="followupForm" action="" method="POST">
                @csrf
                <p style="font-size:13px; color:var(--text-muted); margin-bottom:14px;">
                    Mencatat tindakan untuk <strong id="followupSantriName"></strong> (<span id="followupProblem"></span>).
                </p>

                <div class="form-group">
                    <label class="form-label">Catatan Tindak Lanjut</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Contoh: Sudah dihubungi via telepon asrama / Petugas pos kampus sedang mencari / Kendaraan santri sempat mogok" required></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-outline" onclick="closeFollowupModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Catatan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function openFollowupModal(id, santri, problem) {
        document.getElementById('followupSantriName').textContent = santri;
        document.getElementById('followupProblem').textContent = problem;
        document.getElementById('followupForm').action = "/admin/dashboard/alerts/" + id + "/followup";
        document.getElementById('followupModal').style.display = 'flex';
    }

    function closeFollowupModal() {
        document.getElementById('followupModal').style.display = 'none';
    }
</script>
@endsection
