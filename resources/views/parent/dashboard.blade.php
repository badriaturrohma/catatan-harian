@extends('layouts.app')

@section('title', 'Portal Wali Santri')
@section('page_title', 'Pemantauan Perjalanan Santri')

@section('content')
<div style="display:flex; flex-direction:column; gap:24px;">

    <!-- Top Action & Claim Bar -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-body" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px;">
            <div>
                <h2 style="font-size:18px; font-weight:800; color:var(--text-main);">Selamat Datang, {{ auth()->user()->name }}</h2>
                <div style="font-size:13px; color:var(--text-muted); margin-top:2px;">
                    Portal khusus orang tua/wali untuk memantau perjalanan dan aktivitas santri secara real-time.
                </div>
            </div>

            <div>
                <button type="button" class="btn btn-primary" onclick="openClaimModal()">
                    + Hubungkan Santri (NIUP)
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Sukses / Gagal Khusus Halaman Wali (jika ada) -->
    @if(session('success'))
        <div class="flash-alert flash-success" style="margin-bottom:0;">
            <span>✓ {{ session('success') }}</span>
            <span style="cursor:pointer;" onclick="this.parentElement.remove();">✕</span>
        </div>
    @endif

    @if(session('error'))
        <div class="flash-alert flash-danger" style="margin-bottom:0;">
            <span>✕ {{ session('error') }}</span>
            <span style="cursor:pointer;" onclick="this.parentElement.remove();">✕</span>
        </div>
    @endif

    @if($santris->isEmpty())
        <!-- State Belum Ada Santri Terhubung -->
        <div class="card" style="border-color:#FDE68A; background:#FFFBEB; text-align:center; padding:40px 20px;">
            <div style="font-size:42px; margin-bottom:12px;">👨‍👩‍👧</div>
            <h3 style="font-size:19px; font-weight:800; color:#B45309;">Belum Ada Santri yang Terhubung</h3>
            <p style="font-size:14px; color:#92400E; max-width:520px; margin:8px auto 20px; line-height:1.5;">
                Akun wali Anda saat ini belum terhubung dengan data santri manapun. Silakan masukkan <strong>Nomor Induk / NIUP</strong> anak Anda untuk mulai memantau keberangkatan dan kepulangan mereka.
            </p>
            <button type="button" class="btn btn-primary" onclick="openClaimModal()" style="padding:12px 24px; font-size:15px;">
                + Hubungkan Santri Sekarang
            </button>
        </div>
    @else
        <!-- Daftar Santri yang Terhubung Milik Wali -->
        <div class="card" style="margin-bottom:0;">
            <div class="card-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                <span class="card-title">👨‍👩‍👧 Daftar Anak / Santri Terhubung ({{ $santris->count() }})</span>
                <button type="button" class="btn btn-outline btn-sm" onclick="openClaimModal()">
                    + Tambah Santri Lainnya
                </button>
            </div>
            <div class="card-body" style="padding:16px;">
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:12px;">
                    @foreach($santris as $child)
                        @php
                            $isSelected = $selectedSantri && $selectedSantri->id === $child->id;
                            $relType = $child->pivot->relationship_type ?? 'Wali';
                        @endphp
                        <a href="{{ route('parent.dashboard', ['santri_id' => $child->id]) }}" 
                           style="text-decoration:none; color:inherit; display:block;">
                            <div style="padding:14px; border-radius:10px; border:2px solid {{ $isSelected ? 'var(--primary)' : 'var(--border-soft)' }}; background:{{ $isSelected ? 'var(--primary-light)' : '#FFFFFF' }}; transition:all 0.15s ease;">
                                <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                                    <div style="font-weight:800; font-size:15px; color:{{ $isSelected ? 'var(--primary-dark)' : 'var(--text-main)' }};">
                                        {{ $child->name }}
                                    </div>
                                    <span class="badge {{ $isSelected ? 'badge-teal' : 'badge-hijau' }}" style="font-size:11px;">
                                        {{ $relType }}
                                    </span>
                                </div>
                                <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">
                                    NIUP: <strong>{{ $child->niup }}</strong>
                                </div>
                                <div style="font-size:12px; color:var(--text-muted); margin-top:2px;">
                                    {{ $child->campus->name ?? '-' }} • Kamar: {{ $child->room->name ?? '-' }}
                                </div>
                                @if($isSelected)
                                    <div style="font-size:11px; font-weight:700; color:var(--primary); margin-top:6px;">
                                        ● Sedang Dipantau
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        @if($selectedSantri)
            <!-- Current Real-time Status Card -->
            <div class="card" style="border-left: 5px solid var(--primary); margin-bottom:0;">
                <div class="card-body" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:20px;">
                    <div style="display:flex; align-items:center; gap:16px;">
                        <div style="width:68px; height:68px; border-radius:14px; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:30px;">
                            🎓
                        </div>
                        <div>
                            <div style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Informasi Santri yang Dipilih</div>
                            <h2 style="font-size:22px; font-weight:800; color:var(--text-main); margin-top:2px;">{{ $selectedSantri->name }}</h2>
                            <div style="font-size:13px; color:var(--text-muted);">
                                NIUP: <strong>{{ $selectedSantri->niup }}</strong> • Kampus: <strong>{{ $selectedSantri->campus->name ?? '-' }}</strong> ({{ $selectedSantri->program->name ?? '-' }}) • Kamar: <strong>{{ $selectedSantri->room->name ?? '-' }}</strong>
                            </div>
                        </div>
                    </div>

                    <div style="text-align:right;">
                        <div style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Status Perjalanan Terkini</div>
                        <div style="margin-top:6px;">
                            @if($activeJourney)
                                <span class="badge badge-teal" style="font-size:14px; padding:6px 14px;">
                                    📍 {{ ucfirst(str_replace('_', ' ', $activeJourney->status)) }}
                                </span>
                            @else
                                <span class="badge badge-hijau" style="font-size:14px; padding:6px 14px;">
                                    🏠 Berada di Pondok (Tidak Ada Perjalanan Terbuka)
                                </span>
                            @endif
                        </div>
                        <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">
                            Waktu sistem: {{ now()->format('H:i') }} WIB
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Journey Timeline (if ongoing) -->
            @if($activeJourney)
                <div class="card" style="margin-bottom:0;">
                    <div class="card-header">
                        <span class="card-title">⏱️ Tahapan Perjalanan Aktif Hari Ini (Mulai: {{ $activeJourney->started_at->format('H:i') }} WIB)</span>
                        <span class="badge {{ $activeJourney->type === 'izin' ? 'badge-kuning' : 'badge-teal' }}">
                            {{ ucfirst($activeJourney->type) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div style="display:flex; flex-wrap:wrap; justify-content:space-between; gap:16px; margin:10px 0;">
                            @php
                                $keluarPondok = $activeJourney->events->firstWhere('activity', 'keluar_pondok');
                                $tibaKampus = $activeJourney->events->firstWhere('activity', 'tiba_kampus');
                                $keluarKampus = $activeJourney->events->firstWhere('activity', 'keluar_kampus');
                                $kembaliPondok = $activeJourney->events->firstWhere('activity', 'kembali_pondok');
                            @endphp

                            <div style="flex:1; min-width:140px; padding:12px; background:#F8FAFC; border-radius:8px; border:1px solid {{ $keluarPondok ? 'var(--primary)' : 'var(--border-soft)' }};">
                                <div style="font-size:11px; font-weight:700; color:var(--text-muted);">1. Keluar Pondok</div>
                                <div style="font-size:16px; font-weight:800; color:{{ $keluarPondok ? 'var(--primary)' : '#94A3B8' }}; margin-top:4px;">
                                    {{ $keluarPondok ? $keluarPondok->occurred_at->format('H:i') . ' WIB' : 'Belum tercatat' }}
                                </div>
                            </div>

                            <div style="flex:1; min-width:140px; padding:12px; background:#F8FAFC; border-radius:8px; border:1px solid {{ $tibaKampus ? 'var(--primary)' : 'var(--border-soft)' }};">
                                <div style="font-size:11px; font-weight:700; color:var(--text-muted);">2. Tiba Kampus</div>
                                <div style="font-size:16px; font-weight:800; color:{{ $tibaKampus ? 'var(--primary)' : '#94A3B8' }}; margin-top:4px;">
                                    {{ $tibaKampus ? $tibaKampus->occurred_at->format('H:i') . ' WIB' : 'Belum tercatat' }}
                                </div>
                            </div>

                            <div style="flex:1; min-width:140px; padding:12px; background:#F8FAFC; border-radius:8px; border:1px solid {{ $keluarKampus ? 'var(--primary)' : 'var(--border-soft)' }};">
                                <div style="font-size:11px; font-weight:700; color:var(--text-muted);">3. Keluar Kampus</div>
                                <div style="font-size:16px; font-weight:800; color:{{ $keluarKampus ? 'var(--primary)' : '#94A3B8' }}; margin-top:4px;">
                                    {{ $keluarKampus ? $keluarKampus->occurred_at->format('H:i') . ' WIB' : 'Belum tercatat' }}
                                </div>
                            </div>

                            <div style="flex:1; min-width:140px; padding:12px; background:#F8FAFC; border-radius:8px; border:1px solid {{ $kembaliPondok ? 'var(--primary)' : 'var(--border-soft)' }};">
                                <div style="font-size:11px; font-weight:700; color:var(--text-muted);">4. Kembali Pondok</div>
                                <div style="font-size:16px; font-weight:800; color:{{ $kembaliPondok ? 'var(--primary)' : '#94A3B8' }}; margin-top:4px;">
                                    {{ $kembaliPondok ? $kembaliPondok->occurred_at->format('H:i') . ' WIB' : 'Belum tercatat' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Recent Trips History -->
            <div class="card">
                <div class="card-header">
                    <span class="card-title">📜 Riwayat Perjalanan Sebelumnya ({{ $selectedSantri->name }})</span>
                </div>
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Waktu Keluar</th>
                                <th>Waktu Kembali</th>
                                <th>Status Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentJourneys as $j)
                                <tr>
                                    <td><strong>{{ $j->operational_date->format('d/m/Y') }}</strong></td>
                                    <td>
                                        <span class="badge {{ $j->type === 'izin' ? 'badge-kuning' : 'badge-teal' }}">
                                            {{ ucfirst($j->type) }}
                                        </span>
                                    </td>
                                    <td>{{ $j->started_at->format('H:i') }} WIB</td>
                                    <td>{{ $j->completed_at ? $j->completed_at->format('H:i') . ' WIB' : '-' }}</td>
                                    <td>
                                        <span class="badge {{ $j->status === 'selesai' ? 'badge-hijau' : 'badge-teal' }}">
                                            {{ ucfirst(str_replace('_', ' ', $j->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" style="text-align:center; padding:24px; color:var(--text-muted);">
                                        Belum ada histori perjalanan untuk santri ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif

</div>

<!-- Modal Form Input NIUP (+ Hubungkan Santri) -->
<div id="claimSantriModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:100; align-items:center; justify-content:center; padding:16px;">
    <div class="card" style="width:100%; max-width:460px; margin:0; animation: modalPop 0.2s ease;">
        <div class="card-header" style="background:var(--primary); color:#FFFFFF;">
            <span class="card-title" style="color:#FFFFFF;">+ Hubungkan Santri via NIUP</span>
            <button type="button" onclick="closeClaimModal()" style="background:none; border:none; color:#FFFFFF; font-size:20px; cursor:pointer;">✕</button>
        </div>
        <div class="card-body">
            <form action="{{ route('parent.claim-santri') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="claimNiup">
                        Nomor Induk / NIUP Santri <span style="color:var(--danger)">*</span>
                    </label>
                    <input type="text" name="niup" id="claimNiup" class="form-control" placeholder="Contoh: 2026001" required autofocus>
                    <small style="color:var(--text-muted); font-size:12px; display:block; margin-top:4px;">
                        Masukkan nomor induk santri sesuai kartu santri anak Anda.
                    </small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="claimRelation">
                        Hubungan Keluarga <span style="color:var(--danger)">*</span>
                    </label>
                    <select name="relationship_type" id="claimRelation" class="form-select" required>
                        <option value="Ayah">Ayah</option>
                        <option value="Ibu">Ibu</option>
                        <option value="Wali" selected>Wali</option>
                        <option value="Paman / Bibi">Paman / Bibi</option>
                        <option value="Keluarga">Keluarga Lainnya</option>
                    </select>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
                    <button type="button" class="btn btn-outline" onclick="closeClaimModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Hubungkan Santri</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @keyframes modalPop {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
</style>
@endsection

@section('scripts')
<script>
    function openClaimModal() {
        const modal = document.getElementById('claimSantriModal');
        modal.style.display = 'flex';
        setTimeout(() => {
            document.getElementById('claimNiup').focus();
        }, 100);
    }

    function closeClaimModal() {
        document.getElementById('claimSantriModal').style.display = 'none';
    }

    // Tutup modal jika klik di luar area kartu
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('claimSantriModal');
        if (e.target === modal) {
            closeClaimModal();
        }
    });
</script>
@endsection
