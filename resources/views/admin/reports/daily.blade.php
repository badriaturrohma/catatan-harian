@extends('layouts.app')

@section('title', 'Rekap Harian Perjalanan')
@section('page_title', 'Laporan Rekapitulasi Harian')

@section('content')
<div style="display:flex; flex-direction:column; gap:24px;">

    <!-- Filter & Action Bar -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-body" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px;">
            <form action="{{ route('admin.reports.daily') }}" method="GET" style="display:flex; flex-wrap:wrap; align-items:center; gap:10px; flex:1;">
                <div>
                    <label class="form-label" style="font-size:11px; margin-bottom:2px;">Pilih Tanggal Bebas</label>
                    <input type="date" name="date" class="form-control" value="{{ $date }}" style="max-width:180px;">
                </div>

                <div>
                    <label class="form-label" style="font-size:11px; margin-bottom:2px;">Filter Kampus</label>
                    <select name="campus_id" class="form-select" style="max-width:200px;">
                        <option value="">Semua Kampus</option>
                        @foreach($campuses as $c)
                            <option value="{{ $c->id }}" {{ $campusId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="align-self:flex-end;">
                    <button type="submit" class="btn btn-primary btn-sm">Tampilkan Laporan</button>
                    @if($date !== now()->toDateString() || $campusId)
                        <a href="{{ route('admin.reports.daily') }}" class="btn btn-outline btn-sm">Hari Ini</a>
                    @endif
                </div>
            </form>

            <div style="display:flex; gap:8px;">
                <a href="{{ route('admin.reports.export-csv', ['date' => $date, 'campus_id' => $campusId]) }}" class="btn btn-outline btn-sm">
                    📥 Ekspor CSV
                </a>
                <a href="{{ route('admin.reports.print-daily', ['date' => $date, 'campus_id' => $campusId]) }}" target="_blank" class="btn btn-primary btn-sm">
                    🖨️ Cetak / PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Metrics Grid -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:14px;">
        <div class="card" style="margin-bottom:0;">
            <div class="card-body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Total Perjalanan</div>
                <div style="font-size:24px; font-weight:800; color:var(--text-main); margin-top:2px;">{{ $report['summary']['total_journeys'] }}</div>
            </div>
        </div>

        <div class="card" style="margin-bottom:0;">
            <div class="card-body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Santri Unik</div>
                <div style="font-size:24px; font-weight:800; color:var(--primary); margin-top:2px;">{{ $report['summary']['unique_santri'] }}</div>
            </div>
        </div>

        <div class="card" style="margin-bottom:0;">
            <div class="card-body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Lengkap</div>
                <div style="font-size:24px; font-weight:800; color:var(--success); margin-top:2px;">{{ $report['summary']['complete'] }}</div>
            </div>
        </div>

        <div class="card" style="margin-bottom:0;">
            <div class="card-body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Tidak Lengkap</div>
                <div style="font-size:24px; font-weight:800; color:var(--danger); margin-top:2px;">{{ $report['summary']['incomplete'] }}</div>
            </div>
        </div>

        <div class="card" style="margin-bottom:0;">
            <div class="card-body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Masih Terbuka</div>
                <div style="font-size:24px; font-weight:800; color:#2563EB; margin-top:2px;">{{ $report['summary']['open'] }}</div>
            </div>
        </div>

        <div class="card" style="margin-bottom:0;">
            <div class="card-body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Input Manual</div>
                <div style="font-size:24px; font-weight:800; color:var(--warning); margin-top:2px;">{{ $report['summary']['manual_count'] }}</div>
            </div>
        </div>

        <div class="card" style="margin-bottom:0;">
            <div class="card-body" style="padding:16px;">
                <div style="font-size:11px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Pernah Alert</div>
                <div style="font-size:24px; font-weight:800; color:#DC2626; margin-top:2px;">{{ $report['summary']['alert_count'] }}</div>
            </div>
        </div>
    </div>

    <!-- Detailed Drill-down Table -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Rincian Perjalanan Tanggal {{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</span>
            <span class="badge badge-teal">{{ count($report['rows']) }} Catatan</span>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>NIUP & Nama</th>
                        <th>Kampus / Kamar</th>
                        <th>Jenis</th>
                        <th>Keluar Pondok</th>
                        <th>Tiba Kampus</th>
                        <th>Keluar Kampus</th>
                        <th>Kembali Pondok</th>
                        <th>Durasi</th>
                        <th>Status</th>
                        <th>Peringatan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($report['rows'] as $r)
                        <tr>
                            <td>
                                <strong>{{ $r['santri_name'] }}</strong><br>
                                <small style="color:var(--text-muted);">NIUP: {{ $r['niup'] }}</small>
                            </td>
                            <td>
                                {{ $r['campus'] }}<br>
                                <small style="color:var(--text-muted);">{{ $r['room'] }}</small>
                            </td>
                            <td>
                                <span class="badge {{ $r['type'] === 'izin' ? 'badge-kuning' : 'badge-teal' }}">
                                    {{ ucfirst($r['type']) }}
                                </span>
                                @if($r['permit_info'])
                                    <br><small style="font-size:11px; color:#B45309;">{{ $r['permit_info'] }}</small>
                                @endif
                            </td>
                            <td>{{ $r['keluar_pondok_at'] }}</td>
                            <td>{{ $r['tiba_kampus_at'] }}</td>
                            <td>{{ $r['keluar_kampus_at'] }}</td>
                            <td>{{ $r['kembali_pondok_at'] }}</td>
                            <td>{{ $r['duration'] }}</td>
                            <td>
                                <span class="badge badge-teal">
                                    {{ ucfirst(str_replace('_', ' ', $r['status'])) }}
                                </span>
                                @if($r['is_incomplete'])
                                    <br><span class="badge badge-merah" style="margin-top:2px;">Incomplete</span>
                                @endif
                                @if($r['has_manual'])
                                    <br><span class="badge badge-kuning" style="margin-top:2px;">Manual</span>
                                @endif
                            </td>
                            <td>
                                @if($r['highest_alert'])
                                    <span class="badge {{ $r['highest_alert'] === 'merah' ? 'badge-merah' : 'badge-kuning' }}">
                                        {{ strtoupper($r['highest_alert']) }}
                                    </span>
                                @else
                                    <span style="color:#94A3B8; font-size:12px;">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align:center; padding:36px; color:var(--text-muted);">
                                Tidak ada data perjalanan yang tercatat pada tanggal {{ $date }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
