<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu QR Monitoring - {{ $santri->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }
        body { background: #F1F5F9; min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px; }

        .card-container {
            width: 380px;
            background: #FFFFFF;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border: 2px solid #0F766E;
            overflow: hidden;
            position: relative;
        }

        .card-header {
            background: #0F766E;
            color: #FFFFFF;
            padding: 16px;
            text-align: center;
        }

        .card-header h2 { font-size: 15px; font-weight: 800; letter-spacing: -0.2px; text-transform: uppercase; }
        .card-header p { font-size: 11px; opacity: 0.9; margin-top: 2px; }

        .card-body {
            padding: 24px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .qr-wrapper {
            padding: 12px;
            background: #FFFFFF;
            border: 2px dashed #99F6E4;
            border-radius: 12px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .santri-name { font-size: 17px; font-weight: 800; color: #0F172A; }
        .santri-niup { font-size: 13px; font-weight: 600; color: #0F766E; margin-top: 2px; }
        .santri-org { font-size: 12px; color: #64748B; margin-top: 6px; }

        .card-footer {
            background: #F8FAFC;
            border-top: 1px solid #E2E8F0;
            padding: 10px 16px;
            font-size: 10px;
            color: #94A3B8;
            text-align: center;
        }

        .actions { margin-top: 20px; display: flex; gap: 10px; }
        .btn { padding: 10px 20px; font-size: 14px; font-weight: 700; border-radius: 8px; border: none; cursor: pointer; text-decoration: none; }
        .btn-print { background: #0F766E; color: #FFFFFF; }
        .btn-back { background: #E2E8F0; color: #0F172A; }

        @media print {
            body { background: #FFFFFF; padding: 0; }
            .actions { display: none; }
            .card-container { box-shadow: none; margin: 0 auto; page-break-inside: avoid; }
        }
    </style>
</head>
<body>

    <div class="card-container">
        <div class="card-header">
            <h2>Kartu Monitoring Perjalanan</h2>
            <p>Pondok Pesantren Mahasiswa</p>
        </div>

        <div class="card-body">
            <div class="qr-wrapper">
                <div id="qrcode"></div>
            </div>

            <div class="santri-name">{{ $santri->name }}</div>
            <div class="santri-niup">NIUP: {{ $santri->niup }}</div>
            <div class="santri-org">
                {{ $santri->campus->name ?? '-' }} • {{ $santri->program->name ?? '-' }}<br>
                Kamar: {{ $santri->room->name ?? '-' }}
            </div>
        </div>

        <div class="card-footer">
            Pindai kartu ini di setiap pos pemeriksaan pesantren & kampus.<br>
            Diterbitkan: {{ $santri->activeQrCredential ? $santri->activeQrCredential->issued_at->format('d/m/Y') : now()->format('d/m/Y') }}
        </div>
    </div>

    <div class="actions">
        <button class="btn btn-print" onclick="window.print()">🖨️ Cetak Kartu</button>
        <a href="{{ route('admin.santri.index') }}" class="btn btn-back">Kembali</a>
    </div>

    <script>
        const tokenString = "{{ $plainToken }}";
        new QRCode(document.getElementById("qrcode"), {
            text: tokenString,
            width: 160,
            height: 160,
            colorDark : "#0F172A",
            colorLight : "#FFFFFF",
            correctLevel : QRCode.CorrectLevel.M
        });
    </script>
</body>
</html>
