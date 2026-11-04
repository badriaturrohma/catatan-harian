@extends('layouts.app')

@section('title', 'Pemindai QR Pos Tapping')
@section('page_title', 'Scanner Pos Tapping')

@section('content')
<div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Location & Assignment Header -->
    <div class="card" style="margin-bottom:0;">
        <div class="card-body" style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:16px;">
            <div>
                <div style="font-size:12px; font-weight:700; color:var(--text-muted); text-transform:uppercase;">Pos Aktif Saat Ini</div>
                <div style="font-size:20px; font-weight:800; color:var(--primary); margin-top:2px;">
                    📍 {{ $activeAssignment->location->name ?? 'Belum Ditugaskan' }}
                </div>
                <div style="font-size:13px; color:var(--text-muted);">
                    Tipe Pos: <span style="font-weight:600; text-transform:capitalize;">{{ $activeAssignment->location->type ?? '-' }}</span>
                    @if($activeAssignment && $activeAssignment->location->campus)
                        • Kampus: {{ $activeAssignment->location->campus->name }}
                    @endif
                </div>
            </div>

            @if($assignments->count() > 1)
                <div>
                    <label class="form-label">Ganti Pos Penugasan</label>
                    <select class="form-select" id="selectAssignment" onchange="changeAssignment(this.value)" style="min-width:240px;">
                        @foreach($assignments as $assign)
                            <option value="{{ $assign->id }}" {{ $activeAssignment && $activeAssignment->id == $assign->id ? 'selected' : '' }}>
                                {{ $assign->location->name }} ({{ ucfirst($assign->location->type) }})
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
    </div>

    <!-- Scanner & Manual Entry Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
        
        <!-- Camera Scanner Card -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">📷 Kamera Pemindai QR</span>
                <span id="scanStatusBadge" class="badge badge-teal">Siap Memindai</span>
            </div>
            <div class="card-body" style="display:flex; flex-direction:column; align-items:center;">
                <!-- Video Preview / Viewfinder -->
                <div id="videoContainer" style="width:100%; max-width:400px; height:260px; background:#0F172A; border-radius:12px; position:relative; overflow:hidden; display:flex; align-items:center; justify-content:center;">
                    <video id="cameraVideo" playsinline style="width:100%; height:100%; object-fit:cover; display:none;"></video>
                    
                    <div id="cameraPlaceholder" style="text-align:center; color:#94A3B8; padding:20px;">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="margin-bottom:8px;">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                            <circle cx="12" cy="13" r="4"></circle>
                        </svg>
                        <div style="font-size:14px; font-weight:600;">Kamera Belum Aktif</div>
                        <div style="font-size:12px; margin-top:4px;">Klik 'Nyalakan Kamera' atau gunakan input kode di bawah.</div>
                    </div>

                    <!-- Scanning laser line animation -->
                    <div id="scanLaser" style="display:none; position:absolute; top:0; left:0; right:0; height:3px; background:#14B8A6; box-shadow:0 0 10px #14B8A6; animation: scanAnim 2s infinite ease-in-out;"></div>
                </div>

                <div style="display:flex; gap:10px; margin-top:16px; width:100%; max-width:400px;">
                    <button type="button" class="btn btn-outline" id="btnToggleCam" onclick="toggleCamera()" style="flex:1;">
                        ▶ Nyalakan Kamera
                    </button>
                </div>

                <!-- Simulation & Manual Token Input -->
                <div style="width:100%; max-width:400px; margin-top:20px; padding-top:16px; border-top:1px dashed var(--border-soft);">
                    <label class="form-label" style="font-size:12px;">Simulasi Scan Cepat / Input Kode QR</label>
                    <div style="display:flex; gap:8px;">
                        <select id="quickSantriSelect" class="form-select" style="font-size:13px;">
                            <option value="">-- Pilih Santri Contoh --</option>
                            @foreach($activeSantris as $s)
                                <option value="{{ $s->activeQrCredential ? 'SNT-' . $s->activeQrCredential->token_hash : '' }}" data-santri="{{ $s->name }}">
                                    {{ $s->niup }} - {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-primary" onclick="simulateScanSelected()" style="white-space:nowrap;">
                            Cek QR
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Event Input Tab Card -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">✍️ Input Manual Kejadian</span>
                <span class="badge badge-kuning">Perlu Alasan</span>
            </div>
            <div class="card-body">
                <form action="{{ route('officer.manual.record') }}" method="POST">
                    @csrf
                    <input type="hidden" name="assignment_id" value="{{ $activeAssignment->id ?? '' }}">

                    <div class="form-group">
                        <label class="form-label">Santri</label>
                        <select name="santri_id" class="form-select" required>
                            <option value="">-- Pilih Santri --</option>
                            @foreach($activeSantris as $s)
                                <option value="{{ $s->id }}">{{ $s->niup }} - {{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tahap Aktivitas</label>
                        <select name="activity" class="form-select" required>
                            <option value="keluar_pondok">Keluar Pondok</option>
                            <option value="tiba_kampus">Tiba Kampus</option>
                            <option value="keluar_kampus">Keluar Kampus</option>
                            <option value="kembali_pondok">Kembali Pondok</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Waktu Kejadian Riil</label>
                        <input type="datetime-local" name="occurred_at" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}" required max="{{ now()->format('Y-m-d\TH:i') }}">
                        <small style="color:var(--text-muted); font-size:11px;">Maksimal backdate 24 jam. Lewat dari 24 jam memerlukan persetujuan pengurus.</small>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alasan Pencatatan Manual</label>
                        <textarea name="reason" class="form-control" rows="2" placeholder="Contoh: Kamera rusak / Santri lupa bawa kartu / Koneksi pos sempat terputus" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;">
                        Simpan Catatan Manual
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- Confirmation Modal / Card for Scanned QR -->
    <div id="resolveModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:100; align-items:center; justify-content:center; padding:16px;">
        <div class="card" style="width:100%; max-width:480px; margin:0; animation: modalPop 0.2s ease;">
            <div class="card-header" style="background:var(--primary); color:#FFFFFF;">
                <span class="card-title" style="color:#FFFFFF;">Konfirmasi Identitas Santri</span>
                <button type="button" onclick="closeResolveModal()" style="background:none; border:none; color:#FFFFFF; font-size:20px; cursor:pointer;">✕</button>
            </div>
            <div class="card-body">
                <div style="display:flex; align-items:center; gap:16px; margin-bottom:16px;">
                    <div style="width:64px; height:64px; border-radius:12px; background:var(--primary-light); color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:700;">
                        🎓
                    </div>
                    <div>
                        <h3 id="resSantriName" style="font-size:18px; font-weight:800; color:var(--text-main);">-</h3>
                        <div id="resSantriNiup" style="font-size:13px; color:var(--text-muted);">-</div>
                        <div id="resSantriCampus" style="font-size:12px; color:var(--primary); font-weight:600;">-</div>
                    </div>
                </div>

                <div id="resPermitNotice" style="display:none; padding:10px; background:#FEF3C7; border:1px solid #FDE68A; border-radius:8px; font-size:12px; color:#B45309; margin-bottom:16px;">
                    📝 <strong>Ada Izin Aktif:</strong> <span id="resPermitText"></span>
                </div>

                <div style="margin-top:16px;">
                    <label class="form-label" style="font-size:13px; font-weight:700;">Pilih Aktivitas yang Dikonfirmasi:</label>
                    <div id="resActivitiesList" style="display:flex; flex-direction:column; gap:10px;">
                        <!-- Buttons dynamically rendered here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Events of the Day by this Officer -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📋 Riwayat Scan Hari Ini Oleh Anda</span>
            <span class="badge badge-teal">{{ $recentEvents->count() }} Tercatat</span>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Waktu Scan</th>
                        <th>NIUP</th>
                        <th>Nama Santri</th>
                        <th>Aktivitas</th>
                        <th>Lokasi Pos</th>
                        <th>Metode</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentEvents as $ev)
                        <tr>
                            <td><strong>{{ $ev->occurred_at->format('H:i:s') }}</strong></td>
                            <td>{{ $ev->journey->santri->niup ?? '-' }}</td>
                            <td>{{ $ev->journey->santri->name ?? '-' }}</td>
                            <td>
                                <span class="badge badge-teal">{{ $ev->getActivityLabel() }}</span>
                            </td>
                            <td>{{ $ev->location->name ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $ev->method === 'manual' ? 'badge-kuning' : 'badge-hijau' }}">
                                    {{ ucfirst($ev->method) }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-outline btn-sm" onclick="openCorrectionModal('{{ $ev->id }}', '{{ $ev->journey->santri->name ?? '' }}', '{{ $ev->getActivityLabel() }}')">
                                    Ajukan Koreksi
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; padding:32px; color:var(--text-muted);">
                                Belum ada riwayat scan untuk shift hari ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Correction Modal -->
<div id="correctionModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:100; align-items:center; justify-content:center; padding:16px;">
    <div class="card" style="width:100%; max-width:440px; margin:0;">
        <div class="card-header">
            <span class="card-title">Ajukan Koreksi Event</span>
            <button type="button" onclick="closeCorrectionModal()" style="background:none; border:none; font-size:18px; cursor:pointer;">✕</button>
        </div>
        <div class="card-body">
            <form action="{{ route('officer.corrections.request') }}" method="POST">
                @csrf
                <input type="hidden" name="target_event_id" id="corrEventId">

                <p style="font-size:13px; color:var(--text-muted); margin-bottom:14px;">
                    Mengajukan revisi data tapping untuk <strong id="corrSantriName"></strong> (<span id="corrActivity"></span>).
                </p>

                <div class="form-group">
                    <label class="form-label">Waktu Kejadian yang Benar</label>
                    <input type="datetime-local" name="occurred_at" class="form-control" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Alasan Koreksi</label>
                    <textarea name="reason" class="form-control" rows="3" placeholder="Sebutkan alasan detail mengapa event perlu dikoreksi..." required></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:8px;">
                    <button type="button" class="btn btn-outline" onclick="closeCorrectionModal()">Batal</button>
                    <button type="submit" class="btn btn-primary">Kirim Permohonan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    @keyframes scanAnim {
        0% { top: 0; }
        50% { top: 100%; }
        100% { top: 0; }
    }
    @keyframes modalPop {
        from { transform: scale(0.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
</style>
@endsection

@section('scripts')
<script>
    let currentAssignmentId = {{ $activeAssignment->id ?? 'null' }};
    let cameraStream = null;
    let resolvedData = null;

    function changeAssignment(id) {
        currentAssignmentId = id;
    }

    function toggleCamera() {
        const video = document.getElementById('cameraVideo');
        const placeholder = document.getElementById('cameraPlaceholder');
        const laser = document.getElementById('scanLaser');
        const btn = document.getElementById('btnToggleCam');

        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
            video.style.display = 'none';
            placeholder.style.display = 'block';
            laser.style.display = 'none';
            btn.textContent = '▶ Nyalakan Kamera';
            btn.classList.remove('btn-danger');
            btn.classList.add('btn-outline');
        } else {
            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                    .then(stream => {
                        cameraStream = stream;
                        video.srcObject = stream;
                        video.play();
                        video.style.display = 'block';
                        placeholder.style.display = 'none';
                        laser.style.display = 'block';
                        btn.textContent = '⏹ Hentikan Kamera';
                        btn.classList.remove('btn-outline');
                        btn.classList.add('btn-danger');
                    })
                    .catch(err => {
                        alert('Tidak dapat mengakses kamera browser: ' + err.message + '\nAnda dapat menggunakan input manual atau simulasi QR di bawah.');
                    });
            } else {
                alert('Browser ini tidak mendukung akses kamera langsung. Silakan gunakan tombol simulasi QR atau input manual.');
            }
        }
    }

    function simulateScanSelected() {
        const select = document.getElementById('quickSantriSelect');
        const token = select.value;
        if (!token) {
            alert('Silakan pilih salah satu santri contoh terlebih dahulu.');
            return;
        }
        resolveToken(token);
    }

    function resolveToken(token) {
        if (!currentAssignmentId) {
            alert('Petugas belum memiliki penugasan pos aktif.');
            return;
        }

        fetch("{{ route('officer.qr.resolve') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                token: token,
                assignment_id: currentAssignmentId
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                resolvedData = res.data;
                openResolveModal(res.data);
            } else {
                alert('Gagal: ' + (res.message || 'QR tidak valid atau santri tidak aktif.'));
            }
        })
        .catch(err => {
            alert('Kesalahan jaringan: ' + err.message);
        });
    }

    function openResolveModal(data) {
        document.getElementById('resSantriName').textContent = data.santri.name;
        document.getElementById('resSantriNiup').textContent = 'NIUP: ' + data.santri.niup + ' • ' + (data.santri.program || 'Reguler');
        document.getElementById('resSantriCampus').textContent = (data.santri.campus || 'Kampus Utama') + ' • Kamar: ' + (data.santri.room || '-');

        const permitNotice = document.getElementById('resPermitNotice');
        if (data.active_permit) {
            permitNotice.style.display = 'block';
            document.getElementById('resPermitText').textContent = data.active_permit.destination_name + ' (Batas: ' + data.active_permit.deadline + ')';
        } else {
            permitNotice.style.display = 'none';
        }

        const list = document.getElementById('resActivitiesList');
        list.innerHTML = '';

        if (!data.allowed_activities || data.allowed_activities.length === 0) {
            list.innerHTML = '<div style="color:var(--danger); font-size:13px; padding:10px; background:#FEE2E2; border-radius:8px;">Tidak ada aksi yang diizinkan untuk status santri saat ini di pos ini.</div>';
        } else {
            data.allowed_activities.forEach(act => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'btn btn-primary';
                btn.style.width = '100%';
                btn.style.textAlign = 'left';
                btn.style.justifyContent = 'space-between';
                btn.innerHTML = `
                    <span><strong>${act.label}</strong><br><small style="font-size:11px; opacity:0.85;">${act.description}</small></span>
                    <span>➔</span>
                `;
                btn.onclick = () => commitEvent(data.credential_id, act.code);
                list.appendChild(btn);
            });
        }

        document.getElementById('resolveModal').style.display = 'flex';
    }

    function closeResolveModal() {
        document.getElementById('resolveModal').style.display = 'none';
        resolvedData = null;
    }

    function commitEvent(credentialId, activity) {
        const requestId = 'REQ-' + Date.now() + '-' + Math.random().toString(36).substring(2, 9);

        fetch("{{ route('officer.events.record') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                credential_id: credentialId,
                assignment_id: currentAssignmentId,
                activity: activity,
                request_id: requestId
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                alert('✓ ' + res.message);
                closeResolveModal();
                window.location.reload();
            } else {
                alert('Gagal: ' + res.message);
            }
        })
        .catch(err => {
            alert('Kesalahan jaringan: ' + err.message);
        });
    }

    function openCorrectionModal(id, santri, activity) {
        document.getElementById('corrEventId').value = id;
        document.getElementById('corrSantriName').textContent = santri;
        document.getElementById('corrActivity').textContent = activity;
        document.getElementById('correctionModal').style.display = 'flex';
    }

    function closeCorrectionModal() {
        document.getElementById('correctionModal').style.display = 'none';
    }
</script>
@endsection
