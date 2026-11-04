@extends('layouts.app')

@section('title', 'Edit Data Santri')
@section('page_title', 'Perbarui Data Santri')

@section('content')
<div style="max-width: 680px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Edit Data: {{ $santri->name }}</span>
            <a href="{{ route('admin.santri.index') }}" class="btn btn-outline btn-sm">Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.santri.update', $santri) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Nomor Induk / NIUP <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="niup" class="form-control" value="{{ old('niup', $santri->niup) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Lengkap Santri <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $santri->name) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Jenis Kelamin <span style="color:var(--danger)">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="L" {{ old('gender', $santri->gender) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('gender', $santri->gender) === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Kampus Tujuan Kuliah</label>
                        <select name="campus_id" class="form-select">
                            <option value="">-- Pilih Kampus --</option>
                            @foreach($campuses as $c)
                                <option value="{{ $c->id }}" {{ old('campus_id', $santri->campus_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Program Studi</label>
                        <select name="program_id" class="form-select">
                            <option value="">-- Pilih Prodi --</option>
                            @foreach($programs as $p)
                                <option value="{{ $p->id }}" {{ old('program_id', $santri->program_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Kamar Asrama</label>
                    <select name="room_id" class="form-select">
                        <option value="">-- Pilih Kamar --</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}" {{ old('room_id', $santri->room_id) == $r->id ? 'selected' : '' }}>
                                {{ $r->name }} ({{ $r->area->name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Status Keaktifan Santri</label>
                    <select name="is_active" class="form-select" required>
                        <option value="1" {{ old('is_active', $santri->is_active) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !old('is_active', $santri->is_active) ? 'selected' : '' }}>Non-aktif (Histori Tetap Ada)</option>
                    </select>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
                    <a href="{{ route('admin.santri.index') }}" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
