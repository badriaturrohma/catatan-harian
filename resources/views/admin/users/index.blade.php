@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page_title', 'Manajemen Akun Pengguna & Password')

@section('content')
<div style="display:flex; flex-direction:column; gap:24px;">

    <!-- Action & Filter Header -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-body" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px;">
            <form action="{{ route('admin.users.index') }}" method="GET" style="display:flex; flex-wrap:wrap; align-items:center; gap:10px; flex:1;">
                <input type="text" name="search" class="form-control" placeholder="Cari nama atau email..." value="{{ $search }}" style="max-width:240px;">
                
                <select name="role" class="form-select" style="max-width:180px;">
                    <option value="">Semua Peran (Role)</option>
                    <option value="pengurus" {{ $role === 'pengurus' ? 'selected' : '' }}>Pengurus</option>
                    <option value="petugas" {{ $role === 'petugas' ? 'selected' : '' }}>Petugas Pos</option>
                    <option value="wali" {{ $role === 'wali' ? 'selected' : '' }}>Wali Santri</option>
                </select>

                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                @if($search || $role)
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline btn-sm">Reset</a>
                @endif
            </form>

            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                + Tambah Pengguna Baru
            </a>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Nama Pengguna</th>
                        <th>Email Login</th>
                        <th>Peran (Role)</th>
                        <th>No. Telepon / WA</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                <strong>{{ $user->name }}</strong>
                                @if($user->id === auth()->id())
                                    <span style="font-size:11px; color:var(--primary); font-weight:700;">(Akun Anda)</span>
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge-role badge-{{ $user->role }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td>{{ $user->phone ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'badge-hijau' : 'badge-merah' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Non-aktif' }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline btn-sm" title="Ubah Nama atau Ganti Password">
                                        ✏️ Edit / Ganti Password
                                    </a>

                                    @if($user->id !== auth()->id())
                                        <form action="{{ route('admin.users.toggle', $user) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline btn-sm" style="font-size:12px;">
                                                {{ $user->is_active ? 'Non-aktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:32px; color:var(--text-muted);">
                                Tidak ada data pengguna yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div style="padding:16px;">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
