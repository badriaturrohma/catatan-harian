<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Harian - {{ $date }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; }
        body { padding: 30px; color: #0F172A; }
        .header { text-align: center; border-bottom: 2px solid #0F766E; padding-bottom: 16px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; text-transform: uppercase; margin-bottom: 4px; }
        .header p { font-size: 13px; color: #475569; }
        .summary-box { display: flex; gap: 15px; margin-bottom: 20px; justify-content: space-between; }
        .stat-card { border: 1px solid #E2E8F0; border-radius: 8px; padding: 10px 14px; flex: 1; text-align: center; background: #F8FAFC; }
        .stat-val { font-size: 20px; font-weight: bold; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 10px; }
        th, td { border: 1px solid #CBD5E1; padding: 8px 10px; text-align: left; }
        th { background: #F1F5F9; font-weight: bold; }
        .text-center { text-align: center; }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #0F766E; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            🖨️ Cetak Dokumen / Simpan PDF
        </button>
    </div>

    <div class="header">
        <h1>Laporan Rekapitulasi Monitoring Perjalanan Santri</h1>
        <p>Pondok Pesantren Mahasiswa • Tanggal: <strong>{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y') }}</strong></p>
    </div>

    <div class="summary-box">
        <div class="stat-card">
            <div>Total Perjalanan</div>
            <div class="stat-val">{{ $report['summary']['total_journeys'] }}</div>
        </div>
        <div class="stat-card">
            <div>Santri Unik</div>
            <div class="stat-val">{{ $report['summary']['unique_santri'] }}</div>
        </div>
        <div class="stat-card">
            <div>Lengkap Kembali</div>
            <div class="stat-val">{{ $report['summary']['complete'] }}</div>
        </div>
        <div class="stat-card">
            <div>Incomplete</div>
            <div class="stat-val">{{ $report['summary']['incomplete'] }}</div>
        </div>
        <div class="stat-card">
            <div>Masih Terbuka</div>
            <div class="stat-val">{{ $report['summary']['open'] }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" width="4%">No</th>
                <th>NIUP & Nama Santri</th>
                <th>Kampus</th>
                <th>Kamar</th>
                <th>Jenis</th>
                <th>Keluar Pondok</th>
                <th>Tiba Kampus</th>
                <th>Keluar Kampus</th>
                <th>Kembali Pondok</th>
                <th>Durasi</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($report['rows'] as $idx => $r)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $r['santri_name'] }}</strong><br><small>{{ $r['niup'] }}</small></td>
                    <td>{{ $r['campus'] }}</td>
                    <td>{{ $r['room'] }}</td>
                    <td>{{ ucfirst($r['type']) }}</td>
                    <td>{{ $r['keluar_pondok_at'] }}</td>
                    <td>{{ $r['tiba_kampus_at'] }}</td>
                    <td>{{ $r['keluar_kampus_at'] }}</td>
                    <td>{{ $r['kembali_pondok_at'] }}</td>
                    <td>{{ $r['duration'] }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $r['status'])) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 20px;">Tidak ada catatan perjalanan pada tanggal ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 40px; display: flex; justify-content: flex-end;">
        <div style="text-align: center; width: 220px;">
            <p>Mengetahui,</p>
            <p>Pengurus Bidang Ketertiban</p>
            <div style="height: 60px;"></div>
            <p><strong>( Ust. Ahmad Fauzi )</strong></p>
        </div>
    </div>
</body>
</html>
