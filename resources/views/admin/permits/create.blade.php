@extends('layouts.app')

@section('title', 'Buat Izin Keluar Baru')
@section('page_title', 'Formulir Pengajuan Izin Keluar')

@section('content')
<div style="max-width: 680px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Pengajuan Izin Keluar Santri</span>
            <a href="{{ route('admin.permits.index') }}" class="btn btn-outline btn-sm">Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.permits.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Santri <span style="color:var(--danger)">*</span></label>
                    <select name="santri_id" class="form-select" required>
                        <option value="">-- Pilih Santri --</option>
                        @foreach($santris as $s)
                            <option value="{{ $s->id }}" {{ old('santri_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->niup }} - {{ $s->name }} ({{ $s->campus->name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Tipe Tujuan <span style="color:var(--danger)">*</span></label>
                        <select name="destination_type" class="form-select" required>
                            <option value="kampus" {{ old('destination_type') === 'kampus' ? 'selected' : '' }}>Kampus (Kuliah Tambahan / Lab / Organisasi)</option>
                            <option value="non_kampus" {{ old('destination_type') === 'non_kampus' ? 'selected' : '' }}>Non-Kampus (Keperluan Pribadi / Berobat / Keluarga)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Tempat Tujuan <span style="color:var(--danger)">*</span></label>
                        <input type="text" name="destination_name" class="form-control" value="{{ old('destination_name') }}" placeholder="Contoh: Lab Komputer Kampus A / RS Pondok Indah" required>
                    </div>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Waktu Mulai Berlaku <span style="color:var(--danger)">*</span></label>
                        <input type="datetime-local" name="start_time" class="form-control" value="{{ old('start_time', now()->format('Y-m-d\TH:i')) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Durasi Izin (Jam) <span style="color:var(--danger)">*</span></label>
                        <input type="number" name="duration_hours" class="form-control" value="{{ old('duration_hours', 3) }}" min="1" max="24" required>
                        <small style="color:var(--text-muted); font-size:11px;">Batas waktu kembali dihitung otomatis dari Waktu Mulai + Durasi.</small>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Alasan Keperluan Izin <span style="color:var(--danger)">*</span></label>
                    <textarea name="reason" class="form-control" rows="3" placeholder="Sebutkan keperluan santri dengan jelas..." required>{{ old('reason') }}</textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
                    <a href="{{ route('admin.permits.index') }}" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">Kirim Pengajuan Izin</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
