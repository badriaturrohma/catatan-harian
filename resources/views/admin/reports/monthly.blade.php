@extends('layouts.app')

@section('title', 'Rekap Bulanan Perjalanan')
@section('page_title', 'Laporan Rekapitulasi Bulanan')

@section('content')
<div style="display:flex; flex-direction:column; gap:24px;">

    <!-- Filter Bar -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-body" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px;">
            <form action="{{ route('admin.reports.monthly') }}" method="GET" style="display:flex; flex-wrap:wrap; align-items:center; gap:10px;">
                <div>
                    <label class="form-label" style="font-size:11px; margin-bottom:2px;">Pilih Bulan & Tahun</label>
                    <input type="month" name="month" class="form-control" value="{{ $yearMonth }}" style="max-width:180px;">
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
                    <button type="submit" class="btn btn-primary btn-sm">Tampilkan</button>
                    @if($yearMonth !== now()->format('Y-m') || $campusId)
                        <a href="{{ route('admin.reports.monthly') }}" class="btn btn-outline btn-sm">Bulan Ini</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Monthly Summary Tiles -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:16px;">
        <div class="card" style="margin-bottom:0;">
            <div class="card-body">
                <div style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Periode Laporan</div>
                <div style="font-size:24px; font-weight:800; color:var(--text-main); margin-top:4px;">{{ $report['month_name'] }}</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">Akumulasi bulanan</div>
            </div>
        </div>

        <div class="card" style="margin-bottom:0;">
            <div class="card-body">
                <div style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Total Perjalanan</div>
                <div style="font-size:26px; font-weight:800; color:var(--primary); margin-top:4px;">{{ $report['total_journeys'] }}</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">Keberangkatan tercatat</div>
            </div>
        </div>

        <div class="card" style="margin-bottom:0;">
            <div class="card-body">
                <div style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Santri Unik Bulanan</div>
                <div style="font-size:26px; font-weight:800; color:var(--success); margin-top:4px;">{{ $report['unique_santri'] }}</div>
                <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">Santri bepergian unik bulan ini</div>
            </div>
        </div>
    </div>

    <!-- Daily Breakdown Table -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">Rincian Per Tanggal ({{ $report['month_name'] }})</span>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Total Perjalanan</th>
                        <th>Santri Unik</th>
                        <th>Lengkap Kembali</th>
                        <th>Incomplete</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['daily_breakdown'] as $day)
                        <tr>
                            <td>
                                <strong>{{ \Carbon\Carbon::parse($day['date'])->translatedFormat('d F Y') }}</strong>
                            </td>
                            <td>{{ $day['total_journeys'] }}</td>
                            <td>{{ $day['unique_santri'] }}</td>
                            <td>{{ $day['complete'] }}</td>
                            <td>
                                @if($day['incomplete'] > 0)
                                    <span class="badge badge-merah">{{ $day['incomplete'] }}</span>
                                @else
                                    0
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.reports.daily', ['date' => $day['date']]) }}" class="btn btn-outline btn-sm">
                                    Buka Rekap Harian ➔
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
