@extends('layouts.app')

@section('title', 'Parameter Aturan Berversi')
@section('page_title', 'Konfigurasi Parameter & Aturan Berversi')

@section('content')
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(320px, 1fr)); gap:24px;">

    <!-- Current Active Rules Form -->
    <div class="card" style="grid-column: span 2;">
        <div class="card-header">
            <span class="card-title">⚙️ Parameter Peringatan & Jam Malam Saat Ini</span>
            <span class="badge badge-teal">Aturan Aktif Global</span>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.rules.update') }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="scope" value="global">

                <h4 style="font-size:14px; font-weight:700; color:var(--primary); margin-bottom:12px; border-bottom:1px solid var(--border-soft); padding-bottom:6px;">
                    1. Ambang Peringatan Perjalanan (Belum Tiba di Kampus)
                </h4>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Ambang Kuning (Menit sejak keluar pondok)</label>
                        <input type="number" name="threshold_yellow_arrive_campus" class="form-control" value="{{ $currentGlobalRules['threshold_yellow_arrive_campus'] ?? 15 }}" min="5" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Ambang Merah (Menit sejak keluar pondok)</label>
                        <input type="number" name="threshold_red_arrive_campus" class="form-control" value="{{ $currentGlobalRules['threshold_red_arrive_campus'] ?? 30 }}" min="10" required>
                    </div>
                </div>

                <h4 style="font-size:14px; font-weight:700; color:var(--primary); margin-top:16px; margin-bottom:12px; border-bottom:1px solid var(--border-soft); padding-bottom:6px;">
                    2. Batas Jam Kembali Reguler (Curfew)
                </h4>
                <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Jam Batas Kembali Reguler</label>
                        <input type="time" name="regular_return_curfew" class="form-control" value="{{ $currentGlobalRules['regular_return_curfew'] ?? '16:00' }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kuning (+Menit Lewat Jam Batas)</label>
                        <input type="number" name="curfew_late_yellow" class="form-control" value="{{ $currentGlobalRules['curfew_late_yellow'] ?? 15 }}" min="5" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Merah (+Menit Lewat Jam Batas)</label>
                        <input type="number" name="curfew_late_red" class="form-control" value="{{ $currentGlobalRules['curfew_late_red'] ?? 30 }}" min="10" required>
                    </div>
                </div>

                <h4 style="font-size:14px; font-weight:700; color:var(--primary); margin-top:16px; margin-bottom:12px; border-bottom:1px solid var(--border-soft); padding-bottom:6px;">
                    3. Keterlambatan Kepulangan Berdasarkan Izin
                </h4>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label">Kuning (+Menit setelah deadline izin)</label>
                        <input type="number" name="permit_late_yellow" class="form-control" value="{{ $currentGlobalRules['permit_late_yellow'] ?? 15 }}" min="5" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Merah (+Menit setelah deadline izin)</label>
                        <input type="number" name="permit_late_red" class="form-control" value="{{ $currentGlobalRules['permit_late_red'] ?? 30 }}" min="10" required>
                    </div>
                </div>

                <div class="form-group" style="margin-top:16px;">
                    <label class="form-label">Alasan Perubahan Parameter (Wajib Audit) <span style="color:var(--danger)">*</span></label>
                    <textarea name="change_reason" class="form-control" rows="2" placeholder="Contoh: Penyesuaian jam malam di musim ujian semester..." required></textarea>
                </div>

                <div style="display:flex; justify-content:flex-end;">
                    <button type="submit" class="btn btn-primary">Simpan Versi Aturan Baru</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rule Versions History -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📜 Riwayat Versi Aturan</span>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Versi / Tanggal</th>
                        <th>Cakupan</th>
                        <th>Alasan Perubahan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rulesHistory as $rule)
                        <tr>
                            <td>
                                <strong>Versi #{{ $rule->id }}</strong><br>
                                <small style="color:var(--text-muted);">{{ $rule->effective_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                <span class="badge badge-teal">{{ ucfirst($rule->scope) }}</span>
                            </td>
                            <td>
                                <div style="font-size:12px;">{{ $rule->change_reason ?? 'Inisial sistem' }}</div>
                                <small style="color:var(--text-muted);">Oleh: {{ $rule->creator->name ?? 'System' }}</small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align:center; padding:20px; color:var(--text-muted);">Belum ada riwayat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
