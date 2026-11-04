@extends('layouts.app')

@section('title', 'Tambah Pengguna Baru')
@section('page_title', 'Pendaftaran Pengguna Baru')

@section('content')
<div style="max-width: 640px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Formulir Tambah Akun Pengguna</span>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm">Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label">Nama Lengkap Pengguna <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Ust. Ahmad Fauzi / Rahmat Hidayat" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat Email (Digunakan untuk Login) <span style="color:var(--danger)">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="nama@pesantren.id" required>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Peran (Role Akses) <span style="color:var(--danger)">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="petugas" {{ old('role') === 'petugas' ? 'selected' : '' }}>Petugas Pos (Scanner Tapping)</option>
                            <option value="pengurus" {{ old('role') === 'pengurus' ? 'selected' : '' }}>Pengurus (Akses Penuh)</option>
                            <option value="wali" {{ old('role') === 'wali' ? 'selected' : '' }}>Wali Santri (Pemantauan Anak)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Kata Sandi (Password) <span style="color:var(--danger)">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
