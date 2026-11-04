@extends('layouts.app')

@section('title', 'Tambah Santri Baru')
@section('page_title', 'Pendaftaran Santri Baru')

@section('content')
<div style="max-width: 680px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Formulir Pendaftaran Santri</span>
            <a href="{{ route('admin.santri.index') }}" class="btn btn-outline btn-sm">Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.santri.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Nomor Induk / NIUP <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="niup" class="form-control" value="{{ old('niup') }}" placeholder="Contoh: 2026004" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Nama Lengkap Santri <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Nama sesuai identitas" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Jenis Kelamin <span style="color:var(--danger)">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="L" {{ old('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Kampus Tujuan Kuliah</label>
                        <select name="campus_id" class="form-select">
                            <option value="">-- Pilih Kampus --</option>
                            @foreach($campuses as $c)
                                <option value="{{ $c->id }}" {{ old('campus_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Program Studi</label>
                        <select name="program_id" class="form-select">
                            <option value="">-- Pilih Prodi --</option>
                            @foreach($programs as $p)
                                <option value="{{ $p->id }}" {{ old('program_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Kamar Asrama</label>
                    <select name="room_id" class="form-select">
                        <option value="">-- Pilih Kamar --</option>
                        @foreach($rooms as $r)
                            <option value="{{ $r->id }}" {{ old('room_id') == $r->id ? 'selected' : '' }}>
                                {{ $r->name }} ({{ $r->area->name ?? '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
                    <a href="{{ route('admin.santri.index') }}" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan & Buat Token QR</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
