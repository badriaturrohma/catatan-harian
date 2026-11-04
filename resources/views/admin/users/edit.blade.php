@extends('layouts.app')

@section('title', 'Edit Pengguna & Password')
@section('page_title', 'Perbarui Profil & Password')

@section('content')
<div style="max-width: 640px; margin: 0 auto;">
    <div class="card">
        <div class="card-header">
            <span class="card-title">Edit Akun: {{ $user->name }}</span>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm">Kembali</a>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Nama Lengkap Pengguna <span style="color:var(--danger)">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Alamat Email <span style="color:var(--danger)">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Peran (Role Akses) <span style="color:var(--danger)">*</span></label>
                        <select name="role" class="form-select" required>
                            <option value="pengurus" {{ old('role', $user->role) === 'pengurus' ? 'selected' : '' }}>Pengurus</option>
                            <option value="petugas" {{ old('role', $user->role) === 'petugas' ? 'selected' : '' }}>Petugas Pos</option>
                            <option value="wali" {{ old('role', $user->role) === 'wali' ? 'selected' : '' }}>Wali Santri</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Status Keaktifan Akun</label>
                    <select name="is_active" class="form-select" required>
                        <option value="1" {{ old('is_active', $user->is_active) ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ !old('is_active', $user->is_active) ? 'selected' : '' }}>Non-aktif (Tidak Bisa Login)</option>
                    </select>
                </div>

                <!-- Password Change Box -->
                <div style="margin-top:20px; padding:16px; background:#F8FAFC; border:1px solid var(--border-soft); border-radius:10px;">
                    <label class="form-label" style="font-size:14px; font-weight:700; color:var(--primary);">
                        🔑 Ganti Kata Sandi (Password)
                    </label>
                    <input type="password" name="password" class="form-control" placeholder="Ketik kata sandi baru jika ingin mengganti...">
                    <small style="color:var(--text-muted); font-size:12px; display:block; margin-top:4px;">
                        * <em>Kosongkan kolom ini jika tidak ingin mengubah kata sandi yang sudah ada.</em>
                    </small>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
