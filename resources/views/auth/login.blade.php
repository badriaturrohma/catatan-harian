<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Monitoring Perjalanan Santri</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #0F766E;
            --primary-dark: #0D655E;
            --primary-light: #CCFBF1;
            --bg-body: #F8FAFC;
            --card-bg: #FFFFFF;
            --text-main: #0F172A;
            --text-muted: #475569;
            --border-soft: #E2E8F0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background-color: var(--card-bg);
            border: 1px solid var(--border-soft);
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
            width: 100%;
            max-width: 440px;
            padding: 36px 32px;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background-color: var(--primary-light);
            color: var(--primary);
            margin-bottom: 12px;
        }

        .brand-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.4px;
        }

        .brand-desc {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid var(--border-soft);
            border-radius: 8px;
            font-size: 14px;
            color: var(--text-main);
            background-color: #FFFFFF;
            outline: none;
            transition: all 0.15s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.15);
        }

        .btn-submit {
            width: 100%;
            background-color: var(--primary);
            color: #FFFFFF;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: background-color 0.15s ease;
            margin-top: 8px;
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
        }

        .quick-roles {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px dashed var(--border-soft);
        }

        .quick-roles-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            text-align: center;
            margin-bottom: 12px;
        }

        .quick-buttons {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .btn-quick {
            background-color: #F1F5F9;
            border: 1px solid var(--border-soft);
            border-radius: 6px;
            padding: 8px 4px;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-main);
            cursor: pointer;
            text-align: center;
            transition: all 0.15s ease;
        }

        .btn-quick:hover {
            background-color: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary);
        }

        .error-message {
            background-color: #FEE2E2;
            color: #DC2626;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            border: 1px solid #FECACA;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand-header">
            <div class="brand-icon">
                <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <polyline points="16 11 18 13 22 9"></polyline>
                </svg>
            </div>
            <h1 class="brand-title">Monitoring Santri</h1>
            <p class="brand-desc">Sistem Pencatatan Perjalanan Santri Mahasiswa</p>
        </div>

        @if($errors->any())
            <div class="error-message">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Alamat Email</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="contoh@pesantren.id">
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Kata Sandi</label>
                <input type="password" id="password" name="password" class="form-control" required placeholder="••••••••">
            </div>

            <div class="form-group" style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 22px;">
                <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Ingat Saya</span>
                </label>
            </div>

            <button type="submit" class="btn-submit">Masuk ke Sistem</button>
        </form>

        <div class="quick-roles">
            <div class="quick-roles-title">Akses Cepat Pengujian Role</div>
            <div class="quick-buttons">
                <button type="button" class="btn-quick" onclick="fillLogin('pengurus@example.com', 'password')">
                    👑 Pengurus
                </button>
                <button type="button" class="btn-quick" onclick="fillLogin('petugas@example.com', 'password')">
                    🛡️ Petugas Pos
                </button>
                <button type="button" class="btn-quick" onclick="fillLogin('wali@example.com', 'password')">
                    👨‍👩‍👧 Wali Santri
                </button>
            </div>
        </div>
    </div>

    <script>
        function fillLogin(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
