@extends('layouts.app')

@section('title', 'Master Data Kampus & Pos')
@section('page_title', 'Master Kampus & Lokasi Pos Tapping')

@section('content')
<div style="display:flex; flex-direction:column; gap:24px;">

    <!-- Campuses Section -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">🏢 Daftar Kampus Mitra</span>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Kampus</th>
                            <th>Total Santri</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campuses as $campus)
                            <tr>
                                <td><strong>{{ $campus->code }}</strong></td>
                                <td>{{ $campus->name }}</td>
                                <td>{{ $campus->santris_count }} santri</td>
                                <td>
                                    <span class="badge {{ $campus->is_active ? 'badge-hijau' : 'badge-merah' }}">
                                        {{ $campus->is_active ? 'Aktif' : 'Non-aktif' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center; padding:20px; color:var(--text-muted);">Belum ada data kampus.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Campus Card -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">+ Tambah Kampus Baru</span>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.master.campus.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Kode Kampus</label>
                        <input type="text" name="code" class="form-control" placeholder="Contoh: KMP-C" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nama Kampus</label>
                        <input type="text" name="name" class="form-control" placeholder="Nama resmi perguruan tinggi" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alamat</label>
                        <input type="text" name="address" class="form-control" placeholder="Kota/Kabupaten">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;">Simpan Kampus</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Pos Tapping Locations Section -->
    <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">📍 Lokasi Pos Pemeriksaan Tapping</span>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nama Pos</th>
                            <th>Tipe Pos</th>
                            <th>Kampus Terkait</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locations as $loc)
                            <tr>
                                <td><strong>{{ $loc->name }}</strong></td>
                                <td>
                                    <span class="badge {{ $loc->type === 'pondok' ? 'badge-teal' : 'badge-kuning' }}">
                                        {{ ucfirst($loc->type) }}
                                    </span>
                                </td>
                                <td>{{ $loc->campus->name ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $loc->is_active ? 'badge-hijau' : 'badge-merah' }}">
                                        {{ $loc->is_active ? 'Aktif' : 'Non-aktif' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center; padding:20px; color:var(--text-muted);">Belum ada lokasi pos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Location Pos Card -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">+ Tambah Pos Tapping Baru</span>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.master.location.store') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Nama Pos Lokasi</label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Pos Gerbang Timur Pondok" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tipe Pos</label>
                        <select name="type" class="form-select" required>
                            <option value="pondok">Pos Pondok (Gerbang Keberangkatan / Kepulangan)</option>
                            <option value="kampus">Pos Kampus (Gerbang Tiba / Pulang Kuliah)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kampus Terkait (Jika tipe Kampus)</label>
                        <select name="campus_id" class="form-select">
                            <option value="">-- Tidak Terikat Kampus Tertentu --</option>
                            @foreach($campuses as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;">Simpan Pos Lokasi</button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
