@extends('layouts.app')

@section('title', 'Data Santri & QR Card')
@section('page_title', 'Data Santri & Kredensial QR')

@section('content')
<div style="display:flex; flex-direction:column; gap:24px;">

    @if(session('plain_token'))
        <div class="card" style="border-color:#6EE7B7; background:#ECFDF5;">
            <div class="card-body" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                <div>
                    <h3 style="font-size:16px; font-weight:800; color:#065F46;">🎉 Token QR Berhasil Digenerate!</h3>
                    <p style="font-size:13px; color:#047857; margin-top:2px;">
                        Token Plaintext: <code style="background:#D1FAE5; padding:2px 8px; border-radius:4px; font-size:14px; font-weight:700;">{{ session('plain_token') }}</code>
                    </p>
                    <small style="color:#065F46;">Token ini siap dipindai oleh scanner pos petugas.</small>
                </div>
            </div>
        </div>
    @endif

    <!-- Search & Action Bar -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-body" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px;">
            <form action="{{ route('admin.santri.index') }}" method="GET" style="display:flex; flex-wrap:wrap; align-items:center; gap:10px; flex:1;">
                <input type="text" name="search" class="form-control" placeholder="Cari nama atau NIUP..." value="{{ $search }}" style="max-width:260px;">
                
                <select name="campus_id" class="form-select" style="max-width:200px;">
                    <option value="">Semua Kampus</option>
                    @foreach($campuses as $c)
                        <option value="{{ $c->id }}" {{ $campusId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>

                <select name="status" class="form-select" style="max-width:160px;">
                    <option value="">Semua Status</option>
                    <option value="1" {{ $status === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ $status === '0' ? 'selected' : '' }}>Non-aktif</option>
                </select>

                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                @if($search || $campusId || $status !== null)
                    <a href="{{ route('admin.santri.index') }}" class="btn btn-outline btn-sm">Reset</a>
                @endif
            </form>

            <a href="{{ route('admin.santri.create') }}" class="btn btn-primary">
                + Tambah Santri Baru
            </a>
        </div>
    </div>

    <!-- Table Santri -->
    <div class="card">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>NIUP</th>
                        <th>Nama Santri</th>
                        <th>L/P</th>
                        <th>Kampus & Program Studi</th>
                        <th>Kamar Asrama</th>
                        <th>Status QR</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($santris as $santri)
                        <tr>
                            <td><strong>{{ $santri->niup }}</strong></td>
                            <td>
                                <strong style="color:var(--text-main);">{{ $santri->name }}</strong>
                            </td>
                            <td>{{ $santri->gender }}</td>
                            <td>
                                {{ $santri->campus->name ?? '-' }}<br>
                                <small style="color:var(--text-muted);">{{ $santri->program->name ?? '-' }}</small>
                            </td>
                            <td>{{ $santri->room->name ?? '-' }}</td>
                            <td>
                                @if($santri->activeQrCredential)
                                    <span class="badge badge-teal">Aktif</span>
                                    <br><small style="color:var(--text-muted); font-size:11px;">Prefix: {{ $santri->activeQrCredential->plaintext_preview }}...</small>
                                @else
                                    <span class="badge badge-kuning">Belum Ada QR</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $santri->is_active ? 'badge-hijau' : 'badge-merah' }}">
                                    {{ $santri->is_active ? 'Aktif' : 'Non-aktif' }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <a href="{{ route('admin.santri.card', $santri) }}" class="btn btn-outline btn-sm" title="Lihat & Cetak Kartu QR">
                                        🪪 Cetak Kartu
                                    </a>
                                    <a href="{{ route('admin.santri.edit', $santri) }}" class="btn btn-outline btn-sm">
                                        ✏️ Edit
                                    </a>
                                    <form action="{{ route('admin.santri.regenerate-qr', $santri) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin me-regenerasi token QR? Token lama akan dicabut.');">
                                        @csrf
                                        <button type="submit" class="btn btn-outline btn-sm" title="Rotasi/Ganti Token QR">
                                            🔄 Rotasi QR
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align:center; padding:32px; color:var(--text-muted);">
                                Tidak ada data santri yang sesuai kriteria pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($santris->hasPages())
            <div style="padding:16px;">
                {{ $santris->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
