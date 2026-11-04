@extends('layouts.app')

@section('title', 'Penugasan Petugas Pos')
@section('page_title', 'Penugasan Petugas Pos Tapping')

@section('content')
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px;">

    <!-- Assignments List -->
    <div class="card" style="grid-column: span 2;">
        <div class="card-header">
            <span class="card-title">🛡️ Daftar Penugasan Petugas Aktif</span>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Petugas</th>
                        <th>Lokasi Pos</th>
                        <th>Mulai Tugas</th>
                        <th>Selesai Tugas</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments as $assign)
                        <tr>
                            <td>
                                <strong>{{ $assign->user->name }}</strong><br>
                                <small style="color:var(--text-muted);">{{ $assign->user->email }}</small>
                            </td>
                            <td>
                                <strong>{{ $assign->location->name }}</strong><br>
                                <small style="color:var(--text-muted);">Tipe: {{ ucfirst($assign->location->type) }}</small>
                            </td>
                            <td>{{ $assign->valid_from->format('d/m/Y H:i') }}</td>
                            <td>{{ $assign->valid_until ? $assign->valid_until->format('d/m/Y H:i') : 'Permanen' }}</td>
                            <td>
                                <span class="badge {{ $assign->isValidNow() ? 'badge-hijau' : 'badge-merah' }}">
                                    {{ $assign->isValidNow() ? 'Berlaku' : 'Tidak Berlaku' }}
                                </span>
                            </td>
                            <td>
                                <form action="{{ route('admin.assignments.toggle', $assign) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline btn-sm">
                                        {{ $assign->is_active ? 'Non-aktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:32px; color:var(--text-muted);">
                                Belum ada penugasan petugas tercatat.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($assignments->hasPages())
            <div style="padding:16px;">
                {{ $assignments->links() }}
            </div>
        @endif
    </div>

    <!-- Create Assignment Form -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">+ Tetapkan Tugas Baru</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.assignments.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Akun Petugas <span style="color:var(--danger)">*</span></label>
                    <select name="user_id" class="form-select" required>
                        <option value="">-- Pilih Petugas --</option>
                        @foreach($officers as $o)
                            <option value="{{ $o->id }}">{{ $o->name }} ({{ $o->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Lokasi Pos Penugasan <span style="color:var(--danger)">*</span></label>
                    <select name="location_id" class="form-select" required>
                        <option value="">-- Pilih Pos --</option>
                        @foreach($locations as $l)
                            <option value="{{ $l->id }}">{{ $l->name }} ({{ ucfirst($l->type) }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Mulai Berlaku <span style="color:var(--danger)">*</span></label>
                    <input type="datetime-local" name="valid_from" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Berlaku Sampai (Opsional)</label>
                    <input type="datetime-local" name="valid_until" class="form-control">
                    <small style="color:var(--text-muted); font-size:11px;">Kosongkan jika shift berkelanjutan/permanen.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Catatan Penugasan</label>
                    <input type="text" name="notes" class="form-control" placeholder="Contoh: Jadwal Shift Pagi Jam 06-14">
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;">Simpan Penugasan</button>
            </form>
        </div>
    </div>

</div>
@endsection
