@extends('layouts.app')

@section('title', 'Kalender Libur Kampus')
@section('page_title', 'Kalender Libur & Batas Jam Keberangkatan')

@section('content')
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px;">

    <!-- Holidays List -->
    <div class="card" style="grid-column: span 2;">
        <div class="card-header">
            <span class="card-title">🏖️ Jadwal Libur Kampus & Batas Keluar Pondok</span>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Kampus</th>
                        <th>Keterangan Libur</th>
                        <th>Periode Libur</th>
                        <th>Batas Jam Keluar Pondok</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($holidays as $h)
                        <tr>
                            <td><strong>{{ $h->campus->name }}</strong></td>
                            <td>{{ $h->name }}</td>
                            <td>
                                {{ $h->start_date->format('d/m/Y') }} s/d {{ $h->end_date->format('d/m/Y') }}
                            </td>
                            <td>
                                <strong style="color:var(--danger)">Maksimal {{ $h->departure_cutoff_time }} WIB</strong><br>
                                <small style="color:var(--text-muted);">Setelah jam ini, scan keluar ditolak tanpa izin.</small>
                            </td>
                            <td>
                                <span class="badge {{ $h->is_active ? 'badge-hijau' : 'badge-merah' }}">
                                    {{ $h->is_active ? 'Aktif' : 'Non-aktif' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center; padding:32px; color:var(--text-muted);">
                                Belum ada kalender libur kampus yang ditetapkan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Holiday Period Form -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">+ Tetapkan Periode Libur</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.holidays.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Kampus Terkait <span style="color:var(--danger)">*</span></label>
                    <select name="campus_id" class="form-select" required>
                        <option value="">-- Pilih Kampus --</option>
                        @foreach($campuses as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Periode Libur <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Libur Semester Genap / Hari Raya" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Mulai <span style="color:var(--danger)">*</span></label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Tanggal Selesai <span style="color:var(--danger)">*</span></label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Batas Jam Terakhir Keluar Pondok <span style="color:var(--danger)">*</span></label>
                    <input type="time" name="departure_cutoff_time" class="form-control" value="09:00" required>
                    <small style="color:var(--text-muted); font-size:11px;">Setelah jam ini, santri yang memindai keluar pondok menuju kampus ini ditolak otomatis kecuali memiliki izin resmi.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Tambahan</label>
                    <input type="text" name="notes" class="form-control" placeholder="Instruksi pengurus...">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">Simpan Kalender Libur</button>
            </form>
        </div>
    </div>

</div>
@endsection
